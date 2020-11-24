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

namespace Dam;

use Treo\Core\ModuleManager\AbstractEvent;
use Treo\Core\Utils\Config;
use Treo\Core\Utils\Metadata;

/**
 * Class Event
 *
 * @package Dam
 */
class Event extends AbstractEvent
{
    /**
     * @var array
     */
    protected $searchEntities
        = [
            'Asset',
            'AssetCategory',
            'Collection',
        ];

    /**
     * @var array
     */
    protected $menuItems
        = [
            'Asset',
            'AssetCategory',
            'Collection',
        ];

    /**
     * @inheritdoc
     */
    public function afterInstall(): void
    {
        // add global search
        $this->addGlobalSearchEntities();

        // add menu items
        $this->addMenuItems();

        // add units
        $this->addUnit();

        // set applicationName
        $this->setApplicationName();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
        // delete global search
        $this->deleteGlobalSearchEntities();

        // delete menu items
        $this->deleteMenuItems();
    }

    /**
     * Add new Unit
     */
    protected function addUnit(): void
    {
        $unitsOfMeasure = $this->getConfig()->get("unitsOfMeasure", new \stdClass());

        $name = "File Size";

        if (!property_exists($unitsOfMeasure, $name)) {
            $unitsOfMeasure->{$name} = (object)[
                'unitList'  => [
                    'kb',
                ],
                'baseUnit'  => 'kb',
                'unitRates' => (object)[],
            ];

            $this->getConfig()->set("unitsOfMeasure", $unitsOfMeasure);
            $this->getConfig()->save();
        }
    }

    /**
     * Add global search entities
     */
    protected function addGlobalSearchEntities(): void
    {
        // get config data
        $globalSearchEntityList = $this->getConfig()->get("globalSearchEntityList", []);

        foreach ($this->searchEntities as $entity) {
            if (!in_array($entity, $globalSearchEntityList)) {
                $globalSearchEntityList[] = $entity;
            }
        }

        // set to config
        $this->getConfig()->set('globalSearchEntityList', $globalSearchEntityList);

        // save
        $this->getConfig()->save();
    }

    /**
     * Delete global search entities
     */
    protected function deleteGlobalSearchEntities(): void
    {
        $globalSearchEntityList = [];
        foreach ($this->getConfig()->get("globalSearchEntityList", []) as $entity) {
            if (!in_array($entity, $this->searchEntities)) {
                $globalSearchEntityList[] = $entity;
            }
        }

        // set to config
        $this->getConfig()->set('globalSearchEntityList', $globalSearchEntityList);

        // save
        $this->getConfig()->save();
    }


    /**
     * Add menu items
     */
    protected function addMenuItems()
    {
        // get config data
        $tabList = $this->getConfig()->get("tabList", []);
        $quickCreateList = $this->getConfig()->get("quickCreateList", []);
        $twoLevelTabList = $this->getConfig()->get("twoLevelTabList", []);

        foreach ($this->menuItems as $item) {
            if (!in_array($item, $tabList)) {
                $tabList[] = $item;
            }
            if (!in_array($item, $quickCreateList)) {
                $quickCreateList[] = $item;
            }
            if (!in_array($item, $twoLevelTabList)) {
                $twoLevelTabList[] = $item;
            }
        }

        // set to config
        $this->getConfig()->set('tabList', $tabList);
        $this->getConfig()->set('quickCreateList', $quickCreateList);
        $this->getConfig()->set('twoLevelTabList', $twoLevelTabList);

        // save
        $this->getConfig()->save();
    }

    /**
     * Delete menu items
     */
    protected function deleteMenuItems()
    {
        // for tabList
        $tabList = [];
        foreach ($this->getConfig()->get("tabList", []) as $entity) {
            if (!in_array($entity, $this->menuItems)) {
                $tabList[] = $entity;
            }
        }
        $this->getConfig()->set('tabList', $tabList);

        // for quickCreateList
        $quickCreateList = [];
        foreach ($this->getConfig()->get("quickCreateList", []) as $entity) {
            if (!in_array($entity, $this->menuItems)) {
                $quickCreateList[] = $entity;
            }
        }
        $this->getConfig()->set('quickCreateList', $quickCreateList);

        // for twoLevelTabList
        $twoLevelTabList = [];
        foreach ($this->getConfig()->get("twoLevelTabList", []) as $entity) {
            if (!in_array($entity, $this->menuItems)) {
                $twoLevelTabList[] = $entity;
            }
        }
        $this->getConfig()->set('twoLevelTabList', $twoLevelTabList);

        // save
        $this->getConfig()->save();
    }

    /**
     * Set ApplicationName
     */
    protected function setApplicationName()
    {
        if (!in_array($this->getConfig()->get('applicationName'), ['AtroCORE'])) {
            return;
        }

        $this->getConfig()->set('applicationName', 'AtroDAM');
        $this->getConfig()->save();
    }

    /**
     * Get Metadata
     *
     * @return Metadata
     */
    protected function getMetadata(): Metadata
    {
        return $this->container->get('metadata');
    }

    /**
     * @return Config
     */
    protected function getConfig(): Config
    {
        return $this->getContainer()->get('config');
    }
}
