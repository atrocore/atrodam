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

use Espo\Core\Utils\Json;
use Treo\Listeners\AbstractListener;
use Treo\Core\EventManager\Event;

/**
 * Class Metadata
 */
class Metadata extends AbstractListener
{
    /**
     * @var string
     */
    protected const CACHE_FILE = 'data/cache/asset_types.json';

    /**
     * @return bool
     */
    public static function dropCache(): bool
    {
        if (file_exists(self::CACHE_FILE)) {
            unlink(self::CACHE_FILE);
        }

        return true;
    }

    /**
     * @param Event $event
     */
    public function modify(Event $event)
    {
        $data = $event->getArgument('data');

        $data['fields']['asset']['typeNatures'] = $this->getAssetTypes();
        $data['entityDefs']['Asset']['fields']['type']['options'] = array_keys($data['fields']['asset']['typeNatures']);

        $event->setArgument('data', $data);
    }

    /**
     * @return array
     */
    protected function getAssetTypes(): array
    {
        if (!file_exists(self::CACHE_FILE)) {
            $sth = $this
                ->getContainer()
                ->get('pdo')
                ->prepare("SELECT name, nature FROM asset_type WHERE deleted=0");
            $sth->execute();
            $types = array_column($sth->fetchAll(\PDO::FETCH_ASSOC), 'nature', 'name');

            if (!file_exists('data/cache')) {
                mkdir('data/cache', 0777, true);
                sleep(1);
            }
            file_put_contents(self::CACHE_FILE, Json::encode($types));
        } else {
            $types = Json::decode(file_get_contents(self::CACHE_FILE), true);
        }

        return $types;
    }
}
