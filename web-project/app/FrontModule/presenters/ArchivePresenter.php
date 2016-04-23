<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\ArchiveManager,
Model\CategoriesManager,
Model\VideoManager,
Model\ArchiveMenuManager;


class ArchivePresenter extends BasePresenter {
    
    private $archiveManager;
    private $categoriesManager;
    private $videoManager;
    private $archiveMenuManager;
    public $lang;
    public $container;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, ArchiveManager $archiveManager,
            CategoriesManager $categoriesManager,
            VideoManager $videoManager,
            ArchiveMenuManager $archiveMenuManager) {
        
        parent::__construct($container, $database);
        $this->archiveManager = $archiveManager;
        $this->categoriesManager = $categoriesManager;
        $this->videoManager = $videoManager;
        $this->archiveMenuManager = $archiveMenuManager;
    }
    
    public function renderDefault() {
        $this->redirect('Archive:Page');
    }
    
    public function renderPage($id = 1) {
        
        $page = $id;
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($this->archiveManager->countArchive());
        $paginator->setItemsPerPage(64);
        $paginator->setPage($page);

        $archive = $this->archiveManager
                ->getVideosAndPhotoAlbumsFromDB($paginator->getOffset(), 
                        $paginator->getItemsPerPage(), 
                        $this->lang);
        
        $this->template->categories = $this->categoriesManager
                ->getLocalizedCategories($this->lang);
        
        $this->template->archiveItems = $archive;
        $this->template->paginator = $paginator;
        $this->template->page = $paginator->getPage();
        $this->template->pages = $paginator->getPageCount();
        $this->template->archiveMenu = $this->archiveMenuManager->getLocalizedMenus($this->lang);
        $this->template->archiveMenuManager = $this->archiveMenuManager;
    }
    
    public function renderCategory($id, $attr) {

        $category = $this->categoriesManager->getLocalizedCategory($id, $this->lang);
        $itemsPerPage = 64;
        
        $videos = $this->videoManager
                ->getVideosFromDBbyCategory($category['id'], $attr * $itemsPerPage, 999);
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount(sizeof($videos));
        $paginator->setItemsPerPage($itemsPerPage);
        $paginator->setPage($attr);
        
        $localizedVideos = array();
        foreach($videos as $video) {
            $localizedVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video);
        }
        
        $this->template->categories = $this->categoriesManager
                ->getLocalizedCategories($this->lang);
        
        $this->template->archiveItems = $localizedVideos;
        $this->template->paginator = $paginator;
        $this->template->page = $paginator->getPage();
        $this->template->pages = $paginator->getPageCount();
        $this->template->category = $category;
        $this->template->archiveMenu = $this->archiveMenuManager->getLocalizedMenus($this->lang);
        $this->template->archiveMenuManager = $this->archiveMenuManager;
        
    }
    
}
