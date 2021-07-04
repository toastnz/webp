<?php

namespace Intervention\Image\WebP\Assets;

use LogicException;
use Intervention\Image\WebP\WebP;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Storage\AssetContainer;


trait ImageManipulation
{

    public function manipulateImage($variant, $callback)
    {
        // Set WebP suffix to variant
        $variant .= WebP::shouldGenerateWebP() ? 'wp' : '';

        return $this->manipulate(
            $variant,
            function (AssetStore $store, $filename, $hash, $variant) use ($callback) {
                /** @var Image_Backend $backend */
                $backend = $this->getImageBackend();

                // If backend isn't available
                if (!$backend || !$backend->getImageResource()) {
                    return null;
                }

                // Delegate to user manipulation
                $result = $callback($backend);

                // Empty result means no image generated
                if (!$result) {
                    return null;
                }

                // Write from another container
                if ($result instanceof AssetContainer) {
                    
                    try {       
                        $tuple = $store->setFromStream($result->getStream(), $filename, $hash, $variant);
                        return [$tuple, $result];
                    } finally {
                        // Unload the Intervention Image resource so it can be garbaged collected
                        $res = $backend->setImageResource(null);
                        gc_collect_cycles();
                    }
                }

                // Write from modified backend
                if ($result instanceof Image_Backend) {
                    try {
                        /** @var Image_Backend $result */
                        $tuple = $result->writeToStore(
                            $store,
                            $filename,
                            $hash,
                            $variant,
                            ['conflict' => AssetStore::CONFLICT_USE_EXISTING]
                        );

                        return [$tuple, $result];
                    } finally {
                        // Unload the Intervention Image resource so it can be garbaged collected
                        $res = $backend->setImageResource(null);
                        gc_collect_cycles();
                    }
                }

                // Unknown result from callback
                throw new LogicException("Invalid manipulation result");
            }
        );
    }


}
