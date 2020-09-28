<?php

declare(strict_types=1);

namespace Dam\Services;

/**
 * Class Collection
 * @package Dam\Services
 */
class Collection extends \Espo\Core\Templates\Services\Base
{
    public function updateDefault($entity)
    {
        if ($entity->get("isDefault")) {
            $this->getRepository()->normalizedDefaultValue($entity->id);
        }
    }
}
