<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.txt, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

declare(strict_types=1);

namespace Dam\Listeners;

use Atro\ORM\DB\RDB\Mapper;
use Doctrine\DBAL\Connection;
use Espo\Listeners\AbstractListener;
use Espo\Core\EventManager\Event;

class Metadata extends AbstractListener
{
    public function modify(Event $event): void
    {
        $data = $event->getArgument('data');

        $this->updateRelationshipPanels($data);

        if ($this->getConfig()->get('isInstalled', false)) {
            $typesData = $this->getAssetTypes();
            $data['fields']['asset']['types'] = array_column($typesData, 'name');
            $data['fields']['asset']['hasPreviewExtensions'][] = 'pdf';
            $data['entityDefs']['Asset']['fields']['type']['options'] = $data['fields']['asset']['types'];
            $data['entityDefs']['Asset']['fields']['type']['optionsIds'] = array_column($typesData, 'id');
            $data['entityDefs']['Asset']['fields']['type']['assignAutomatically'] = [];
            foreach ($typesData as $item) {
                if (!empty($item['assignAutomatically'])) {
                    $data['entityDefs']['Asset']['fields']['type']['assignAutomatically'][] = $item['name'];
                }
                if (!empty($item['typesToExclude'])) {
                    $data['entityDefs']['Asset']['fields']['type']['typesToExclude'][$item['name']] = $item['typesToExclude'];
                }
            }
        }

        $event->setArgument('data', $data);
    }

    protected function getAssetTypes(): array
    {
        $assetTypes = $this->getContainer()->get('dataManager')->getCacheData('assetTypes');
        if (!is_array($assetTypes)) {
            /** @var Connection $connection */
            $connection = $this->getContainer()->get('connection');

            try {
                $res = $connection->createQueryBuilder()
                    ->select('at.id, at.name, at.assign_automatically, at.types_to_exclude')
                    ->from($connection->quoteIdentifier('asset_type'), 'at')
                    ->where('at.deleted = :false')
                    ->setParameter('false', false, Mapper::getParameterType(false))
                    ->orderBy('at.sort_order', 'ASC')
                    ->fetchAllAssociative();

                $assetTypes = [];
                foreach ($res as $k => $row) {
                    $assetTypes[$k]['id'] = $row['id'];
                    $assetTypes[$k]['name'] = $row['name'];
                    $assetTypes[$k]['assignAutomatically'] = !empty($row['assign_automatically']);
                    $assetTypes[$k]['typesToExclude'] = !empty($row['types_to_exclude']) ? @json_decode((string)$row['types_to_exclude'], true) : [];
                }
            } catch (\Throwable $e) {
                $assetTypes = [];
            }
            $this->getContainer()->get('dataManager')->setCacheData('assetTypes', $assetTypes);
        }

        return $assetTypes;
    }

    protected function updateRelationshipPanels(array &$data): void
    {
        foreach ($data['entityDefs'] as $scope => $defs) {
            if (empty($defs['links'])) {
                continue 1;
            }
            foreach ($defs['links'] as $link => $linkData) {
                if (!empty($linkData['entity']) && $linkData['entity'] == 'Asset' && !isset($data['clientDefs'][$scope]['relationshipPanels'][$link]['view'])) {
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['view'] = "dam:views/record/panels/assets";
                }
            }
        }
    }
}
