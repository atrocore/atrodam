<?php

declare(strict_types=1);

namespace Dam\Services;

/**
 * Class RenditionMetaData
 * @package Dam\Services
 */
class RenditionMetaData extends \Espo\Core\Templates\Services\Base
{
    const RENDITION_FIELD = "rendition_id";

    /**
     * @param $entityId
     * @param $metaData
     * @return mixed
     */
    public function insertData($entityId, $metaData)
    {
        $repository =  $this->getRepository();

        $repository->clearData(self::RENDITION_FIELD, $entityId);

        return $repository->insertMeta(self::RENDITION_FIELD, $entityId, $metaData);
    }
}
