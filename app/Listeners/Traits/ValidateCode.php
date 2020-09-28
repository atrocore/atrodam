<?php

declare(strict_types=1);

namespace Dam\Listeners\Traits;

use Espo\Core\ORM\Entity;
use Treo\Core\EventManager\Event;

/**
 * Trait ValidateCode
 *
 * @package Dam\Listeners\Traits
 */
trait ValidateCode
{
    /**
     * @var string
     */
    private $pattern = '/^[a-z0-9_]*$/';

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    protected function isValidCode(Entity $entity): bool
    {
        $result = false;

        if (!empty($entity->get('code')) && preg_match($this->pattern, $entity->get('code'))) {
            $result = $this->isUnique($entity, 'code');
        }

        return $result;
    }

    /**
     * @param $entity
     * @param $field
     *
     * @return bool
     */
    protected function isUnique($entity, $field)
    {
        $repository = $this->getEntityManager()
                           ->getRepository($entity->getEntityName());

        return $repository->where([
            [$field => $entity->get($field)],
            ["id!=" => $entity->get("id")],
        ])->findOne() ? false : true;

    }
}
