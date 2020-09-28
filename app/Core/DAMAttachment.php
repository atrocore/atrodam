<?php

namespace Dam\Core;

use Espo\ORM\Entity;

/**
 * Interface DAMAttachment
 * @package Dam\Core
 */
interface DAMAttachment
{
    /**
     * @param Entity $entity
     * @return array
     */
    public function buildPath(Entity $entity): array;
}