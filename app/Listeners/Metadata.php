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

        $this->updateRelationMetadata($data);

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

        // set system asset types
        $types['File'] = 'File';
        $types['Image'] = 'Image';

        return $types;
    }

    /**
     * @param array $data
     */
    protected function updateRelationMetadata(array &$data)
    {
        foreach ($data['entityDefs'] as $scope => $defs) {
            if (empty($defs['links']) || $scope == 'Asset') {
                continue 1;
            }
            foreach ($defs['links'] as $link => $linkData) {
                if (!empty($linkData['entity']) && $linkData['entity'] == 'Asset') {
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['entityName'] = $scope;
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['label'] = $this->getLanguage()->translate('Asset', 'scopeNamesPlural', 'Global');
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['view'] = "dam:views/asset/record/panels/bottom-panel";

                    $data['entityDefs'][$scope]['links'][$link]['additionalColumns']['sorting'] = [
                        'type' => 'int'
                    ];
                    $data['entityDefs'][$scope]['fields'][$link]['columns']['assetSorting'] = 'sorting';
                    $data['entityDefs'][$scope]['fields']['assetSorting'] = [
                        'type'                      => 'int',
                        'notStorable'               => true,
                        'layoutListDisabled'        => true,
                        'layoutListSmallDisabled'   => true,
                        'layoutDetailDisabled'      => true,
                        'layoutDetailSmallDisabled' => true,
                        'layoutMassUpdateDisabled'  => true,
                        'layoutFiltersDisabled'     => true,
                        'importDisabled'            => true,
                        'exportDisabled'            => true,
                    ];
                }
            }
        }
    }
}
