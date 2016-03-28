<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\VideoManager,
Model\AnalyticsManager,
Model\SongsManager,
Model\PreachersManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoPresenter extends BasePresenter {

    private $videoManager;
    private $analyticsManager;
    private $songsManager;
    private $preachersManager;

    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager,
            AnalyticsManager $analyticsManager, SongsManager $songsManager,
            PreachersManager $preachersManager) {

        parent::__construct($container, $database);

        $this->videoManager = $videoManager;
        $this->analyticsManager = $analyticsManager;
        $this->songsManager = $songsManager;
        $this->preachersManager = $preachersManager;
    }
    
    private function countView($id, $hash) {
        $httpResponse = $this->container->getByType('Nette\Http\Response');
        $watchedCookie = $this->getHttpRequest()->getCookie($hash);
        if (!isset($watchedCookie)) {
            $this->videoManager->countView($id);
            $this->analyticsManager->countVideoView($id, AnalyticsManager::WEB);
            $this->analyticsManager->addVideoToPopular($id);
            $httpResponse->setCookie($hash, 'watched', '1 hour');
        }
    }

    public function renderWatch($id, $searched) {
        $hash = $id; //id only in router, actualy its hash
        $video = $this->videoManager->getVideoFromDBbyHash($hash);
        if($searched) {
            $this->analyticsManager->countVideoSearchClick($video['id'], AnalyticsManager::WEB);
        }

        $tags = explode(",",$video['tags']);
        $tagsWithSongs = $this->songsManager->parseTagsAndReplaceKnownSongs($tags);
        $this->template->tags = $tagsWithSongs;
        
        $preachers = array();
        foreach($tags as $tag) {
            $findPreacher = $this->preachersManager->getPreacherFromDBByTag($tag);
            if ($findPreacher) {
                $preacher = $this->preachersManager->createLocalizedObject($this->lang, $findPreacher);
                $preachers[] = $preacher;
            }
        }
        $this->template->preachers = array_map("unserialize", array_unique(array_map("serialize", $preachers)));

        $this->countView($video->id, $hash);

        $this->template->video = $this->videoManager
                ->createLocalizedVideoObject($this->lang, $video);
        
        $this->template->similarVideos = $this->videoManager->findSimilarVideos($video, $this->lang, 12);
    }

}
