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
 *  This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam\Core;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Injectable;
use Espo\ORM\Entity;

class AssetValidator extends Injectable
{
    public function __construct()
    {
        $this->addDependency('configManager');
        $this->addDependency('validator');
        $this->addDependency('entityManager');
        $this->addDependency('language');
    }

    public function validateViaType(string $type, Entity $attachment): void
    {
        if (empty($type)) {
            return;
        }
        $config = $this->getInjection("configManager")->getByType([ConfigManager::getType($type)]);
        if (!empty($config['validations'])) {
            foreach ($config['validations'] as $type => $value) {
                $this->getInjection('validator')->validate($type, clone $attachment, ($value['private'] ?? $value));
            }
        }
    }

    public function validateViaTypes(array $types, Entity $attachment): void
    {
        foreach ($types as $type) {
            $this->validateViaType((string)$type, $attachment);
        }
    }

    public function validate(Entity $asset): void
    {
        $attachment = $this->getInjection('entityManager')->getEntity('Attachment', $asset->get('fileId'));
        if (empty($attachment)) {
            throw new BadRequest($this->getInjection('language')->translate('noAttachmentExist', 'exceptions', 'Asset'));
        }

        $this->validateViaTypes($asset->get('type'), $attachment);
    }
}
