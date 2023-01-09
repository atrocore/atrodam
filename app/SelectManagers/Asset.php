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
