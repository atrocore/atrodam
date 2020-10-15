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

use Dam\Entities\Asset;
use Espo\Core\ORM\Entity;
use Espo\Core\Templates\Services\Base;
use Treo\Core\Slim\Http\Request;

/**
 * Class AssetRelation
 * @package Dam\Services
 */
class AssetRelation extends Base
{
    /**
     * @param $entity1
     * @param $entity2
     * @param $assignedUserId
     */
    public function createLink($entity1, $entity2, $assignedUserId)
    {
        if (is_a($entity1, Asset::class)) {
            $assetEntity   = $entity1;
            $relatedEntity = $entity2;
        } else {
            $assetEntity   = $entity2;
            $relatedEntity = $entity1;
        }

        $r = 1;

        if (
            !$this->isRelatedAssets($assetEntity, $relatedEntity)
            && $this->checkRules($relatedEntity)
            && !$this->checkDuplicate($assetEntity, $relatedEntity)
        ) {
            $this->deleteBelongsRelations($assetEntity, $relatedEntity);
            $this->getRepository()->createLink($assetEntity, $relatedEntity, $assignedUserId);
        }
    }

    /**
     * @param $entity1
     * @param $entity2
     * @return mixed
     */
    public function deleteLink($entity1, $entity2)
    {
        if (is_a($entity1, Asset::class)) {
            $assetEntity   = $entity1;
            $relatedEntity = $entity2;
        } else {
            $assetEntity   = $entity2;
            $relatedEntity = $entity1;
        }

        return $this->getRepository()->deleteLink($assetEntity, $relatedEntity);
    }

    /**
     * @param string $entityName
     * @param string $entityId
     * @return mixed
     */
    public function deleteLinks(string $entityName, string $entityId)
    {
        return $this->getRepository()->deleteLinks($entityName, $entityId);
    }

    /**
     * @param Entity $assetEntity
     * @param Entity $relatedEntity
     * @return mixed
     */
    public function checkDuplicate(Entity $assetEntity, Entity $relatedEntity)
    {
        return $this->getRepository()
                    ->getEntityAssetsById($assetEntity->id, $relatedEntity->getEntityName(), $relatedEntity->id);
    }

    /**
     * @param array  $list
     * @param string $entityName
     * @param string $entityId
     * @return array
     */
    public function getItemsInList(array $list, string $entityName, string $entityId)
    {
        $items = $this
            ->getRepository()
            ->getItemsInList($list, $entityName, $entityId);

        $resItems = [];
        $res      = [];

        foreach ($items as $item) {
            $resItems[$item['type']] = $item['count'];
        }

        foreach ($list as $listItem) {
            $res[] = [
                "id"      => $listItem,
                "name"    => $listItem,
                "hasItem" => $resItems[$listItem] ?? false,
            ];
        }

        return $res;
    }

    /**
     * @param string  $entityId
     * @param string  $entityName
     * @param Request $request
     * @return bool
     */
    public function getItems(string $entityId, string $entityName, Request $request)
    {
        if ($request->get('type')) {
            $res = $this->getRepository()->getItemsByType($entityId, $entityName, $request->get("type"));
        } elseif ($request->get("assetIds")) {
            $res = $this->getRepository()->getItemsByAssetIds($entityName, $entityId, explode(",", $request->get("assetIds")));
        }

        if (!isset($res) || !$res) {
            return false;
        }

        foreach ($res as $i => $item) {
            $model = $this->getRepository()->get();
            $model->set($item);
            $this->loadAdditionalFields($model);

            $res[$i] = $model->toArray();
        }

        return $res;
    }

    /**
     * @param array $where
     * @return mixed
     */
    public function getItem(array $where)
    {
        return $this->getRepository()->where($where)->findOne();
    }

    /**
     * @param string $entityName
     * @param string $entityId
     * @param array  $data
     * @return bool
     */
    public function updateSortOrder(string $entityName, string $entityId, array $data): bool
    {
        $result = false;

        if (!empty($data)) {
            $template = "UPDATE asset_relation SET sort_order = %s 
                      WHERE entity_name = '%s' AND entity_id = '%s' AND id = '%s';";
            $sql      = '';
            foreach ($data as $k => $id) {
                $sql .= sprintf($template, $k, $entityName, $entityId, $id);
            }

            $sth = $this->getEntityManager()->getPDO()->prepare($sql);
            $sth->execute();

            $result = true;
        }

        return $result;
    }

    /**
     * @param Entity $entity
     * @return mixed
     */
    public function getRelationsLinks(Entity $entity)
    {
        return $this->getRepository()->where([
            "assetId" => $entity->id,
        ])->find();
    }

