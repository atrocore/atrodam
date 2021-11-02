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

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Utils\Util;
use Espo\ORM\Entity;

/**
 * Class Asset
 *
 * @package Dam\Repositories
 */
class Asset extends AbstractRepository
{
    /**
     * @param Entity $entity
     * @param string $type
     *
     * @return array
     */
    public function findRelatedAssetsByType(Entity $entity, string $type): array
    {
        if (method_exists($this->getEntityManager()->getRepository($entity->getEntityType()), 'findRelatedAssetsByType')) {
            return $this->getEntityManager()->getRepository($entity->getEntityType())->findRelatedAssetsByType($entity, $type);
        }

        $relation = $this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'links', 'assets']);
        if (empty($relation['foreign']) || empty($relation['relationName'])) {
            return [];
        }

        $relationTableName = Util::toUnderScore($relation['relationName']);
        $entityTableName = Util::toUnderScore(lcfirst($entity->getEntityType()));
        $id = $entity->get('id');

        $sql = "SELECT a.*, at.id as fileId, at.name as fileName
                FROM $relationTableName r 
                LEFT JOIN asset a ON a.id=r.asset_id
                LEFT JOIN attachment at ON at.id=a.file_id 
                WHERE 
                      r.deleted=0 
                  AND a.deleted=0
                  AND at.deleted=0 
                  AND a.type='$type' 
                  AND r.{$entityTableName}_id='$id' 
                ORDER BY r.sorting ASC";

        return $this->findByQuery($sql)->toArray();
    }

    /**
     * @param string $scope
     * @param string $entityId
     * @param array  $ids
     *
     * @return bool
     */
    public function updateSortOrder(string $scope, string $entityId, array $ids): bool
    {
        if (method_exists($this->getEntityManager()->getRepository($scope), 'updateSortOrder')) {
            return $this->getEntityManager()->getRepository($scope)->updateSortOrder($entityId, $ids);
        }

        $relation = $this->getMetadata()->get(['entityDefs', $scope, 'links', 'assets']);
        if (empty($relation['foreign']) || empty($relation['relationName'])) {
            return false;
        }

        $relationTableName = Util::toUnderScore($relation['relationName']);
        $entityTableName = Util::toUnderScore(lcfirst($scope));

        foreach ($ids as $k => $id) {
            $sorting = $k * 10;
            $this->getEntityManager()->nativeQuery("UPDATE $relationTableName SET sorting=$sorting WHERE asset_id='$id' AND {$entityTableName}_id='$entityId' AND deleted=0");
        }

        return true;
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
        // set defaults
        if (empty($entity->get('libraryId'))) {
            $entity->set('libraryId', '1');
        }
        if (empty($entity->get('type'))) {
            $entity->set('type', 'File');
        }

        if (!empty($url = $entity->get('url'))) {
            $attachment = $this->getInjection('serviceFactory')->create('Attachment')->createEntityByUrl((string)$url);
            $entity->set('fileId', $attachment->get('id'));
            $entity->set('fileName', $attachment->get('name'));
        }

        // prepare name
        if (empty($entity->get('name'))) {
            $entity->set('name', $entity->get('file')->get('name'));
        }

        if (!preg_match("/^(?!(?:COM[0-9]|CON|LPT[0-9]|NUL|PRN|AUX|com[0-9]|con|lpt[0-9]|nul|prn|aux)|[\s\.])[^\\\\\/:\*\"\?<>%|\s\r\n=,]{1,254}$/", (string)$entity->get('name'))) {
            throw new BadRequest($this->translate('fileNameNotValid', 'exceptions', 'Asset'));
        }

        if (empty(str_replace('/', '', (string)$entity->get('name')))) {
            throw new BadRequest($this->translate('assetNameIsInvalid', 'exceptions', 'Asset'));
        }

        parent::beforeSave($entity, $options);
    }

    protected function init()
    {
        parent::init();

        $this->addDependency('serviceFactory');
    }
}
