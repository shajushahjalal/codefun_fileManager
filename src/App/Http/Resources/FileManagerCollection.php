<?php

namespace Codefun\FileManager\App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FileManagerCollection extends ResourceCollection
{
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
     * Process The Collection
     */
    protected function processCollection($request){
        return $this->collection->map(function (FileManagerResource $resource) use ($request) {
            if( count($this->withFields) > 0){
                return $resource->show($this->withFields)->toArray($request);
            }
            return $resource->hide($this->withoutFields)->toArray($request);
        })->all();
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->processCollection($request);
    }
}
