<?php
/*
 *  This file is part of AtroDAM.
 *
 *  AtroDAM - Open Source DAM application.
 *  Copyright (C) 2020 AtroCore UG (haftungsbeschränkt).
 *  Website: https://atrodam.com
 *
 *  AtroDAM is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  AtroDAM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with AtroDAM. If not, see http://www.gnu.org/licenses/.
 *
 *  The interactive user interfaces in modified source and object code versions
 *  of this program must display Appropriate Legal Notices, as required under
 *  Section 5 of the GNU General Public License version 3.
 *
 *  In accordance with Section 7(b) of the GNU General Public License version 3,
 *  these Appropriate Legal Notices must retain the display of the "AtroDAM" word.
 */

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
