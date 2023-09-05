<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

declare(strict_types=1);

namespace Dam\Listeners;

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
        if (empty($assetTypes)) {
            $query = "SELECT id, name, assign_Automatically as assignAutomatically, types_to_exclude as typesToExclude FROM asset_type WHERE deleted=0 ORDER BY sort_order";
            try {
                $assetTypes = $this->getContainer()->get('pdo')->query($query)->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($assetTypes as &$assetType) {
                    $assetType['assignAutomatically'] = !empty($assetType['assignAutomatically']);
                    $assetType['typesToExclude'] = !empty($assetType['typesToExclude']) ? @json_decode((string)$assetType['typesToExclude'], true) : [];
                }
                unset($assetType);

                $this->getContainer()->get('dataManager')->setCacheData('assetTypes', $assetTypes);
            } catch (\Throwable $e) {
                $assetTypes = [];
            }
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
                if (!empty($linkData['entity']) && $linkData['entity'] == 'Asset') {
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['view'] = "dam:views/record/panels/assets";
                }
            }
        }
    }
}
