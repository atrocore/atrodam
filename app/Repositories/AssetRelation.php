<?php
declare(strict_types=1);

namespace Dam\Repositories;

use Dam\Core\ORM\Query\DamQuery;
use Espo\Core\ORM\Entity;
use Espo\Core\Templates\Repositories\Base;
use PDO;

/**
 * Class AssetRelation
 * @package Dam\Repositories
 */
class AssetRelation extends Base
{
    /**
     * @param Entity $asset
     * @param Entity $related
     * @param string $assignedUserId
     */
    public function createLink(Entity $asset, Entity $related, string $assignedUserId)
    {
        $entity = $this->get();

        $entity->set([
            "name"           => $asset->get("name") . " / " . $asset->get("size"),
            "entityName"     => $related->getEntityName(),
            "entityId"       => $related->id,
            "assetId"        => $asset->id,
            "assetType"      => $asset->type,
            "assignedUserId" => $assignedUserId,
        ]);

        $this->save($entity);
    }

    /**
     * @param Entity $asset
     * @param Entity $related
     * @return bool
     */
    public function deleteLink(Entity $asset, Entity $related)
    {
        $entities = $this->where([
            'assetId'    => $asset->id,
            "entityName" => $related->getEntityName(),
            "entityId"   => $related->id,
        ])->find();

        if (!$entities) {
            return false;
        }

        foreach ($entities as $entity) {
            $this->deleteFromDb($entity->id);
        }

        return true;
    }

    /**
     * @param string $entityName
     * @param string $entityId
     * @return mixed
     */
    public function deleteLinks(string $entityName, string $entityId)
    {
        if ($entityName === "Asset") {
            $where = "asset_id='{$entityId}'";
        } else {
            $where = "entity_name='{$entityName}' AND entity_id='{$entityId}'";
        }

        $pdo = $this->getPDO();

        return $pdo->exec("DELETE FROM `asset_relation` WHERE {$where}");
    }

    /**
     * @param string $assetId
     * @param string $entityName
     * @param string $entityId
     * @return mixed
     */
    public function getEntityAssetsById(string $assetId, string $entityName, string $entityId)
    {
        return $this->where([
            "assetId"    => $assetId,
            "entityName" => $entityName,
            "entityId"   => $entityId,
        ])->findOne();
    }

    /**
     * @param array  $list
     * @param string $entityName
     * @param string $entityId
     * @return mixed
     */
    public function getItemsInList(array $list, string $entityName, string $entityId)
    {
        return $this->getData($this->getSqlItemsInList($list), [$entityId, $entityName]);
    }

    /**
     * @param $entityId
     * @param $entityName
     * @param $type
     * @return mixed
     */
    public function getItemsByType($entityId, $entityName, $type)
    {
        $entity = $this->getEntityManager()->getEntity("AssetRelation");

        $fieldsList = array_filter($entity->getAttributeList(), function ($item) {
            return !in_array($item, $this->skipAttributeList());
        });

        $select = $this->getDamQuery()->createSelectStatement($entity, $fieldsList);
        $joins  = $this->getDamQuery()->buildJoins($entity);

        if ($entityName === "Asset") {

            $tableName = $this->getDamQuery()->toDb($type);

            $sql  = "    SELECT {$select}, {$tableName}.name as relatedEntityName
                    FROM asset_relation
                    INNER JOIN {$tableName} ON ({$tableName}.id = asset_relation.entity_id)
                    " . ($joins ? $joins : "") . "
                    WHERE asset_relation.asset_id = ?
                        AND {$tableName}.deleted = '0'
                        AND asset_relation.deleted = '0'";
            $data = [$entityId];
        } else {
            $sql  = "    SELECT {$select}, asset.name as relatedEntityName
                    FROM asset_relation
                    INNER JOIN asset ON (asset.id = asset_relation.asset_id)
                     " . ($joins ? $joins : "") . "
                    WHERE asset_relation.entity_id = ?
                        AND asset_relation.entity_name = ?
                        AND asset.type = ?
                        AND asset.deleted = '0'
                        AND asset_relation.deleted = '0'
                    ORDER BY asset_relation.sort_order ASC";
            $data = [$entityId, $entityName, $type];
        }

        return $this->getData($sql, $data);

    }

    /**
     * @param string $entityName
     * @param string $entityId
     * @param array  $assetIds
     * @return mixed
     */
    public function getItemsByAssetIds(string $entityName, string $entityId, array $assetIds)
    {
        $entity = $this->getEntityManager()->getEntity("AssetRelation");

        $fieldsList = array_filter($entity->getAttributeList(), function ($item) {
            return !in_array($item, $this->skipAttributeList());
        });

        $select = $this->getDamQuery()->createSelectStatement($entity, $fieldsList);
        $joins  = $this->getDamQuery()->buildJoins($entity);

        $sql  = "   SELECT {$select}, 
                        asset.name as assetName, 
                        asset.size as assetSize, 
                        asset.type as assetType
                    FROM asset_relation
                    INNER JOIN asset ON (asset.id = asset_relation.asset_id)
                    " . ($joins ? $joins : "") . "
                    WHERE asset_relation.entity_id = ?
                        AND asset_relation.entity_name = ?
                        AND asset.deleted = '0'
                        AND asset_relation.deleted = '0'
                        AND asset_relation.asset_id IN (\"" . implode('","', $assetIds) . "\")";
        $data = [$entityId, $entityName];

        return $this->getData($sql, $data);
    }

    /**
     * @param       $assetId
     * @param array $availableEntities
     * @return mixed
     */
    public function getAvailableEntities($assetId, array $availableEntities)
    {
        return $this->getData(
            "SELECT entity_name as name 
                 FROM asset_relation 
                 WHERE 
                    deleted = 0 
                    AND asset_id = ?
                    AND entity_name IN ('" . implode("','", $availableEntities) . "') 
                 GROUP BY entity_name",
            [$assetId]
        );
    }

    /**
     * @param string $sql
     * @param array  $data
     * @return mixed
     */
    protected function getData(string $sql, array $data = [])
    {
        $pdo = $this->getPDO();

        $stmt = $pdo->prepare($sql);

        $stmt->execute($data);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array $list
     * @return string
     */
    protected function getSqlItemsInList(array $list)
    {
        return "SELECT COUNT('id') as count, a.type
                FROM asset_relation as ar
                    INNER JOIN asset as a ON (a.id = ar.asset_id)
                WHERE ar.entity_id = ?
                  AND ar.entity_name = ?
                  AND a.type IN ('" . implode("','", $list) . "')
                  AND a.deleted = '0'
                  AND ar.deleted = '0'
                GROUP BY a.type";
    }

    /**
     * @return DamQuery
     */
    protected function getDamQuery()
    {
        return new DamQuery($this->getPDO(), $this->entityFactory);
    }

    /**
     * @return array
     */
    private function skipAttributeList()
    {
        return [
            "deleted",
            "createAt",
            "modifiedAt",
            "createdById",
            "createdByName",
            "modifiedById",
            "modifiedByName",
            "assignedUserId",
            "assignedUserName",
            "teamsIds",
            "teamsNames",
        ];
    }
}
