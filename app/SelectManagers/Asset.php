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

namespace Dam\SelectManagers;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\SelectManagers\Base;

class Asset extends Base
{
    protected function boolFilterLinkedWithAssetCategory(array &$result): void
    {
        $assetCategoryId = (string)$this->getBoolFilterParameter('linkedWithAssetCategory');
        if (empty($assetCategoryId)) {
            return;
        }

        $assetCategory = $this->getEntityManager()->getEntity('AssetCategory', $assetCategoryId);
        if (empty($assetCategory)) {
            throw new BadRequest('No such asset category.');
        }

        $ids = $this->getEntityManager()->getRepository('AssetCategory')->getChildrenRecursivelyArray($assetCategoryId);
        $ids = implode("','", array_merge($ids, [$assetCategoryId]));

        $result['customWhere'] .= " AND asset.id IN (SELECT asset_id FROM `asset_category_asset` WHERE asset_id IS NOT NULL AND deleted=0 AND asset_category_id IN ('$ids'))";
    }

    protected function boolFilterOnlyPrivate(array &$result): void
    {
        $result['customWhere'] .= " AND EXISTS (SELECT e_attachment.id FROM `attachment` e_attachment WHERE e_attachment.id=asset.file_id AND e_attachment.private=1 AND deleted=0)";
    }

    protected function boolFilterOnlyPublic(array &$result): void
    {
        $result['customWhere'] .= " AND EXISTS (SELECT a2.id FROM `attachment` a2 WHERE a2.id=asset.file_id AND a2.private=0 AND deleted=0)";
    }
}
