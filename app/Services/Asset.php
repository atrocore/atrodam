<?php

declare(strict_types=1);

namespace Dam\Services;

use Dam\Core\ConfigManager;
use Dam\Core\FileManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Templates\Services\Base;
use Espo\Core\Utils\Log;
use Espo\ORM\Entity;

/**
 * Class Asset
 *
 * @package Dam\Services
 */
class Asset extends Base
{
    /**
     * Asset constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addDependency("DAMFileManager");
        $this->addDependency("language");
        $this->addDependency("ConfigManager");
        $this->addDependency('log');
    }

    /**
     * @param \Dam\Entities\Asset $asset
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
        $type   = ConfigManager::getType($asset->get('type'));
        $nature = $this->getConfigManager()->getByType([$type, "nature"]);

        $fileInfo = $this->getService("Attachment")->getFileInfo($asset->get("file"));

        $asset->set([
            "size"     => round($fileInfo['size'] / 1024, 1),
            "sizeUnit" => "kb",
        ]);

        if ($nature === "image") {
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
     * @param Entity $entity
     * @return mixed
     */
    public function getRelationsCount(Entity $entity)
    {
        $collection = $this->getService("AssetRelation")->getRelationsLinks($entity);

        return $collection->count();
    }

    /**
     * @param Entity $entity
     * @param        $userId
     * @return bool
     */
    public function assetRelation(Entity $entity, $userId)
    {
        if (!$list = $this->checkIssetLink($entity)) {
            return false;
        }

        $service = $this->getService("AssetRelation");

        foreach ($list as $item) {
            $fEntity = $this->getEntityManager()->getEntity($item['entityName'], $item['entityId']);

            if ($fEntity) {
                $service->createLink($entity, $fEntity, $userId);
            }
        }

        return true;
    }

    /**
     * @param Entity $entity
     * @return mixed
     */
    public function deleteLinks(Entity $entity)
    {
        return $this->getService("AssetRelation")->deleteLinks("Asset", $entity->id);
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     * @return mixed
     */
    public function linkToAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        if ($main->id === $foreign->id) {
            throw new BadRequest($this->getTranslate("JoinMainAsset", "exceptions", "Asset"));
        }

        return $this->getRepository()->linkAsset($main, $foreign);
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     * @return mixed
     */
    public function unlinkToAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        return $this->getRepository()->unlinkAsset($main, $foreign);
    }

    /**
     * @return FileManager
     */
    protected function getFileManager(): FileManager
    {
        return $this->getInjection("DAMFileManager");
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
     * @return mixed
     */
    protected function getService($name)
    {
        return $this->getServiceFactory()->create($name);
    }

    /**
     * @param Entity $entity
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
     * @return bool
     */
    protected function isMulti(Entity $entity, $relation, $key): bool
    {
        return $relation['type'] === "belongsTo"
            &&
            $entity->isAttributeChanged($relation['key'])
            &&
            $key !== "ownerUser"
            &&
            !$this->skipEntityAssets($key);
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
     * @param $label
     * @param $category
     * @param $scope
     */
    private function getTranslate($label, $category, $scope)
    {
        $this->getInjection("language")->translate($label, $category, $scope);
    }
}
