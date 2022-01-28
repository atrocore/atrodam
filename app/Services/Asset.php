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
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Templates\Services\Base;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\Log;
use Espo\ORM\Entity;
use Treo\Core\EventManager\Event;

/**
 * Class Asset
 *
 * @package Dam\Services
 */
class Asset extends Base
{
    /**
     * @var string[]
     */
    protected $mandatorySelectAttributeList = ['fileId', 'type'];

    /**
     * @param \stdClass $data
     * @param string    $fileId
     *
     * @return \stdClass
     */
    public static function preparePostDataForMassCreateAssets(\stdClass $data, string $fileId): \stdClass
    {
        // get file name
        $fileName = $data->filesNames->$fileId;

        // parse name
        $parts = explode('.', $fileName);

        // get asset name
        $assetName = implode('.', $parts);

        $postData = clone $data;
        $postData->fileId = $fileId;
        $postData->fileName = $fileName;
        $postData->name = $assetName;

        unset($postData->filesIds);
        unset($postData->filesNames);
        unset($postData->filesTypes);

        return $postData;
    }

    /**
     * @inheritDoc
     */
    public function prepareEntityForOutput(Entity $entity)
    {
        parent::prepareEntityForOutput($entity);

        $file = $entity->get('file');
        if (!empty($file)) {
            $entity->set('icon', $this->prepareAssetIcon((string)$entity->get('type'), (string)$file->get('name')));
            $entity->set('private', $file->get('private'));

            $pathData = $this->getEntityManager()->getRepository('Attachment')->getAttachmentPathsData($entity->get('fileId'));
            if (!empty($pathData['download'])) {
                $entity->set('url', $pathData['download']);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function isRequiredField(string $field, Entity $entity, $typeResult): bool
    {
        if ($field == 'filesIds') {
            return false;
        }

        return parent::isRequiredField($field, $entity, $typeResult);
    }

    /**
     * @inheritDoc
     */
    public function createEntity($data)
    {
        if (property_exists($data, 'url') && !empty($data->url)) {
            if (empty($attachment = $this->getService('Attachment')->createEntityByUrl($data->url, false))) {
                throw new BadRequest(sprintf($this->translate('wrongUrl', 'exceptions', 'Asset'), $data->url));
            }
            unset($data->url);

            if (!empty($asset = $this->getEntityManager()->getRepository('Asset')->where(['fileId' => $attachment->get('id')])->findOne())) {
                if (property_exists($data, 'type') && $data->type !== $asset->get('type')) {
                    throw new BadRequest(sprintf($this->translate('assetExistWithOtherType', 'exceptions', 'Asset'), $data->url));
                }
                return $this->readEntity($asset->get('id'));
            }

            $data->name = $attachment->get('name');
            $data->fileId = $attachment->get('id');
        }

        $entity = $this->getRepository()->get();
        $entity->set($data);

        // Are all required fields filled ?
        $this->checkRequiredFields($entity, $data);

        if ($this->isMassCreating($entity)) {
            return $this->massCreateAssets($data);
        }

        return parent::createEntity($data);
    }

    /**
     * @param string $scope
     * @param string $id
     *
     * @return array
     * @throws NotFound
     */
    public function getEntityAssets(string $scope, string $id): array
    {
        $event = $this
            ->getInjection('eventManager')
            ->dispatch(
                'AssetService',
                'beforeGetEntityAssets',
                new Event(['scope' => $scope, 'id' => $id])
            );
        $id = $event->getArgument('id');
        $scope = $event->getArgument('scope');

        $entity = $this->getEntityManager()->getEntity($scope, $id);
        if (empty($entity)) {
            throw new NotFound();
        }

        // get asset types
        $types = $this->getMetadata()->get('fields.asset.types', []);

        // sorting types
        sort($types);

        $list = [];
        foreach ($types as $type) {
            $assets = $this->getRepository()->findRelatedAssetsByType($entity, $type);

            // prepare assets
            foreach ($assets as &$item) {
                if (!empty($item['fileName'])) {
                    $item['icon'] = $this->prepareAssetIcon((string)$item['type'], (string)$item['fileName']);
                    $item['filePathsData'] = $this->getEntityManager()->getRepository('Attachment')->getAttachmentPathsData($item['fileId']);
                    if (!empty($item['filePathsData']['download'])) {
                        $item['url'] = $this->prepareUrl($item['filePathsData']['download']);
                    }

                    $assetCategories = $this
                        ->getEntityManager()
                        ->getRepository('AssetCategory')
                        ->select(['id', 'name'])
                        ->join('assets')
                        ->where(['assets.id' => $item['id']])
                        ->find()
                        ->toArray();

                    $item['assetCategoriesIds'] = array_column($assetCategories, 'id');
                    $item['assetCategoriesNames'] = array_column($assetCategories, 'name', 'id');
                }
            }
            unset($item);

            $list[] = [
                'id'     => $type,
                'name'   => $type,
                'assets' => $assets
            ];
        }

        return [
            'count' => count($list),
            'list'  => $list
        ];
    }

    /**
     * @param string $scope
     * @param string $entityId
     * @param array  $data
     *
     * @return bool
     */
    public function updateAssetsSortOrder(string $scope, string $entityId, array $data): bool
    {
        if (!empty($data['ids']) && is_array($data['ids'])) {
            return $this->getRepository()->updateSortOrder($scope, $entityId, $data['ids']);
        }

        return true;
    }

    /**
     * @param \Dam\Entities\Asset $asset
     *
     * @return mixed
     */
    public function updateMetaData(\Dam\Entities\Asset $asset)
    {
        $attachment = $asset->get('file');

        $metaData = $this->getServiceFactory()->create("Attachment")->getFileMetaData($attachment);

        return $this->getService("AssetMetaData")->insertData("asset", $asset->id, $metaData);
    }

    /**
     * @param \Dam\Entities\Asset $asset
     */
    public function getFileInfo(\Dam\Entities\Asset $asset)
    {
        $type = ConfigManager::getType($asset->get('type'));

        $fileInfo = $this->getService("Attachment")->getFileInfo($asset->get("file"));

        $asset->set(
            [
                "size"     => round($fileInfo['size'] / 1024, 1),
                "sizeUnit" => "kb",
            ]
        );

        if ($this->isImage($asset)) {
            $imageInfo = $this->getService("Attachment")->getImageInfo($asset->get("file"));
            $this->updateAttributes($asset, $imageInfo);
        }
    }

    /**
     * @param \Dam\Entities\Asset $asset
     * @param array               $imageInfo
     */
    public function updateAttributes(\Dam\Entities\Asset $asset, array $imageInfo)
    {
        $asset->set(
            $this->attributeMapping("height"),
            ($imageInfo['height'] ?? false) ? $imageInfo['height'] : null
        );
        $asset->set(
            $this->attributeMapping("width"),
            ($imageInfo['width'] ?? false) ? $imageInfo['width'] : null
        );
        $asset->set(
            $this->attributeMapping("color-space"),
            ($imageInfo['color_space'] ?? false) ? $imageInfo['color_space'] : null
        );
        $asset->set(
            $this->attributeMapping("color-depth"),
            ($imageInfo['color_depth'] ?? false) ? $imageInfo['color_depth'] : null
        );
        $asset->set(
            $this->attributeMapping("orientation"),
            ($imageInfo['orientation'] ?? false) ? $imageInfo['orientation'] : null
        );
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     *
     * @return mixed
     */
    public function linkToAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        if ($main->id === $foreign->id) {
            throw new BadRequest($this->translate("JoinMainAsset", "exceptions", "Asset"));
        }

        return $this->getRepository()->linkAsset($main, $foreign);
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     *
     * @return mixed
     */
    public function unlinkToAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        return $this->getRepository()->unlinkAsset($main, $foreign);
    }

    /**
     * @param \stdClass $data
     *
     * @return Entity
     */
    protected function massCreateAssets(\stdClass $data): Entity
    {
        $entity = $this->getRepository()->get();

        if (count($data->filesIds) > 20) {
            $name = $this->getInjection('language')->translate('massCreateAssets', 'labels', 'Asset');
            $this->getInjection('queueManager')->push($name, 'QueueManagerMassCreateAssets', ['data' => $data]);

            return $entity;
        }

        foreach ($data->filesIds as $fileId) {
            $postData = self::preparePostDataForMassCreateAssets($data, $fileId);
            try {
                $entity = parent::createEntity($postData);
            } catch (\Throwable $e) {
                $GLOBALS['log']->error("ERROR in massCreateAssets: " . $e->getMessage() . ' | ' . $e->getTraceAsString());
            }
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        $this->addDependency("DAMFileManager");
        $this->addDependency("language");
        $this->addDependency("ConfigManager");
        $this->addDependency('log');
        $this->addDependency('eventManager');
        $this->addDependency('queueManager');
    }

    protected function prepareUrl(string $downloadPath): string
    {
        return rtrim($this->getConfig()->get('siteUrl', ''), '/') . '/' . $downloadPath;
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager(): ConfigManager
    {
        return $this->getInjection("ConfigManager");
    }

    /**
     * @return Log
     */
    protected function getLog(): Log
    {
        return $this->getInjection("log");
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    protected function getService($name)
    {
        return $this->getServiceFactory()->create($name);
    }

    /**
     * @param Entity $entity
     *
     * @return array
     */
    protected function checkIssetLink(Entity $entity)
    {
        $list = [];

        foreach ($entity->getRelations() as $key => $relation) {
            if ($this->isMulti($entity, $relation, $key)) {
                $list[] = [
                    "entityName" => $relation['entity'],
                    "entityId"   => $entity->get($relation['key']),
                ];
            }
        }

        return $list;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function skipEntityAssets(string $key)
    {
        return !$this->getMetadata()->get(['entityDefs', 'Asset', 'links', $key, 'entityAsset']);
    }

    /**
     * @param Entity $entity
     * @param        $relation
     * @param        $key
     *
     * @return bool
     */
    protected function isMulti(Entity $entity, $relation, $key): bool
    {
        return $relation['type'] === "belongsTo"
            && $entity->isAttributeChanged($relation['key'])
            && $key !== "ownerUser"
            && !$this->skipEntityAssets($key);
    }

    protected function attributeMapping(string $name): string
    {
        return $this->getConfigManager()->get(["attributeMapping", $name, "field"]) ?? $name;
    }

    protected function translate(string $label, string $category, string $scope): string
    {
        return $this->getInjection("language")->translate($label, $category, $scope);
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    protected function isMassCreating(Entity $entity): bool
    {
        return !empty($entity->get('filesIds')) && $entity->isNew();
    }

    protected function prepareAssetIcon(string $type, string $fileName): ?string
    {
        $fileNameParts = explode('.', $fileName);
        $fileExt = strtolower(array_pop($fileNameParts));

        return in_array($fileExt, $this->getMetadata()->get('fields.asset.hasPreviewExtensions', [])) ? null : $fileExt;
    }

    protected function isImage(Entity $asset): bool
    {
        $fileNameParts = explode('.', $asset->get("file")->get('name'));
        $fileExt = strtolower(array_pop($fileNameParts));

        return in_array($fileExt, $this->getMetadata()->get('dam.image.extensions', []));
    }
}
