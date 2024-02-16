<?php

namespace Codefun\FileManager\Facade;

use Codefun\FileManager\App\Component\Classes\File;
use Illuminate\Support\Facades\Facade;

class FileManager extends Facade{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return File::class;
    }
}