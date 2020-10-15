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

namespace Dam\Services;

use Dam\Core\ConfigManager;
use Dam\Core\FilePathBuilder;
use Dam\Core\FileStorage\DAMUploadDir;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Treo\Core\EventManager\Event;
use Treo\Core\EventManager\Manager;
use Treo\Core\Utils\Language;

/**
 * Class Rendition
 * @package Dam\Services
 */
class Rendition extends \Espo\Core\Templates\Services\Base
{
    /**
     * Rendition constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addDependency("ConfigManager");
        $this->addDependency("filePathBuilder");
        $this->addDependency("language");
        $this->addDependency("eventManager");
    }

    /**
     * @param \Dam\Entities\Rendition $rendition
     * @return mixed
     */
    public function toDelete(\Dam\Entities\Rendition $rendition)
    {
        $rendition->set("deleted", true);

        if ($this->getRepository()->save($rendition)) {
            $this->afterToDelete($rendition);

            return $this->getService("Attachment")->toDelete($rendition->get("fileId"));
        }

        return false;
    }

    public function toDeleteCollection($collections)
    {
        if (!$collections->count()) {
            return false;
        }

        $res = true;

        foreach ($collections as $collection) {
            $res &= $this->toDelete($collection);
        }

        return $res;
    }

    /**
     * @param \Dam\Entities\Rendition $entity
     * @return bool
     */
    public function updateMetaData(\Dam\Entities\Rendition $entity)
    {
        $attachment = $entity->get("file");

        if (stripos($attachment->get("type"), "image/") !== false) {
            if ($meta = $this->getServiceFactory()->create("Attachment")->getImageMeta($attachment)) {
                return $this->getServiceFactory()->create("RenditionMetaData")->insertData($entity->id, $meta);
            }
        }

        return false;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function validateType($entity)
    {
        $asset = $entity->get('asset');

        $info = $this->getConfigManager()->getByType([ConfigManager::getType($asset->get("type")), "renditions"]);

        if (!$info) {
            throw new Error("This asset can't have any renditions");
        }

        $renditionNames = array_keys($info);

        if (!in_array($entity->get('type'), $renditionNames)) {
            throw new Error("Unsupported type");
        }

        if ($this->getRepository()->checkExist($entity)) {
            throw new BadRequest("Rendition with type '{$entity->get("type")}' already exist");
        }

        return true;
    }

    /**
     * @param $entity
     * @param $assetEntity
     */
    public function updateAttachmentInfo($entity, $assetEntity)
    {
        $attachmentService = $this->getServiceFactory()->create("Attachment");
        $attachmentEntity  = $entity->get("file");
        $nature            = $this->getConfigManager()->getByType([
            ConfigManager::getType($assetEntity->get("type")),
            "renditions",
            $entity->get("type"),
            "nature",
        ]);

        $path = ($entity->get("private") ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "{$entity->get("type")}/" . $entity->get("path") . "/" . $attachmentEntity->get("name");

        $fileInfo = $attachmentService->getFileInfo($attachmentEntity, $path);

        if ($fileInfo) {
            $entity->set([
                "size"     => round($fileInfo['size'] / 1024, 2),
                "sizeUnit" => "kb",
            ]);
        }

        if ($nature === "image" && stripos($attachmentEntity->get("type"), "image/") !== false) {
            $imageInfo = $attachmentService->getImageInfo($attachmentEntity, $path);
            if ($imageInfo) {
                $entity->set([
                    $this->attributeMapping("width")       => $imageInfo['width'],
                    $this->attributeMapping("height")      => $imageInfo['height'],
                    $this->attributeMapping("color-space") => $imageInfo['color_space'],
                    $this->attributeMapping("color-depth") => $imageInfo['color_depth'],
                    $this->attributeMapping("orientation") => $imageInfo['orientation'],
                ]);
            }

        }
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager(): ConfigManager
    {
        return $this->getInjection("ConfigManager");
    }

    protected function afterToDelete($entity)
    {
        $event = new Event(
            [
                'entity' => $entity,
            ]
        );

        // dispatch an event
        $this->getInjection('eventManager')->dispatch('RenditionEntity', "afterToDelete", $event);
    }

    /**
     * @return Language
     */
    protected function getLanguage(): Language
    {
        return $this->getInjection("language");
    }

    /**
     * @return FilePathBuilder
     */
    protected function getFilePathBuilder(): FilePathBuilder
    {
        return $this->getInjection("filePathBuilder");
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function getService($name)
    {
        return $this->getServiceFactory()->create($name);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function attributeMapping(string $name): string
    {
        return $this->getConfigManager()->get(["attributeMapping", $name, "field"]) ?? $name;
    }

    /**
     * @return Manager
     */
    protected function getEventManager(): Manager
    {
        return $this->getInjection("eventManager");
    }
}

