<?php

namespace Codefun\FileManager\App\Component\Traits;

use Codefun\FileManager\App\Models\FileManager as ModelsFileManager;

trait FileManager{
    /**
     * Autoload
     */
    public static function bootFileManager(){

    }

    /**
     * get All Files
     */
    public function files(){
        return $this->morphMany(ModelsFileManager::class, __FUNCTION__, "tableable", "tableable_id");
    }

    /**
     * get Profile Pic
     */
    public function profilePic(){
        return $this->morphMany(ModelsFileManager::class, __FUNCTION__, "tableable", "tableable_id")
            ->where("is_profile_pic", true);
    }

    /**
     * get Profile Pic
     */
    public function coverPic(){
        return $this->morphMany(ModelsFileManager::class, __FUNCTION__, "tableable", "tableable_id")
            ->where("is_cover_pic", true);
    }
}