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

use Espo\Core\Exceptions\BadRequest;
use Treo\Core\EventManager\Event;

/**
 * Class EntityManagerController
 */
class EntityManagerController extends AbstractListener
{
    /**
     * @param Event $event
     *
     * @throws BadRequest
     */
    public function beforeActionCreateLink(Event $event)
    {
        $entity = !empty($event->getArgument('data')->entity) ? $event->getArgument('data')->entity : '';
        $entityForeign = !empty($event->getArgument('data')->entityForeign) ? $event->getArgument('data')->entityForeign : '';

        if ($entity === 'Asset') {
            foreach ($this->getMetadata()->get(['entityDefs', $entityForeign, 'links'], []) as $row) {
                if ($row['type'] == 'hasMany' && $row['entity'] === 'Asset') {
                    $this->showErrorMessage($entityForeign);
                }
            }
        }

        if ($entityForeign === 'Asset') {
            foreach ($this->getMetadata()->get(['entityDefs', $entity, 'links'], []) as $row) {
                if ($row['type'] == 'hasMany' && $row['entity'] === 'Asset') {
                    $this->showErrorMessage($entity);
                }
            }
        }
    }


    /**
     * @param string $entity
     *
     * @throws BadRequest
     */
    protected function showErrorMessage(string $entity): void
    {
        throw new BadRequest(
            $this->getLanguage()->translate(
                sprintf("Entity 'Asset' is already linked with entity '%s'. You can't link these entities again.", $entity), 'exceptions', 'Asset'
            )
        );
    }
}
