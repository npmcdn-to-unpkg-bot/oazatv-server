<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Nette\Application\Responses\JsonResponse,
 App\ApiModule\JsonApi;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoPresenter extends BasePresenter {
   
    public function actionDefault($id) {
        $hash = $id;
        
        $video = $this->videoManager->getVideoFromDBbyHash($hash);
        
        if ($video != false) {
            
            $localizedVideo = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video);

            $this->sendResponse(new JsonResponse($localizedVideo));
        }
        
        
    }
}
