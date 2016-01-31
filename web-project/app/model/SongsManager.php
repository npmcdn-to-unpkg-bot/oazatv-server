<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CategoriesManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
namespace Model;

use Nette,
Model\TagsManager;
/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class SongsManager extends BaseModel {

    const
            TABLE_NAME = 'db_known_songs',
            COLUMN_ID = 'id',
            COLUMN_TAG = 'tag',
            COLUMN_NAME_CS = 'name_';

    /** @var Nette\Database\Context */
    public static $database;
    private $tagsManager;

    public function __construct(Nette\Database\Context $database,
    TagsManager $tagsManager) {
        self::$database = $database;
        $this->tagsManager = $tagsManager;
    }

    private function checkIfSongExists($id) {
        return self::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }

    public function saveSongToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfSongExists($id) > 0) {
            $category = self::$database->table(self::TABLE_NAME)->get($id);
            $sql = $category->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME)->insert($values);
        }

        return $sql->id;

    }


    public function getSongFromDB($id) {
        return self::$database->table(self::TABLE_NAME)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id))
                ->fetch();
    }

    public function getSongsFromDB() {
        return self::$database->table(self::TABLE_NAME)
            ->select('*')
            ->fetchAll();

    }

    public function deleteSong($id) {
        $video = $this->getSongFromDB($id);
        $video->delete();
    }

    public function parseTagsAndReplaceKnownSongs(array $tags) {

        $knownSongs = $this->getSongsFromDB();
        $outputTags = array();

        foreach($tags as $tagIndex => $tag) {
            $newTag = array();
            $newTag['tag'] = '#'.trim($tag);
            $newTag['usage'] = $this->tagsManager->tagUsage($tag);
            foreach($knownSongs as $song) {
                if ($newTag['tag'] == '#'.trim($song['tag'])) {
                    $newTag['tag'] = '♫ ' . $song['name'] . ' ♫';
                }
            }
            $outputTags[] = $newTag;
        }

        return $outputTags;
    }

}
