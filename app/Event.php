<?php

declare(strict_types=1);

namespace Dam;

use Dam\Core\Utils\Util;
use DamCommon\Services\MigrationPimImage;
use Treo\Core\ModuleManager\AbstractEvent;
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
    protected $searchEntities = [
        'Asset',
        'AssetCategory',
        'Collection',
    ];

    /**
     * @var array
     */
    protected $menuItems = [
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
        //for Pim
        $this->migratePim();
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
        $config         = $this->getContainer()->get('config');
        $unitsOfMeasure = $config->get("unitsOfMeasure", []);
        $name           = "File Size";

        if (!property_exists($unitsOfMeasure, $name)) {
            $unitsOfMeasure->{$name} = (object)[
                'unitList'  => [
                    'kb',
                ],
                'baseUnit'  => 'kb',
                'unitRates' => (object)[],
            ];

            $config->set("unitsOfMeasure", $unitsOfMeasure);
            $config->save();
        }
    }

    /**
     * Add global search entities
     */
    protected function addGlobalSearchEntities(): void
    {
        // get config
        $config = $this->getContainer()->get('config');

        // get config data
        $globalSearchEntityList = $config->get("globalSearchEntityList", []);

        foreach ($this->searchEntities as $entity) {
            if (!in_array($entity, $globalSearchEntityList)) {
                $globalSearchEntityList[] = $entity;
            }
        }

        // set to config
        $config->set('globalSearchEntityList', $globalSearchEntityList);

        // save
        $config->save();
    }

    /**
     * Delete global search entities
     */
    protected function deleteGlobalSearchEntities(): void
    {
        // get config
        $config = $this->getContainer()->get('config');

        $globalSearchEntityList = [];
        foreach ($config->get("globalSearchEntityList", []) as $entity) {
            if (!in_array($entity, $this->searchEntities)) {
                $globalSearchEntityList[] = $entity;
            }
        }

        // set to config
        $config->set('globalSearchEntityList', $globalSearchEntityList);

        // save
        $config->save();
    }


    /**
     * Add menu items
     */
    protected function addMenuItems()
    {
        // get config
        $config = $this->getContainer()->get('config');

        // get config data
        $tabList         = $config->get("tabList", []);
        $quickCreateList = $config->get("quickCreateList", []);
        $twoLevelTabList = $config->get("twoLevelTabList", []);

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
        $config->set('tabList', $tabList);
        $config->set('quickCreateList', $quickCreateList);
        $config->set('twoLevelTabList', $twoLevelTabList);

        // save
        $config->save();
    }

    /**
     * Delete menu items
     */
    protected function deleteMenuItems()
    {
        // get config
        $config = $this->getContainer()->get('config');

        // for tabList
        $tabList = [];
        foreach ($config->get("tabList", []) as $entity) {
            if (!in_array($entity, $this->menuItems)) {
                $tabList[] = $entity;
            }
        }
        $config->set('tabList', $tabList);

        // for quickCreateList
        $quickCreateList = [];
        foreach ($config->get("quickCreateList", []) as $entity) {
            if (!in_array($entity, $this->menuItems)) {
                $quickCreateList[] = $entity;
            }
        }
        $config->set('quickCreateList', $quickCreateList);

        // for twoLevelTabList
        $twoLevelTabList = [];
        foreach ($config->get("twoLevelTabList", []) as $entity) {
            if (!in_array($entity, $this->menuItems)) {
                $twoLevelTabList[] = $entity;
            }
        }
        $config->set('twoLevelTabList', $twoLevelTabList);

        // save
        $config->save();
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
        // get config
        $config = $this->getContainer()->get('config');

        if (!$this->getMetadata()->isModuleInstalled('PIM')) {
            $config->set('applicationName', 'TreoDAM');
        }

        // save
        $config->save();
    }

    /**
     * Remove ApplicationName
     */
    protected function removeApplicationName()
    {
        // get config
        $config = $this->getContainer()->get('config');

        if (!$this->getMetadata()->isModuleInstalled('PIM')) {
            $config->set('applicationName', 'TreoCore');
        }

        // save
        $config->save();
    }

    /**
     * @throws \Espo\Core\Exceptions\Error
     */
    protected function migratePim(): void
    {
        $config = $this->getContainer()->get('config');

        if (!empty($config->get('isInstalled'))
            && $config->get('pimAndDamInstalled') === false
            && $this->getMetadata()->isModuleInstalled('Pim')) {
            //migration pimImage
            $migrationPimImage = new MigrationPimImage();
            $migrationPimImage->setContainer($this->getContainer());
            $migrationPimImage->run();

            //set flag about installed Pim and Image
            $config->set('pimAndDamInstalled', true);
            $config->save();
        }
    }

    /**
     * Get Metadata
     * @return Metadata
     */
    protected function getMetadata(): Metadata
    {
        return $this->container->get('metadata');
    }

}
