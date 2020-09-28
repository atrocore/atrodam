<?php

declare(strict_types=1);

namespace Dam\Listeners;

use Dam\Entities\Collection;
use Dam\Listeners\Traits\ValidateCode;
use Espo\Core\Exceptions\BadRequest;
use Treo\Core\EventManager\Event;
use Treo\Listeners\AbstractListener;

/**
 * Class AssetEntity
 *
 * @package Dam\Listeners
 */
class CollectionEntity extends AbstractListener
{
    use ValidateCode;

    /**
     * @param Event $event
     * @throws BadRequest
     */
    public function beforeSave(Event $event)
    {
        /**@var $entity Collection* */
        $entity = $event->getArgument('entity');

        if (!$this->isValidCode($entity)) {
            throw new BadRequest($this->getLanguage()->translate('Code is invalid', 'exceptions', 'Global'));
        }
    }

    /**
     * @param Event $event
     */
    public function afterSave(Event $event)
    {
        /**@var $entity Collection* */
        $entity = $event->getArgument("entity");

        if ($entity->isAttributeChanged("isDefault")) {
            $this->getService("Collection")->updateDefault($entity);
        }
    }

    /**
     * @param Event $event
     * @throws BadRequest
     */
    public function beforeRelate(Event $event)
    {
        if ($event->getArgument('relationName') == "assetCategories" && !$this->isValidCategory($event->getArgument('foreign'))) {
            throw new BadRequest("Only root category");
        }
    }

    /**
     * @param $entity
     * @return bool
     * @throws \Espo\Core\Exceptions\Error
     */
    protected function isValidCategory($entity)
    {
        if (is_string($entity)) {
            $entity = $this->getEntityManager()->getEntity("AssetCategory", $entity);
        }

        return !$entity->get("categoryParentId");
    }

}
