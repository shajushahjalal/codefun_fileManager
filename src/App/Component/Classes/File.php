<?php

namespace Codefun\FileManager\App\Component\Classes;

use Codefun\FileManager\App\Models\FileManager;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File{

    /**
     * Mix Variables
     */
    private $disk;
    private $dir = "";
    private $width;
    private $height;
    private $model;
    private $request;
    private $is_profile_pic = false;
    private $deletable_file_ids = [];

    /**
     * Inject Request
     */
    function __construct(Request $request)
    {
        $this->request = $request;
        $this->disk = "public";
        ini_set('memory_limit', '1024M');
        $this->createStorageIfNotExists();
    }

    /**
     * Create Storage Simlink Where Storage Folder Dosen't Exists in Public folder
     */
    private function createStorageIfNotExists(){
        $dir = base_path().'/public/storage';
        if( !is_dir($dir) ){
            Artisan::call("storage:link");
        }
    }


    /**
     * Set Storege Disk
     */
    public function disk($disk = "public"){
        $this->disk = $disk;
        return $this;
    }

    /**
     * Set Request
     */
    public function request($request){
        $this->request = $request;
        return $this;
    }

    /**
     * Set Target Model Class
     * @param Model
     */
    public function model(Model $model){
        $this->model = $model;
        return $this;
    }

    /*
     * ---------------------------------------------
     * Check the Derectory If exists or Not
     * ---------------------------------------------
     */
    protected function checkDir($dir){
        $dir = "storage/" . $dir;
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }

        if(!file_exists($dir.'index.php')){
            $file = fopen($dir.'index.php','w');
            fwrite($file," <?php \n /* \n Unauthorize Access \n @Developer: Sm Shahjalal Shaju \n Email: shajushahjalal@gmail.com \n */ ");
            fclose($file);
        }
    }

    /*
     * ---------------------------------------------
     * Check the file If exists then Delete the file
     * ---------------------------------------------
     */
    protected function RemoveFile($filePath) {
        if(file_exists($filePath)){
            try{
                unlink($filePath);
            }catch(Exception $e){
                // Exception
            }
        }
    }

    /**
     * Set Directory
     */
    public function dir(string $dir_name){
        $this->dir = str_replace(["storage/", "storage"], "", $dir_name);
        return $this;
    }

     /**
     * Set Image as Profile Pic
     */
    public function profilePic($_is_profile_pic = true){
        $this->is_profile_pic = $_is_profile_pic;
        return $this;
    }

    /**
     * Update File
     * @param accept Int or array
     */
    public function update($deletable_file_id){
        if( !is_array($deletable_file_id) ){
            $deletable_file_id = (array) $deletable_file_id;
        }
        $this->deletable_file_ids = $deletable_file_id;
        return $this;
    }

    /*
     * ---------------------------------------------
     * Upload an Image
     * Change Image height and width
     * Send the null value in height or width to keep the Image Orginal Ratio.
     * ---------------------------------------------
     */
    public function upload($request, $fileName, $width = null, $height =  null){
        try{
            if( !$request->hasFile($fileName) ){
                throw new Exception("No File Selected", 404);
            }
            if(empty($this->model)){
                throw new Exception("Model Not Found", 404);
            }
            // $this->request($request);
            $this->width = $width;
            $this->height = $height;

            if(is_array($request->$fileName) ){
                foreach($request->$fileName as $key => $file){
                    $file = $request->file($fileName)[$key];
                    $this->saveFile($file);
                }
            }else{
                $file = $request->file($fileName);
                $this->saveFile($file);            
            }
            return ["status" => true, "message" => "File Save Successfully!"];
        }catch(Exception $e){
            return ["status" => false, "message" => $e->getMessage().' on '. $e->getFile() . ':'.$e->getLine()]; 
        }
    }

    /**
     * Save File
     */
    protected function saveFile($file){
        $filename = Str::random(16).time().'.'.$file->getClientOriginalExtension();
        $dir = empty($this->dir) ? "images/" : trim($this->dir, "/"). "/";
        $this->checkDir($dir);
        $path = "storage/".$dir.$filename;

        if( $this->isImage($filename) ){
            if( empty($this->height) && empty($this->width)){
                Image::make($file)->save($path);
            }
            elseif( empty($this->height) && !empty($this->width) ){
                Image::make($file)->resize($this->width, null, function($constant){
                    $constant->aspectRatio();
                })->save($path);
            }
            elseif( !empty($this->height) && empty($this->width) ){
                Image::make($file)->resize(null, $this->height, function($constant){
                    $constant->aspectRatio();
                })->save($path);
            }
            else{
                Image::make($file)->resize($this->width, $this->height)->save($path);
            }
        }else{
            $path = "storage/".Storage::disk($this->disk)->putFile($this->dir, $file);
        }

        /**
         * Store File Info In DB
         */
        $this->updateFileManager($path);
    }

    /**
     * Check Image or not
     * @return boolean
     */
    function isImage($file_path){
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $imgExtArr = ['jpg', 'jpeg', 'png'];
        if(in_array($extension, $imgExtArr)){
            return true;
        }
        return false;
    }

    /**
     * Store File Info Into DB
     */
    protected function updateFileManager($file_url){
        $file = new FileManager();
        $file->tableable_type= $this->model->getMorphClass();
        $file->tableable_id =  $this->model->id;
        $file->mime_type    = pathinfo($file_url, PATHINFO_EXTENSION);
        $file->size         = is_int(filesize($file_url)) ? filesize($file_url) / 1024 : 0;
        $file->file_url     = $file_url;
        $file->is_profile_pic= $this->is_profile_pic;

        if( isset(Auth::user()->id) ){
            $file->causarable_id= Auth::user()->id;
            $file->causarable   = Auth::user()->getMorphClass();
        }
        $file->save();

        if( count($this->deletable_file_ids) > 0 ){
            $this->deleteFiles();
        }
    }

    /**
     * Set Profile Pic
     */
    public function setProfilePic(){
        try{
            if(empty($this->model)){
                throw new Exception("Model Not Found", 404);
            }
            DB::beginTransaction();
            FileManager::where("tableable_type", $this->model->getMorphClass())
                ->where("id", "!=", $this->model->id)
                ->update([ "is_profile_pic"    => false ]);

            FileManager::where("tableable_type", $this->model->getMorphClass())
                ->where("id", $this->model->id)
                ->update(["is_profile_pic"    => true ]);
            DB::commit();
            return ["status" => true, "message" => "Profile Picture Set Successfully!"];
        }catch(Exception $e){
            DB::rollBack();
            return ["status" => false, "message" => $e->getMessage().' on '. $e->getFile() . ':'.$e->getLine()]; 
        }
    }

    /**
     * Delete all Files
     */
    public function deleteAll($model = null){
        if( !empty($model) ){
            $this->model($model);
        }

        if(empty($this->model)){
            throw new Exception("Model Not Found", 404);
        }

        if( !empty($this->request) ){
            FileManager::where("tableable", $this->model->getMorphClass())
            ->where("tableable_id", $this->model->id)
            ->update([
                "causarable_id" => Auth::user()->id ?? null,
                "causarable"    => Auth::user()->getMorphClass() ?? null,
            ]);
        }
        FileManager::where("tableable", $this->model->getMorphClass())
            ->where("tableable_id", $this->model->id)
            ->delete();
    }

    /**
     * Delete File Requeast
     * @return Array
     */
    public function delete($deltable_file_id){
        try{
            $this->update($deltable_file_id);
            $this->deleteFiles();

            return ["status" => true, "message" => "File Deleted Successfully!"];
        }catch(Exception $e){
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    /**
     * Delete File From File Manager
     */
    protected function deleteFiles(){
        if(empty($this->model)){
            throw new Exception("Model Not Found", 404);
        }
        
        FileManager::where("id", $this->deletable_file_ids)
            ->update([
                "causarable_id" => Auth::user()->id ?? null,
                "causarable"    => Auth::user()->getMorphClass() ?? null,
                "deleted_at"    => now(),
            ]);
    }
}