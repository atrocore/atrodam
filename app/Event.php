<?php

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

        //init DAM configs
        $this->installConfig();

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

        // remove applicationName
        $this->removeApplicationName();
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
     * @return bool
     */
    protected function installConfig()
    {
        if (file_exists("data/dam/config.yaml")) {
            return true;
        }

        $damModule = $this->getContainer()->get('moduleManager')->getModule("Dam");

        if (!is_dir("data/dam")) {
            mkdir("data/dam");
        }

        copy($damModule->getPath() . "/app/config.yaml", "data/dam/config.yaml");

        file_put_contents(
            "data/dam/config.php",
            "<?php " . PHP_EOL . "return " . $this->container->get("FileManager")->varExport(yaml_parse_file("data/dam/config.yaml")) . ";" . PHP_EOL
        );
    }

    /**
     * Set ApplicationName
     */
    protected function setApplicationName()
    {
        if (!$this->getMetadata()->isModuleInstalled('PIM')) {
            $this->getConfig()->set('applicationName', 'AtroDAM');
        }

        // save
        $this->getConfig()->save();
    }

    /**
     * Remove ApplicationName
     */
    protected function removeApplicationName()
    {
        if (!$this->getMetadata()->isModuleInstalled('PIM')) {
            $this->getConfig()->set('applicationName', 'AtroCore');
        }

        // save
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
