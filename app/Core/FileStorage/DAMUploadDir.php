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

namespace Dam\Core\FileStorage;

use Dam\Core\DAMAttachment;
use Dam\Core\FilePathBuilder;
use Treo\Core\FileStorage\Storages\UploadDir;
use Treo\Core\ORM\EntityManager;
use Treo\Entities\Attachment;

/**
 * Class DAMUploadDir
 * @package Dam\Core\FileStorage
 */
class DAMUploadDir extends UploadDir
{
    const PRIVATE_PATH   = 'data/dam/private/';
    const PUBLIC_PATH    = 'data/dam/public/';
    const DAM_THUMB_PATH = 'data/dam/thumbs/';

    /**
     * DAMUploadDir constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->addDependency("EntityManager");
    }

    /**
     * @return array
     */
    public static function thumbsFolderList()
    {
        return [
            self::DAM_THUMB_PATH,
            self::BASE_THUMB_PATH,
        ];
    }

    /**
     * @param Attachment $attachment
     *
     * @return string
     */
    protected function getFilePath(Attachment $attachment): string
    {
        if ($attachment->get("tmpPath")) {
            return (string)$attachment->get("tmpPath");
        }

        $storage = $attachment->get('storageFilePath');
        $related = $attachment->get('related');

        if ($related) {
            $repository = $this->getEntityManager()->getRepository($related->getEntityType());
        }

        if (isset($repository) && is_a($repository, DAMAttachment::class)) {
            list($path, $type) = $repository->buildPath($related);
        } else {
            $type = FilePathBuilder::UPLOAD;
            $path = self::BASE_PATH;
        }

        if (!$storage) {
            $storage = $this->getPathBuilder()->createPath($type);
            $attachment->set('storageFilePath', $storage);
        }

        return $path . "{$storage}/" . $attachment->get('name');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->getInjection("EntityManager");
    }
}