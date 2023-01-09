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

use Espo\Core\Templates\Repositories\Base;
use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\Entity;

class AssetType extends Base
{
    public function deleteValidationRules(Entity $entity): void
    {
        $this
            ->getEntityManager()
            ->getRepository('ValidationRule')
            ->where(['assetTypeId' => $entity->get('id')])
            ->removeCollection();
    }

    public function clearCache(): void
    {
        $this->getInjection('dataManager')->clearCache();
    }

    public function isInUse(Entity $entity): bool
    {
        $asset = $this
            ->getEntityManager()
            ->getRepository('Asset')
            ->select(['id'])
            ->where(['type*' => '%"' . ($entity->isNew() ? $entity->get('name') : $entity->getFetched('name')) . '"%'])
            ->findOne();

        return !empty($asset);
    }

    protected function beforeSave(Entity $entity, array $options = [])
    {
        if ($entity->isAttributeChanged('name') && $this->isInUse($entity)) {
            throw new BadRequest($this->getInjection('container')->get('language')->translate('assetTypeInUseRename', 'exceptions', 'AssetType'));
        }

        parent::beforeSave($entity, $options);
    }

    protected function afterSave(Entity $entity, array $options = [])
    {
        $this->clearCache();

        parent::afterSave($entity, $options);
    }

    protected function beforeRemove(Entity $entity, array $options = [])
    {
        if ($this->isInUse($entity)) {
            throw new BadRequest($this->getInjection('container')->get('language')->translate('assetTypeInUseDelete', 'exceptions', 'AssetType'));
        }

        parent::beforeRemove($entity, $options);
    }

    protected function afterRemove(Entity $entity, array $options = [])
    {
        $this->deleteValidationRules($entity);

        $this->clearCache();

        parent::afterRemove($entity, $options);
    }

    protected function init()
    {
        parent::init();

        $this->addDependency('dataManager');
    }
}
