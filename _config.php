<?php

use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;


$inject = !((!isset($_SERVER['REQUEST_URI']) || (isset($_SERVER['REQUEST_URI']) && ($_SERVER['REQUEST_URI'] == '/dev/build'))) || Director::is_cli());

// Manipulation only matters when running it from the browser, and not a dev/build
if ($inject) {
    Config::inst()->update(Injector::class, Image::class, [
        'class' => \Intervention\Image\WebP\Injectors\Image::class
    ]);
}

try {

    $extensions = Config::inst()->get(File::class, 'class_for_file_extension');

    Config::inst()->set(File::class, 'class_for_file_extension', array_merge($extensions ?: [], [
        'webp' => Image::class
    ]));

} catch (Throwable $e) {}