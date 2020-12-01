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

use Dam\Core\FilePathBuilder;
use Dam\Entities\Asset;
use Dam\Entities\AssetCategory;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\ORM\Entity;
use PDO;
use Treo\Core\EventManager\Event;

/**
 * Class AssetEntity
 *
 * @package Dam\Listeners
 */
class AssetEntity extends AbstractListener
{
    /**
     * @param Event $event
     * @throws BadRequest
     * @throws Error
     */
    public function beforeSave(Event $event)
    {
        /**@var $entity Asset* */
        $entity = $event->getArgument('entity');

        if (!$entity->isNew() && !$entity->get("isActive") && $entity->isAttributeChanged("collectionId")) {
            throw new BadRequest($this->getLanguage()->translate('You can not change collection', 'exceptions', 'Asset'));
        }

        if (!$this->isValidCode($entity)) {
            throw new BadRequest($this->getLanguage()->translate('Code is invalid', 'exceptions', 'Global'));
        }

        if (!$entity->isNew() && $entity->isAttributeChanged("type")) {
            throw new BadRequest("You can't change type");
        }

        //Change path for new entity or after change private
        if ($entity->isNew() || $entity->isAttributeChanged("private")) {
            $entity->set('path', $this->setPath($entity));
        }

        //After update image
        if ($this->changeAttachment($entity)) {
            $this->getService("Asset")->getFileInfo($entity);
        }

        //After create new asset
        if ($entity->isNew()) {
            //move from tmp to master storage
            $this->getService("Attachment")->moveToMaster($entity);
        }

        //After upload new file|image
        if ($this->changeAttachment($entity) && !$entity->isNew()) {
            $this->getService("Attachment")->moveToMaster($entity);
        }

        //After change private (move to other folder)
        if (!$entity->isNew() && $entity->isAttributeChanged("private")) {
            $this->getService("Attachment")->changeAccess($entity);
        }

        //rename file
        if (!$entity->isNew() && $entity->isAttributeChanged("nameOfFile")) {
            $this->getService("Attachment")->changeName($entity->get('file'), $entity->get('nameOfFile'), $entity);
        }

        //deactivate asset
        if ($this->isDeactivateAsset($entity) && $this->getService("Asset")->getRelationsCount($entity)) {
            throw new BadRequest("You can't deactivate this asset");
        }
    }

    /**
     * @param Event $event
     */
    public function afterSave(Event $event)
    {
        /** @var $entity Asset */
        $entity = $event->getArgument("entity");

        //get meta data
        if ($this->changeAttachment($entity)) {
            $this->getService("Asset")->updateMetaData($entity);
        }

        if ($entity->isAttributeChanged("fileId") && !$entity->isNew()) {
            $this->getService("Attachment")->deleteAttachment($entity->getFetched("fileId"), $entity->getEntityType());
        }
    }

    /**
     * @param Event $event
     *
     * @throws BadRequest
     */
    public function beforeRelate(Event $event)
    {
        $foreign = $event->getArgument('foreign');
        $entity  = $event->getArgument('entity');

        if (is_string($foreign) && $event->getArgument("relationName") === "assetsLeft") {
            $foreign = $this->getEntityManager()->getEntity("Asset", $foreign);
        }

        //check any relation for inactive assets
        if (!$entity->get("isActive")) {
            throw new BadRequest($this->getLanguage()->translate("CantAddInActive", 'exceptions', 'Asset'));
        }

        //create leftAsset relation
        if (is_a($entity, Asset::class) && is_a($foreign, Asset::class)) {
            $this->getService("Asset")->linkToAsset($entity, $foreign);
        }

        //check join with last (list) category
        if ($event->getArgument("relationName") === "assetCategories") {
            if ($this->isLast($event, $foreign)) {
                throw new BadRequest($this->getLanguage()->translate("Category is not last", 'exceptions', 'Global'));
            }
        }

        //check if this is collection category
        if ($event->getArgument("relationName") === "collection") {
            if ($this->isCollectionCatalog($entity, $foreign)) {
                throw new BadRequest($this->getLanguage()->translate("Category is not set in collection", 'exceptions', 'Global'));
            }
        }
    }

    /**
     * @param Event $event
     */
    public function beforeUnrelate(Event $event)
    {
        $foreign = $event->getArgument('foreign');
        $entity  = $event->getArgument('entity');

        //remove leftAsset relation
        if (is_a($entity, Asset::class) && is_a($foreign, Asset::class)) {
            $this->getService("Asset")->unlinkToAsset($entity, $foreign);
        }
    }

    /**
     * @param Event $event
     */
    public function afterRemove(Event $event)
    {
        $entity       = $event->getArgument("entity");
        $attachmentId = $entity->get("fileId");

        if ($attachmentId) {
            $this->getService("Attachment")->toDelete($attachmentId);
        }

        $this->getService("Rendition")->toDeleteCollection($entity->get("renditions"));
    }

    /**
     * @param $entity
     * @param $foreign
     * @throws BadRequest
     */
    protected function isCollectionCatalog($entity, $foreign)
    {
        $collection = $entity->get('collection');

        $route   = $foreign->get('categoryRoute');
        $routeEl = array_filter(explode("|", $route ?? ''), function ($item) {
            return !empty($item);
        });

        if (!$this->isCorrectCategory($collection->id, $routeEl)) {
            throw new BadRequest("Incorrect catalog");
        }
    }

    /**
     * @param $collectionId
     * @param $categories
     * @return bool
     */
    protected function isCorrectCategory($collectionId, $categories): bool
    {
        $pdo = $this->getEntityManager()->getPDO();

        $sql = "SELECT 1 FROM collection_asset_category WHERE asset_category_id IN ('" . implode("','", $categories) . "') AND collection_id = '{$collectionId}'";

        $prepare = $pdo->query($sql);
        $res     = $prepare->fetch(PDO::FETCH_ASSOC);

        return $res ? true : false;
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    private function hasChild($entity): bool
    {
        if (is_string($entity)) {
            $entity = $this->getEntityManager()->getRepository("AssetCategory")->where(['id' => $entity])->findOne();
        }

        if (!is_a($entity, AssetCategory::class)) {
            return false;
        }

        return $entity->get('hasChild');
    }

    /**
     * @param Event $event
     * @param       $entity
     *
     * @return bool
     */
    private function isLast(Event $event, $entity): bool
    {
        return $event->getArgument('relationName') == "assetCategories" && $entity && $this->hasChild($entity);
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    private function changeAttachmentOrPrivate(Entity $entity)
    {
        return $entity->isAttributeChanged('private') || $this->changeAttachment($entity);
    }

    /**
     * @param Entity $entity
     * @return mixed
     */
    private function changeAttachment(Entity $entity)
    {
        return $entity->isAttributeChanged('fileId');
    }

    /**
     * @return mixed
     */
    private function getFilePathBuilder()
    {
        return $this->getContainer()->get('filePathBuilder');
    }

    /**
     * @param Entity $entity
     * @return string
     */
    private function setPath(Entity $entity): string
    {
        /**@var $repository \Dam\Repositories\Asset* */
        $repository = $this->getEntityManager()->getRepository($entity->getEntityType());

        do {
            $type = $entity->get('private') ? FilePathBuilder::PRIVATE : FilePathBuilder::PUBLIC;
            $path = $this->getFilePathBuilder()->createPath($type, "master");

            $count = $repository->where(['path' => $path])->count();

        } while ($count);

        return $path;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    private function isDeactivateAsset(Entity $entity)
    {
        return $entity->isAttributeChanged("isActive") && !$entity->get("isActive");
    }
}
