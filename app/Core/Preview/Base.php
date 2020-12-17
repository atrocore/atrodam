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

namespace Dam\Core\Preview;

use Dam\Core\FileStorage\DAMUploadDir;
use Espo\Core\Exceptions\Error;
use Gumlet\ImageResize;
use Treo\Entities\Attachment;
use Treo\Core\Container;
use Treo\Core\ModuleManager\Manager;

/**
 * Class Base
 * @package Dam\Core\Preview
 */
abstract class Base
{
    /**
     * @var Attachment
     */
    protected $attachment;
    /**
     * @var string
     */
    protected $size;
    /**
     * @var Container
     */
    protected $container;

    const MIME_MAPPING = [
        "application/pdf" => Pdf::class,
        "image/gif"       => Image::class,
        "image/jpeg"      => Image::class,
        "image/png"       => Image::class,
    ];

    const EXT_MAPPING = [];

    const DEFAULT_CLASS = Image::class;

    /**
     * @var $imageSizes
     */
    protected $imageSizes;

    /**
     * Base constructor.
     * @param Attachment $attachment
     * @param string     $size
     * @param Container  $container
     */
    public function __construct(Attachment $attachment, string $size, Container $container)
    {
        $this->attachment = $attachment;
        $this->size       = $size;
        $this->container  = $container;

        $this->imageSizes = $this->getMetadata()->get(['app', 'imageSizes']);
    }

    /**
     * @param Attachment  $attachment
     * @param string|null $size
     * @param Container   $container
     *
     * @return mixed
     */
    public static function init(Attachment $attachment, $size, Container $container)
    {
        $size = $size ?? 'original';
        $mime = $attachment->get('type');
        $extension = explode('.', $attachment->get("name"));
        $extension = end($extension);

        switch (true) {
            case isset(self::MIME_MAPPING[$mime]):
                $className = self::MIME_MAPPING[$mime];
                break;
            case isset(self::EXT_MAPPING[$extension]):
                $className = self::EXT_MAPPING[$extension];
                break;
            default:
                $className = self::DEFAULT_CLASS;
        }

        return (new $className($attachment, $size, $container))->show();
    }

    /**
     * @param Attachment $attachment
     * @param            $size
     * @return string
     */
    protected function buildPath(Attachment $attachment, $size)
    {
        if ($attachment->get('relatedType') === "Asset") {
            $asset = $attachment->get('related');
            if ($asset) {
                $isPrivate = $asset->get('private');
            }
        }

        $path = isset($isPrivate) ? DAMUploadDir::DAM_THUMB_PATH : DAMUploadDir::BASE_THUMB_PATH;

        return $path . $attachment->get('storageFilePath') . "/{$size}/" . $attachment->get('name');
    }

    /**
     * @param $thumbFilePath
     * @param $filePath
     * @param $size
     * @return mixed
     * @throws Error
     * @throws \Gumlet\ImageResizeException
     */
    protected function createThumb($thumbFilePath, $filePath, $size)
    {
        //TODO: change to loaders
        $image = new ImageResize($filePath);

        if (!$this->imageSizes[$size]) {
            throw new Error();
        }

        list($w, $h) = $this->imageSizes[$size];

        $image->resizeToBestFit($w, $h);

        if ($this->getFileManager()->putContents($thumbFilePath, $image->getImageAsString())) {
            return $thumbFilePath;
        }

        return false;
    }

    /**
     * @return mixed
     */
    protected function getMetadata()
    {
        return $this->container->get('metadata');
    }

    /**
     * @return mixed
     */
    protected function getEntityManager()
    {
        return $this->container->get('entityManager');
    }

    /**
     * @return mixed
     */
    protected function getFileManager()
    {
        return $this->container->get('fileManager');
    }

    protected function getModuleManager(): Manager
    {
        return $this->container->get("moduleManager");
    }

    /**
     * @return mixed
     */
    abstract public function show();
}