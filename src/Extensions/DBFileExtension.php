<?php

namespace Intervention\Image\WebP\Extensions;

use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataExtension;

class DBFileExtension extends DataExtension
{
    public function updateURL(&$url)
    {       
        if (strstr($url, 'wp.')) {
            $this->moveWebPifRequired($url);
            $url = str_replace('.' . pathinfo($url, PATHINFO_EXTENSION), '.webp', $url);
        }
    }

    private function moveWebPifRequired($url)
    {
        $urlParts = parse_url($url);

        if (class_exists(\Aws\S3\S3Client::class) && class_exists(\League\Flysystem\AwsS3v3\AwsS3Adapter::class) && Environment::getEnv('AWS_REGION_NAME') && Environment::getEnv('AWS_BUCKET_NAME') && isset($urlParts['scheme']) && isset($urlParts['host']) && isset($urlParts['path'])) {
            $originalFile = str_replace($urlParts['scheme'] . '://' . $urlParts['host'], '', $url);
            $webpFile = str_replace('.' . pathinfo($originalFile, PATHINFO_EXTENSION), '.webp', $originalFile);

            $s3 = new \Aws\S3\S3Client([
                'region'  => Environment::getEnv('AWS_REGION_NAME'),
                'version' => 'latest'
            ]);	 

            $adapter = new \League\Flysystem\AwsS3v3\AwsS3Adapter($s3, Environment::getEnv('AWS_BUCKET_NAME'));
            if ($adapter->has($originalFile) && !$adapter->has($webpFile)) {
                $adapter->rename($originalFile, $webpFile);
            }

        } else {
            $originalFile = str_replace('/' . ASSETS_DIR, ASSETS_PATH, $url);
            $webpFile = str_replace('.' . pathinfo($url, PATHINFO_EXTENSION), '.webp', $originalFile);

            // local file system
            if (file_exists($originalFile) && !file_exists($webpFile)) {
                rename($originalFile, $webpFile);
            }
            
        }

    }


}