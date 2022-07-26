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
 *
 * This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam\Repositories;

use Espo\ORM\Entity;
use Espo\Core\Templates\Repositories\Base;

class ValidationRule extends Base
{
    /**
     * @inheritDoc
     */
    public function beforeSave(Entity $entity, array $options = array())
    {
        // set name
        $entity->set('name', $entity->get('type'));

        parent::beforeSave($entity, $options);
    }

    public function save(Entity $entity, array $options = [])
    {
        $inTransaction = false;
        if (!$this->getEntityManager()->getPDO()->inTransaction()) {
            $this->getEntityManager()->getPDO()->beginTransaction();
            $inTransaction = true;
        }

        try {
            $result = parent::save($entity, $options);
            $this->recheckAllAssets($entity);
            if ($inTransaction) {
                $this->getEntityManager()->getPDO()->commit();
            }
        } catch (\Throwable $e) {
            if ($inTransaction) {
                $this->getEntityManager()->getPDO()->rollBack();
            }
            throw $e;
        }

        return $result;
    }

    public function recheckAllAssets(Entity $entity): void
    {
        $assets = $this
            ->getEntityManager()
            ->getRepository('Asset')
            ->select(['id'])
            ->where(['type*' => '%"' . $entity->get('assetTypeName') . '"%'])
            ->find();

        if (count($assets) === 0) {
            return;
        }

        foreach ($assets as $asset) {
            $this->getInjection('pseudoTransactionManager')->pushCustomJob('Asset', 'recheckAssetTypes', ['assetId' => $asset->get('id')]);
        }
    }

    protected function init()
    {
        parent::init();

        $this->addDependency('pseudoTransactionManager');
    }
}
