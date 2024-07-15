<?php

namespace Codefun\FileManager\App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FileManagerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    protected $withoutFields = [];
    protected $withFields = [];

     /**
      * Set Hidden Item
      */
    public function hide($hide_field)
    {
        if( !is_array($hide_field) ){
            $hide_field = (array) $hide_field;
        }
        $this->withoutFields = $hide_field;
        return $this;
    }

    /**
     * Set Only Item
     * Accept @param string 
     * @param Array
    */
    public function show($show_field)
    {
        if( !is_array($show_field) ){
            $show_field = (array) $show_field;
        }
        $this->withFields = $show_field;
        return $this;
    }

    /**
      * Collection
      */
      public static function collection($resource)
      {
          return tap(new FileManagerCollection($resource), function ($collection) {
              $collection->collects = __CLASS__;
          });
      }
 
     /**
      * Filter Hide Items
      */
    protected function filter($data)
    {
        if( count($this->withFields) > 0 ){
            return collect($data)->only($this->withFields)->toArray();
        }
        return collect($data)->forget($this->withoutFields)->toArray();
    }
 
     /**
      * Transform the resource into an array.
      *
      * @param  \Illuminate\Http\Request  $request
      * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
      */
    public function toArray($request)
    {
        return $this->filter([
            "id"            => $this->id,
            "uuid"          => $this->uuid ?? "",
            "mime_type"      => $this->mime_type ?? "",
            "size"          => $this->size ? round($this->size/1024, 2) . "MB" : "",
            "file_url"      => $this->file_url ? asset($this->file_url) : null,
        ]);
    }
}
