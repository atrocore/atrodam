<?php

declare(strict_types=1);

namespace Dam\Core\Loaders;

use Dam\Core\ImageResize;
use Treo\Core\Loaders\Base;

/**
 * Class ImageResizeLoader
 * @package Dam\Core\Loaders\
 */
class ImageResizeLoader extends Base
{
    /**
     * @return ImageResize
     */
    public function load()
    {
        return new ImageResize();
    }
}