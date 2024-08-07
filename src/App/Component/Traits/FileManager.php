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
     * get Files
     */
    public function files(){
        return $this->morphMany(ModelsFileManager::class, __FUNCTION__, "tableable_type", "tableable_id")
            ->where("is_profile_pic", false)->where("is_cover_pic", false);
    }

    /**
     * get All Files
     */
    public function allFiles(){
        return $this->morphMany(ModelsFileManager::class, __FUNCTION__, "tableable_type", "tableable_id");
    }

    /**
     * get Profile Pic
     */
    public function profilePic(){
        return $this->morphOne(ModelsFileManager::class, __FUNCTION__, "tableable_type", "tableable_id")
            ->where("is_profile_pic", true);
    }

    /**
     * get Profile Pic
     */
    public function coverPic(){
        return $this->morphOne(ModelsFileManager::class, __FUNCTION__, "tableable_type", "tableable_id")
            ->where("is_cover_pic", true);
    }
}
