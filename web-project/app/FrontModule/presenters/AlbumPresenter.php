<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\PhotosManager,
Model\TagsManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class AlbumPresenter extends BasePresenter {

    private $photosManager;
    private $tagsManager;

    public function __construct(Nette\DI\Container $container,
            Context $database, PhotosManager $photosManager, TagsManager $tagsManager) {
        parent::__construct($container, $database);
        $this->photosManager = $photosManager;
        $this->tagsManager = $tagsManager;
    }

    public function renderView($id) {
        $hash = $id; //id only in router, actualy its hash
        $album = $this->photosManager->getAlbumFromDBbyHash($hash);
        $tags = explode(",", $album['tags']);
        $tagsWithUsage = $this->tagsManager->tagsUsage($tags);
        $this->template->tags = $tagsWithUsage;

        $this->template->album = $this->photosManager
                ->createLocalizedAlbumThumbObject($this->lang, $album);

        $this->template->photos = $this->photosManager
                ->createLocalizedAlbumPhotosObject($this->lang, $album['id']);
    }

}
