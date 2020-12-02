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

namespace Dam\Repositories;

use Dam\Core\DAMAttachment;
use Dam\Core\FilePathBuilder;
use Dam\Core\FileStorage\DAMUploadDir;
use Espo\Core\Templates\Repositories\Base;
use Espo\ORM\Entity;

/**
 * Class Asset
 *
 * @package Dam\Repositories
 */
class Asset extends Base implements DAMAttachment
{
    /**
     * @param string $nature
     *
     * @return array
     */
    public function getNatureTypes(string $nature): array
    {
        $types = [];
        foreach ($this->getMetadata()->get(['fields', 'asset', 'typeNatures'], []) as $type => $typeNature) {
            if ($typeNature == $nature) {
                $types[] = $type;
            }
        }

        return $types;
    }

    /**
     * @param Entity $entity
     * @param string $nature
     *
     * @return bool
     */
    public function hasAssetsWithNature(Entity $entity, string $nature): bool
    {
        $relation = $this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'links', 'assets', 'foreign']);
        if (empty($relation)) {
            return false;
        }

        $entity = $this
            ->where(['type' => $this->getNatureTypes($nature), "$relation.id" => $entity->get('id')])
            ->join($relation)
            ->findOne();

        return !empty($entity);
    }

    /**
     * @param Entity $entity
     * @param string $nature
     *
     * @return array
     */
    public function findRelatedAssetsByNature(Entity $entity, string $nature, bool $countOnly = false): array
    {
        $relation = $this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'links', 'assets', 'foreign']);
        if (empty($relation)) {
            return [];
        }

        return $this
            ->where(['type' => $this->getNatureTypes($nature), "$relation.id" => $entity->get('id')])
            ->join($relation)
            ->find()
            ->toArray();
    }

    /**
     * @param Entity $entity
     * @param array  $ids
     *
     * @return array
     */
    public function findRelatedAssetsByIds(Entity $entity, array $ids): array
    {
        $relation = $this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'links', 'assets', 'foreign']);
        if (empty($relation)) {
            return [];
        }

        return $this
            ->where(['id' => $ids, "$relation.id" => $entity->get('id')])
            ->join($relation)
            ->find()
            ->toArray();
    }

    /**
     *
     * @param Entity $entity
     *
     * @return array
     */
    public function buildPath(Entity $entity): array
    {
        return [
            ($entity->get('private') ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "master/",
            $entity->get('private') ? FilePathBuilder::PRIVATE : FilePathBuilder::PUBLIC,
        ];
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     *
     * @return bool
     */
    public function linkAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        return $this->getMapper()->relate($foreign, "relatedAssets", $main) && $this->getMapper()->relate($main, "relatedAssets", $foreign);
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     *
     * @return mixed
     */
    public function unlinkAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        return $this->getMapper()->unrelate($foreign, "relatedAssets", $main);
    }

    /**
     * @inheritDoc
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        // set default album
        if (empty($entity->get('albumId'))) {
            $entity->set('albumId', '1');
        }

        parent::beforeSave($entity, $options);
    }
}
