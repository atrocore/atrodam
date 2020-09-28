<?php

declare(strict_types=1);

namespace Dam\Repositories;

/**
 * Class Collection
 * @package Dam\Repositories
 */
class Collection extends \Espo\Core\Templates\Repositories\Base
{
    /**
     * @param string $id
     */
    public function normalizedDefaultValue(string $id)
    {
        $entity = $this->where([
            'isDefault' => 1,
            "id!="      => $id,
        ])->findOne();

        if ($entity) {
            $entity->set("isDefault", false);
            $this->save($entity);
        }
    }
}