    /**
     * @param Asset  $asset
     * @param Entity $entity
     * @return bool
     */
    public function deleteBelongsRelations(Asset $asset, Entity $entity)
    {
        list($assetTo, $entityTo) = $this->getRelationType($asset, $entity);

        if ($assetTo === "hasMany" && $entityTo === "hasMany") {
            return true;
        }

        /**@var $repository \Dam\Repositories\AssetRelation * */
        $repository = $this->getRepository();

        if ($assetTo === "belongsTo") {
            $item = $repository->where([
                'entityName' => $entity->getEntityType(),
                "assetId"    => $asset->id,
            ])->findOne();
        }

        if ($entityTo === "belongsTo") {
            $item = $repository->where([
                'entityName' => $entity->getEntityType(),
                "entityId"   => $entity->id,
            ])->findOne();
        }
        if (!isset($item)) {
            return false;
        }

        return $repository->deleteFromDb($item->id);
    }

    /**
     * @param Entity $entity
     */
    public function deleteRelation(Entity $entity)
    {
        $asset   = $this->getEntityManager()->getEntity("Asset", $entity->getFetched("assetId"));
        $fEntity = $this->getEntityManager()->getEntity($entity->getFetched("entityName"), $entity->getFetched("entityId"));

        list($assetTo, $entityTo) = $this->getRelationType($asset, $fEntity);

        switch (true) {
            case $assetTo === "belongsTo" :
                $this->removeBelongsToRelation($asset, $fEntity->getEntityType());
                break;
            case $entityTo === "belongsTo" :
                $this->removeBelongsToRelation($fEntity, "Asset");
                break;
            default:
                $this->removeHasManyRelation($asset, $fEntity);

        }

    }

    /**
     * @param string $assetId
     * @return mixed
     */
    public function getAvailableEntities(string $assetId)
    {
        $links = array_map(function ($item) {
            return $item['entity'];
        }, $this->getMetadata()->get(["entityDefs", "Asset", "links"]));

        $availableEntities = array_unique(array_values($links));

        return $this->getRepository()->getAvailableEntities($assetId, $availableEntities);
    }

    /**
     * @param Entity $entity
     * @param string $relatedEntityName
     * @return bool
     */
    protected function removeBelongsToRelation(Entity $entity, string $relatedEntityName)
    {
        if (!$info = $this->getRelationInfo($entity, $relatedEntityName)) {
            return false;
        }

        $entity->set($info['key'], null);

        return $this->getEntityManager()->saveEntity($entity, ['skipAll' => true]);
    }

    /**
     * @param Asset  $asset
     * @param Entity $entity
     * @return mixed
     */
    protected function removeHasManyRelation(Asset $asset, Entity $entity)
    {
        $info = $this->getRelationInfo($asset, $entity->getEntityType());
        /** @var \Espo\Core\Templates\Repositories\Base $relationRepository */
        $relationRepository = $this->getEntityManager()->getRepository($info['relationName']);

        return $relationRepository->unrelate($asset, $info['relationIndex'], $entity);
    }

    /**
     * @param Entity $entity
     * @param string $entityName
     * @return array
     */
    protected function getRelationInfo(Entity $entity, string $entityName): array
    {
        foreach ($entity->getRelations() as $key => $relation) {
            if ($relation['entity'] === $entityName) {
                $relation['relationIndex'] = $key;

                return $relation;
            }
        }

        return null;
    }

    /**
     * @param Asset  $asset
     * @param Entity $entity
     * @return array
     */
    protected function getRelationType(Asset $asset, Entity $entity)
    {
        foreach ($asset->getRelations() as $relation) {
            if ($relation['entity'] === $entity->getEntityType()) {
                $assetTo = $relation['type'];
                break;
            }
        }

        foreach ($entity->getRelations() as $relation) {
            if ($relation['entity'] === $asset->getEntityType()) {
                $entityTo = $relation['type'];
                break;
            }
        }

        return [
            $assetTo ?? null,
            $entityTo ?? null,
        ];
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
     * @return bool
     */
    protected function checkRules(Entity $entity)
    {
        $assetLinks = $this->getMetadata()->get("entityDefs.Asset.links");
        $entityName = $entity->getEntityName();

        foreach ($assetLinks as $link) {
            if ($link['entity'] === $entityName && isset ($link['entityAsset']) && !$link['entityAsset']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $entity1
     * @param $entity2
     * @return bool
     */
    protected function isRelatedAssets($entity1, $entity2)
    {
        return get_class($entity1) === get_class($entity2);
    }

}
