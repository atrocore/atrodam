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

namespace Dam\Repositories;

use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\Entity;

/**
 * Class Library
 */
class Library extends AbstractRepository
{
    public const CODE_PATTERN = '/^[\p{Ll}0-9_]*$/u';

    /**
     * @inheritDoc
     *
     * @throws BadRequest
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        if (!$this->isValidCode($entity)) {
            throw new BadRequest($this->translate('codeInvalid', 'exceptions', 'Global'));
        }

        parent::beforeSave($entity, $options);
    }

    /**
     * @param Entity $entity
     * @param array  $options
     */
    protected function beforeRemove(Entity $entity, array $options = [])
    {
        if ($entity->get('id') === '1') {
            throw new BadRequest($this->translate("defaultLibraryCantBeDeleted", 'exceptions', 'Library'));
        }

        parent::beforeRemove($entity, $options);
    }

    /**
     * @inheritDoc
     *
     * @throws BadRequest
     */
    protected function beforeRelate(Entity $entity, $relationName, $foreign, $data = null, array $options = [])
    {
        if ($relationName == "assetCategories" && !$this->isValidCategory($foreign)) {
            throw new BadRequest($this->translate('libraryCanBeLinkedWithRootCategoryOnly', 'exceptions', 'Library'));
        }

        parent::beforeRelate($entity, $relationName, $foreign, $data, $options);
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    protected function isValidCategory(Entity $entity): bool
    {
        if (is_string($entity)) {
            $entity = $this->getEntityManager()->getEntity("AssetCategory", $entity);
        }

        return !$entity->get("categoryParentId");
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    protected function isValidCode(Entity $entity): bool
    {
        $result = false;

        if (!empty($entity->get('code')) && preg_match(self::CODE_PATTERN, $entity->get('code'))) {
            $result = $this->isUnique($entity);
        }

        return $result;
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    protected function isUnique(Entity $entity)
    {
        $entity = $this
            ->getEntityManager()
            ->getRepository('Library')
            ->where([['code' => $entity->get('code')], ["id!=" => $entity->get("id")],])
            ->findOne();

        return empty($entity);
    }
}
