<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.txt, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

declare(strict_types=1);

namespace Dam;

use Atro\ORM\DB\RDB\Mapper;
use Dam\Repositories\Attachment;
use Espo\Core\Utils\Config;
use Espo\ORM\EntityManager;
use Treo\Core\ModuleManager\AfterInstallAfterDelete;
use Espo\Core\Utils\Metadata;

class Event extends AfterInstallAfterDelete
{
    protected array $searchEntities
        = [
            'Asset',
            'AssetCategory',
            'Library',
        ];

    protected array $menuItems
        = [
            'Asset',
            'AssetCategory',
            'Library',
        ];

    public function afterInstall(): void
    {
        $this->addGlobalSearchEntities();
        $this->addMenuItems();
        if ($this->getConfig()->get('isInstalled')) {
            $this->insertDbData();
            $this->createAssets();
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
        $connection = $this->getEntityManager()->getConnection();

        $res = $connection->createQueryBuilder()
            ->select('sj.id')
            ->from($connection->quoteIdentifier('scheduled_job'), 'sj')
            ->where('sj.job = :job')
            ->setParameter('job', 'PdfTemplate')
            ->fetchAllAssociative();

        $ids = array_column($res, 'id');

        $connection->createQueryBuilder()
            ->delete($connection->quoteIdentifier('job'), 'j')
            ->where('j.scheduled_job_id IN (:ids)')
            ->setParameter('ids', $ids, Mapper::getParameterType($ids))
            ->executeQuery();

        $connection->createQueryBuilder()
            ->delete($connection->quoteIdentifier('scheduled_job'))
            ->where('id IN (:ids)')
            ->setParameter('ids', $ids, Mapper::getParameterType($ids))
            ->executeQuery();
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
     * Add menu items
     */
    protected function addMenuItems()
    {
        // get config data
        $tabList = $this->getConfig()->get("tabList", []);
        $quickCreateList = $this->getConfig()->get("quickCreateList", []);
        $twoLevelTabList = $this->getConfig()->get("twoLevelTabList", []);

        $twoLevelTabListItems = [];
        foreach ($twoLevelTabList as $item) {
            if (is_string($item)) {
                $twoLevelTabListItems[] = $item;
            } else {
                $twoLevelTabListItems = array_merge($twoLevelTabListItems, $item->items);
            }
        }

        foreach ($this->menuItems as $item) {
            if (!in_array($item, $tabList)) {
                $tabList[] = $item;
            }
            if (!in_array($item, $quickCreateList)) {
                $quickCreateList[] = $item;
            }
            if (!in_array($item, $twoLevelTabListItems)) {
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
     * Insert demo data to DB
     */
    protected function insertDbData()
    {
        $connection = $this->getEntityManager()->getConnection();

        $connection->createQueryBuilder()
            ->insert($connection->quoteIdentifier('asset_type'))
            ->setValue('id', ':id')
            ->setValue($connection->quoteIdentifier('name'), ':name')
            ->setValue('created_at', ':date')
            ->setValue('created_by_id', ':userId')
            ->setParameter('id', 'file')
            ->setParameter('name', 'File')
            ->setParameter('date', (new \DateTime())->format('Y-m-d H:i:s'))
            ->setParameter('userId', '1')
            ->executeQuery();

        $connection->createQueryBuilder()
            ->insert($connection->quoteIdentifier('library'))
            ->setValue('id', ':id')
            ->setValue($connection->quoteIdentifier('name'), ':name')
            ->setValue('is_active', ':true')
            ->setValue('created_at', ':date')
            ->setValue('created_by_id', ':userId')
            ->setParameter('id', '1')
            ->setParameter('name', 'Default Library')
            ->setParameter('true', true, Mapper::getParameterType(true))
            ->setParameter('date', (new \DateTime())->format('Y-m-d H:i:s'))
            ->setParameter('userId', '1')
            ->executeQuery();
    }

    protected function execute(string $query): void
    {
        try {
            $this->getContainer()->get('pdo')->exec($query);
        } catch (\Throwable $e) {
            // ignore all
        }
    }

    /**
     * Create assets if it needs
     */
    protected function createAssets()
    {
        /** @var Attachment $attachmentRepository */
        $attachmentRepository = $this->getEntityManager()->getRepository('Attachment');

        $attachments = $attachmentRepository->find();
        if ($attachments->count() > 0) {
            foreach ($attachments as $attachment) {
                // get field type
                $fieldType = $this->getMetadata()->get(['entityDefs', $attachment->get('relatedType'), 'fields', $attachment->get('field'), 'type']);

                if ($fieldType === 'asset') {
                    try {
                        $attachmentRepository->createAsset($attachment, true);
                    } catch (\Throwable $e) {
                        // ignore validations
                    }
                }
            }
        }
    }

    protected function getMetadata(): Metadata
    {
        return $this->container->get('metadata');
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->container->get('entityManager');
    }

    protected function getConfig(): Config
    {
        return $this->getContainer()->get('config');
    }
}
