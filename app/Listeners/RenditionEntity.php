<?php

declare(strict_types=1);

namespace Dam\Listeners;

use Dam\Core\ConfigManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\Entity;
use Treo\Core\EventManager\Event;
use Treo\Listeners\AbstractListener;

/**
 * Class AssetCategoryEntity
 *
 * @package Dam\Listeners
 *
 */
class RenditionEntity extends AbstractListener
{
    /**
     * @param Event $event
     */
    public function beforeSave(Event $event)
    {
        /**@var $entity Entity* */
        $entity = $event->getArgument("entity");

        if ($entity->isNew() && $this->typeDuplicate($entity)) {
            throw new BadRequest($this->getLanguage()->translate("Renditions with this type already exist"));
        }

        if (!$entity->isNew() && $entity->isAttributeChanged("type")) {
            throw new BadRequest("You can't change type");
        }

        if ($entity->isNew()) {
            $this->getService($entity->getEntityType())->validateType($entity);
        }

        $attachment = $entity->get("file");
        if ($attachment->get("tmpPath")) {
            $this->getService("Attachment")->moveToRendition($entity, $attachment);
        }

        if ($this->changeAttachment($entity)) {
            $asset = $this->getService("Asset")->getEntity($entity->get("assetId"));
            $this->getService($entity->getEntityType())->updateAttachmentInfo($entity, $asset);
        }

        if (!$entity->get("nameOfFile")) {
            $this->getService($entity->getEntityType())->createNameOfFile($entity);
        } elseif ($entity->isAttributeChanged("nameOfFile")) {
            $this->getService("Attachment")
                 ->changeName($attachment, $entity->get("nameOfFile"), $entity);
        }

        if (!$entity->isNew() && $entity->isAttributeChanged("private")) {
            $this->getService("Attachment")->changeAccess($entity);
        }
    }

    /**
     * @param Event $event
     */
    public function afterSave(Event $event)
    {
        /**@var $entity \Dam\Entities\Rendition* */
        $entity = $event->getArgument('entity');

        if ($entity->isNew() || $this->changeAttachment($entity)) {
            $this->getService("Rendition")->updateMetaData($entity);
        }

        if ($entity->isAttributeChanged("fileId") && !$entity->isNew()) {
            $this->getService("Attachment")->deleteAttachment($entity->getFetched("fileId"), $entity->getEntityType());
        }
    }

    public function afterRemove(Event $event)
    {
        $entity = $event->getArgument("entity");

        $attachmentId = $entity->get("fileId");

        if ($attachmentId) {
            $this->getService("Attachment")->toDelete($attachmentId);
        }
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    protected function typeDuplicate(Entity $entity)
    {
        $assetId = $entity->get("assetId");
        $type    = $entity->get("type");

        $res = $this->getRepository($entity->getEntityName())->where([
            'assetId' => $assetId,
            'type'    => $type,
        ])->count();

        return $res ? true : false;

    }

    /**
     * @param Entity $entity
     * @return mixed
     */
    protected function changeAttachment(Entity $entity)
    {
        return $entity->isAttributeChanged("fileId");
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager(): ConfigManager
    {
        return $this->container->get("ConfigManager");
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function getRepository($name)
    {
        return $this->getEntityManager()->getRepository($name);
    }
}
