<?php

declare(strict_types=1);

namespace Dam\Core;

use Dam\Core\FileStorage\DAMUploadDir;

/**
 * Class FilePathBuilder
 * @package Treo\Core
 */
class FilePathBuilder extends \Treo\Core\FilePathBuilder
{
    const PRIVATE = 'private';
    const PUBLIC  = 'public';

    /**
     * @return array
     */
    public static function folderPath()
    {
        return array_merge(parent::folderPath(), [
            'private' => DAMUploadDir::PRIVATE_PATH,
            "public"  => DAMUploadDir::PUBLIC_PATH,
        ]);
    }
}
