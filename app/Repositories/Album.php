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

use Dam\Listeners\AbstractListener;
use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\Entity;

/**
 * Class Album
 */
class Album extends \Espo\Core\Templates\Repositories\Base
{
    /**
     * @inheritDoc
     *
     * @throws BadRequest
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        if (!$this->isValidCode($entity)) {
            throw new BadRequest($this->translate('Code is invalid', 'exceptions', 'Global'));
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
            throw new BadRequest($this->translate("Default album can't be deleted.", 'exceptions', 'Album'));
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
            throw new BadRequest($this->translate('Album can be linked with a root category only.', 'exceptions', 'Album'));
        }

        parent::beforeRelate($entity, $relationName, $foreign, $data, $options);
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->addDependency('language');
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

        if (!empty($entity->get('code')) && preg_match(AbstractListener::CODE_PATTERN, $entity->get('code'))) {
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
            ->getRepository('Album')
            ->where([['code' => $entity->get('code')], ["id!=" => $entity->get("id")],])
            ->findOne();

        return empty($entity);
    }

    /**
     * @param string $key
     * @param string $category
     * @param string $scope
     *
     * @return string
     */
    protected function translate(string $key, string $category, string $scope): string
    {
        return $this->getInjection('language')->translate($key, $category, $scope);
    }
}
