<?php

declare(strict_types=1);

namespace Dam\EntryPoints;

use Dam\Core\FileStorage\DAMUploadDir;
use Dam\Core\PathInfo;
use Treo\Entities\Attachment;

/**
 * Class Image
 * @package Dam\EntryPoints
 */
class Image extends \Treo\EntryPoints\Image
{
    /**
     * @param Attachment $attachment
     * @param            $size
     * @return string
     */
    protected function getThumbPath($attachment, $size)
    {
        $related = $attachment->get("related");
        $path    = DAMUploadDir::BASE_THUMB_PATH;

        if (is_a($related, PathInfo::class)) {
            $path = DAMUploadDir::DAM_THUMB_PATH . $related->getMainFolder() . "/";
        }

        return $path . $attachment->get('storageFilePath') . "/{$size}/" . $attachment->get('name');
    }
}
