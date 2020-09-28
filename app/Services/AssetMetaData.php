<?php

declare(strict_types=1);

namespace Dam\Services;

/**
 * Class AssetMetaData
 * @package Dam\Services
 */
class AssetMetaData extends \Espo\Core\Templates\Services\Base
{
    /**
     * @param $entityType
     * @param $entityId
     * @param $metaData
     * @return mixed
     */
    public function insertData($entityType, $entityId, $metaData)
    {
        $field = $entityType . "_id";

        $repository = $this->getRepository();

        $repository->clearData($field, $entityId);

        return $repository->insertMeta($field, $entityId, $metaData);
    }
}
