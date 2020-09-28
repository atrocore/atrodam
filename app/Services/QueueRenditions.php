<?php

declare(strict_types=1);

namespace Dam\Services;

use Treo\Services\QueueManagerBase;

/**
 * Class QueueRenditions
 * @package Dam\Services
 */
class QueueRenditions extends QueueManagerBase
{
    /**
     * @param array $data
     * @return bool
     */
    public function run(array $data = []): bool
    {
        $entity = $this->getEntityManager()->getEntity("Asset", $data['entityId']);
        if (!$entity) {
            return true;
        }

        return $this->getService("Rendition")->buildRenditions($entity);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function getService($name)
    {
        return $this->getContainer()->get("serviceFactory")->create($name);
    }
}