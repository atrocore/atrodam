<?php

declare(strict_types=1);

namespace Dam\Listeners;

use Treo\Core\EventManager\Event;
use Treo\Listeners\AbstractListener;

/**
 * Class AssetRelationEntity
 * @package Dam\Listeners
 */
class AssetRelationEntity extends AbstractListener
{
    /**
     * @param Event $event
     */
    public function afterRemove(Event $event)
    {
        /** @var $entity \Espo\Core\ORM\Entity */
        $entity = $event->getArgument("entity");

        $this->getService($entity->getEntityType())->deleteRelation($entity);
    }
}
