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
 * Class AssetType
 */
class AssetType extends \Espo\Core\Templates\Repositories\Base
{
    /**
     * @inheritDoc
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        if ($entity->isAttributeChanged('isDefault')) {
            $this->getEntityManager()->nativeQuery("UPDATE `asset_type` SET is_default=0 WHERE 1");
        }

        if ($entity->isAttributeChanged('name') && $entity->getFetched('name') == 'File') {
            throw new BadRequest($this->getInjection('language')->translate('fileAssetTypeIsRequired', 'exceptions', 'AssetType'));
        }

        parent::beforeSave($entity, $options);
    }

    /**
     * @inheritDoc
     */
    protected function afterSave(Entity $entity, array $options = [])
    {
        $this->clearCache();

        parent::afterSave($entity, $options);
    }

    /**
     * @inheritDoc
     */
    protected function beforeRemove(Entity $entity, array $options = [])
    {
        if ($entity->get('name') == 'File') {
            throw new BadRequest($this->getInjection('language')->translate('fileAssetTypeIsRequired', 'exceptions', 'AssetType'));
        }

        parent::beforeRemove($entity, $options);
    }

    /**
     * @inheritDoc
     */
    protected function afterRemove(Entity $entity, array $options = [])
    {
        $this->clearCache();

        parent::afterRemove($entity, $options);
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        $this->addDependency('dataManager');
        $this->addDependency('language');
    }

    /**
     * Clearing cache
     */
    protected function clearCache(): void
    {
        $this->getInjection('dataManager')->clearCache();
    }
}
