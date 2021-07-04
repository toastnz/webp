<?php

use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use Intervention\Image\ImageManager;
use SilverStripe\Core\Config\Config;

$extensions = Config::inst()->get(File::class, 'class_for_file_extension');

Config::inst()->set(File::class, 'class_for_file_extension', array_merge($extensions ?: [], [
    'webp' => Image::class
]));

