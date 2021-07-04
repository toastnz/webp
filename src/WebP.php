<?php

namespace Intervention\Image\WebP;

use SilverStripe\Control\Controller;
use SilverStripe\CMS\Controllers\ContentController;

class WebP
{
    private static $enabled = true;

    static function disable()
    {
        self::$enabled = false;
    }

    static function enable()
    {
        self::$enabled = true;
    }

    static function isEnabled()
    {
        return self::$enabled;
    }

    static function shouldGenerateWebP()
    {        
        return 
            (Controller::curr() instanceof ContentController) && 
            self::isEnabled() && function_exists('imagewebp') && 
            isset($_SERVER['HTTP_ACCEPT']) && 
            strstr($_SERVER['HTTP_ACCEPT'], 'image/webp');        
    }

}