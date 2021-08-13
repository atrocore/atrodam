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

namespace Dam\Listeners;

use Treo\Core\EventManager\Event;

/**
 * Class FieldManagerController
 */
class FieldManagerController extends AbstractListener
{
    public function beforePatchActionUpdate(Event $event): void
    {
        $this->beforePutActionUpdate($event);
    }

    /**
     * @param Event $event
     */
    public function beforePutActionUpdate(Event $event): void
    {
        $params = $event->getArgument('params');
        $data = $event->getArgument('data');

        if ($params['scope'] === 'Asset' && $params['name'] === 'type') {
            $repository = $this->getEntityManager()->getRepository('AssetType');

            if (property_exists($data, 'optionsIds')) {
                $options = (array)$data->options;
                $optionsIds = (array)$data->optionsIds;

                foreach ($optionsIds as $k => $id) {
                    $assetType = $repository->get($id);
                    if (empty($assetType)) {
                        $assetType = $repository->get();
                        $assetType->id = $id;
                    }

                    $assetType->set('name', $options[$k]);
                    $assetType->set('sortOrder', $k);

                    $this->getEntityManager()->saveEntity($assetType);
                }

                foreach ($repository->where(['id!=' => $optionsIds])->find() as $assetType) {
                    $this->getEntityManager()->removeEntity($assetType);
                }

                unset($data->options);
                unset($data->optionsIds);
            }

            if (property_exists($data, 'default')) {
                $assetType = $repository->where(['name' => $data->default])->findOne();
                if (!empty($assetType)) {
                    $assetType->set('isDefault', true);
                    $this->getEntityManager()->saveEntity($assetType);
                }
                unset($data->default);
            }

            $event->setArgument('data', $data);
        }
    }
}
