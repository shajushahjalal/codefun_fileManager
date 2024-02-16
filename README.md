# About Laravel CodeFun File Manager System

* This package will automatically handel your all files for upload, update & delete.

# Installation Process

* ```composer require codefun/filemanager```
* ```php artisan migrate``` 

## Before Laravel 5.7 

Add the following into your _**providers**_ array on ```config\app.php```:

* ```CodeFun\Activitylog\App\Providers\FileManagerServiceProvider```

then add This alias into _**alias**_ array on ```config\app.php```:

* ```"FileManager" => CodeFun\Activitylog\Facade\FileManager::class```

## Not necessary from Laravel 5.7 onwards


# How To Upload File?
In your method Just Use it 
```
FileManager::upload();
```

Go to your Model and use the trait file: 
```
use CodeFun\FileManager\App\Component\Traits\FileManager;
class AnyModel extends Model
{
    use FileManager;
}
```
# Retrive Multiple Uploaded Files

``` 
$user = User::find(1);
$user->files() [ This Method Return File List(Array List)]

```
# Retrive profile Pic
``` 
$user = User::find(1);
$user->profilePic() [ This Method Return single File ]

```