<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette;

/**
 * Description of TagsManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class TagsManager extends BaseModel {
    
    const 
            TABLE_NAME_HIDDEN_TAGS = 'db_hidden_tags',
            COLUMN_ID = 'id',
            COLUMN_TAG = 'tag';


    /** @var Nette\Database\Context */
    public static $database;
    public static $videoManager;

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
    }

    public function tagCloud() {

        $query= self::$database->query("(SELECT tags FROM ".VideoManager::TABLE_NAME.
                ") UNION ALL (".
                "SELECT tags FROM ".PhotosManager::TABLE_NAME_ALBUMS.");")
                ->fetchAll();

        $tagArray = '';

        foreach($query as $line) {
            $tagArray .= ",".$line['tags'];
        }

        $tagArray = str_replace(" ", "", $tagArray);

        $tagArray = array_count_values(explode(',', $tagArray));
        arsort($tagArray);

        return $tagArray;

    }

    public function tagUsage($tag) {
        $allTags = $this->tagCloud();
        if(isset($allTags[trim($tag)])) {
            return $allTags[trim($tag)];
        } else {
            return 0;
        }
    }

    public function tagsUsage(array $tags) {
        $tagsWithUsage = array();
        foreach($tags as $tag) {
            $tagsWithUsage[str_replace('\"', '', $tag)] = $this->tagUsage($tag);
        }

        return $tagsWithUsage;
    }
    
    public function getHiddenTagsFromDB() {
        return self::$database->table(self::TABLE_NAME_HIDDEN_TAGS)
                ->select(self::COLUMN_TAG)
                ->fetchAll();
    }
    
    public function saveHiddenTagToDB($tag) {
        self::$database->table(self::TABLE_NAME_HIDDEN_TAGS)
                ->insert(array(self::COLUMN_TAG => $tag));
    }
    
    public function deleteHiddenTagFromDB($tag) {
        $tagToDelete = self::$database->table(self::TABLE_NAME_HIDDEN_TAGS)
                ->select('*')->where(self::COLUMN_TAG, $tag);
        $tagToDelete->delete();
    }
    
    public function isTagHidden($tag) {
        $hiddenTags = $this->getHiddenTagsFromDB();
        $tagsArray = array();
        foreach($hiddenTags as $hiddenTag) {
            $tagsArray[] = $hiddenTag[self::COLUMN_TAG];
        }
        return in_array($tag, $tagsArray);
    }

}
