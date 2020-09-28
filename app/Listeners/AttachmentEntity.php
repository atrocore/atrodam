<?php

declare(strict_types=1);

namespace Dam\Listeners;

use Dam\Entities\Attachment;
use Espo\Core\Exceptions\InternalServerError;
use Treo\Core\EventManager\Event;
use Treo\Core\Utils\File\Manager;
use Treo\Listeners\AbstractListener;

/**
 * Class AssetEntity
 *
 * @package Dam\Listeners
 */
class AttachmentEntity extends AbstractListener
{
    /**
     * @param Event $event
     * @throws InternalServerError
     */
    public function beforeSave(Event $event)
    {
        /**@var $entity Attachment* */
        $entity = $event->getArgument('entity');

        if ($entity->isNew() && $entity->get("contents") && $entity->get('relatedType') === "Asset") {
            $entity->set('hash_md5', md5($entity->get("contents")));
        }
    }

    /**
     * @param Event $event
     */
    public function afterSave(Event $event)
    {

    }

    /**
     * @param Event $event
     */
    public function afterRemove(Event $event)
    {
        $entity = $event->getArgument('entity');

        $this->getEntityManager()->getRepository("Attachment")->removeThumbs($entity);
    }

    /**
     * @return Manager
     */
    protected function getFileManager(): Manager
    {
        return $this->container->get('FileManager');
    }
}
