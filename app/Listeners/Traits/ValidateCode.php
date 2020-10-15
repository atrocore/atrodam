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
