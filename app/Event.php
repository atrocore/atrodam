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
 *
 *  This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam;

use Dam\Repositories\Attachment;
use Espo\Core\Utils\Config;
use Treo\Core\ModuleManager\AfterInstallAfterDelete;
use Espo\Core\Utils\Metadata;

/**
 * Class Event
 */
class Event extends AfterInstallAfterDelete
{
    /**
     * @var array
     */
    protected $searchEntities
        = [
            'Asset',
            'AssetCategory',
            'Library',
        ];

    /**
     * @var array
     */
    protected $menuItems
        = [
            'Asset',
            'AssetCategory',
            'Library',
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

        if ($this->getConfig()->get('isInstalled')) {
            // insert DB data to DB
            $this->insertDbData();

            // create assets
            $this->createAssets();
        }

        // set applicationName
        $this->setApplicationName();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
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
        $this->execute("INSERT INTO `asset_type` (`id`, `name`, `deleted`, `created_at`, `modified_at`, `created_by_id`, `modified_by_id`) VALUES ('5fbccf520e185ac0b', 'Icon', 0, '2020-11-24 09:16:02', '2020-11-25 13:53:44', '1', '1'),('5fbe1cdfa364772fe', 'Gallery Image', 0, '2020-11-25 08:59:11', '2020-11-25 13:53:36', '1', '1'),('5fbe2b489bf7238b3', 'Description Image', 0, '2020-11-25 10:00:40', '2020-11-25 13:53:55', '1', '1'),('5fbe2c7339a697f29', 'Office Document', 0, '2020-11-25 10:05:39', '2020-11-25 13:54:04', '1', '1'),('5fbe2eb87a6a53286', 'Text', 0, '2020-11-25 10:15:20', '2020-11-25 13:54:17', '1', '1'),('5fbe2ff5726cd54fe', 'Csv', 0, '2020-11-25 10:20:37', '2020-11-25 13:54:23', '1', '1'),('5fbe621a705f6b661', 'Pdf Document', 0, '2020-11-25 13:54:34', '2020-11-25 13:54:34', '1', NULL),('5fbe62a63ad79a16b', 'Archive', 0, '2020-11-25 13:56:54', '2020-11-25 13:56:54', '1', NULL),('603d0ed4662639434', 'Video', 0, '2021-03-01 00:00:00', '2021-03-01 00:00:00', '1', NULL),('file', 'File', 0, '2021-03-01 00:00:00', '2021-03-01 00:00:00', '1', NULL)");
        $this->execute("INSERT INTO `validation_rule` (`id`, `name`, `deleted`, `created_at`, `modified_at`, `is_active`, `type`, `ratio`, `validate_by`, `pattern`, `min`, `max`, `color_depth`, `color_space`, `min_width`, `min_height`, `extension`, `mime_list`, `created_by_id`, `modified_by_id`, `asset_type_id`) VALUES ('5fbe1cefaffe2f282', 'Mime', 0, '2020-11-25 08:59:27', '2020-11-25 13:46:51', 1, 'Mime', NULL, 'Pattern', 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '1', '1', '5fbe1cdfa364772fe'),('5fbe2a34adb706d7d', 'Mime', 0, '2020-11-25 09:56:04', '2020-11-25 13:47:16', 1, 'Mime', NULL, 'Pattern', 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '1', '1', '5fbccf520e185ac0b'),('5fbe2a3b8e991c4de', 'Ratio', 0, '2020-11-25 09:56:11', '2020-11-25 13:47:09', 1, 'Ratio', 1, 'List', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbccf520e185ac0b'),('5fbe2a46529506918', 'Size', 0, '2020-11-25 09:56:22', '2020-11-25 09:56:22', 0, 'Size', NULL, 'List', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '5fbccf520e185ac0b'),('5fbe2a6527ca22241', 'Size', 0, '2020-11-25 09:56:53', '2020-11-25 13:46:42', 1, 'Size', NULL, 'List', NULL, 0, 5000, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe1cdfa364772fe'),('5fbe2acb105f82186', 'Quality', 0, '2020-11-25 09:58:35', '2020-11-25 13:46:38', 1, 'Quality', NULL, 'List', NULL, 10, 100, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe1cdfa364772fe'),('5fbe2adab3a88d1b0', 'Color Depth', 0, '2020-11-25 09:58:50', '2020-11-25 13:46:33', 1, 'Color Depth', NULL, 'List', NULL, NULL, NULL, '[\"8\"]', NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe1cdfa364772fe'),('5fbe2ae704ed96253', 'Color Space', 0, '2020-11-25 09:59:03', '2020-11-25 13:46:29', 1, 'Color Space', NULL, 'List', NULL, NULL, NULL, NULL, '[\"RGB\",\"SRGB\"]', NULL, NULL, NULL, NULL, '1', '1', '5fbe1cdfa364772fe'),('5fbe2b2a5ba5c3633', 'Scale', 0, '2020-11-25 10:00:10', '2020-11-25 13:46:23', 1, 'Scale', NULL, 'List', NULL, NULL, NULL, NULL, NULL, 150, 150, NULL, NULL, '1', '1', '5fbe1cdfa364772fe'),('5fbe2b58e84f859a7', 'Mime', 0, '2020-11-25 10:00:56', '2020-11-25 13:46:03', 1, 'Mime', NULL, 'Pattern', 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '1', '1', '5fbe2b489bf7238b3'),('5fbe2b66899e859bb', 'Size', 0, '2020-11-25 10:01:10', '2020-11-25 13:45:55', 1, 'Size', NULL, 'List', NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe2b489bf7238b3'),('5fbe2b7c7d73904fe', 'Quality', 0, '2020-11-25 10:01:32', '2020-11-25 13:45:50', 1, 'Quality', NULL, 'List', NULL, 10, 100, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe2b489bf7238b3'),('5fbe2c138b6a4c7a7', 'Color Depth', 0, '2020-11-25 10:04:03', '2020-11-25 13:45:45', 1, 'Color Depth', NULL, 'List', NULL, NULL, NULL, '[\"8\"]', NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe2b489bf7238b3'),('5fbe2c1bf0ce1d175', 'Color Space', 0, '2020-11-25 10:04:11', '2020-11-25 13:44:52', 1, 'Color Space', NULL, 'List', NULL, NULL, NULL, NULL, '[\"RGB\",\"SRGB\"]', NULL, NULL, NULL, NULL, '1', '1', '5fbe2b489bf7238b3'),('5fbe2c5915eb961a5', 'Scale', 0, '2020-11-25 10:05:13', '2020-11-25 13:44:42', 1, 'Scale', NULL, 'List', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '1', '1', '5fbe2b489bf7238b3'),('5fbe2c7f55e59659e', 'Size', 0, '2020-11-25 10:05:51', '2020-11-25 13:42:53', 1, 'Size', NULL, 'List', NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe2c7339a697f29'),('5fbe2d30270d40ab5', 'Extension', 0, '2020-11-25 10:08:48', '2020-11-25 13:42:48', 1, 'Extension', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"doc\",\"dot\",\"docx\",\"dotx\",\"dotm\",\"docb\",\"xls\",\"xlt\",\"xlm\",\"xlxs\",\"xlsm\",\"xltx\",\"xltm\",\"ppt\",\"pot\",\"pps\",\"pptx\",\"pptm\",\"potx\",\"potm\",\"ppam\",\"ppsx\",\"ppsm\",\"sldx\",\"sldm\",\"mdb\",\"accdb\",\"accdr\",\"accdt\",\"vaccdr\",\"rtf\",\"odt\",\"ott\",\"odm\",\"ods\",\"ots\",\"odg\",\"otg\",\"odp\",\"otp\",\"odf\",\"odc\",\"odb\",\"xlsx\"]', NULL, '1', '1', '5fbe2c7339a697f29'),('5fbe2e9b845ffbbcc', 'Mime', 0, '2020-11-25 10:14:51', '2020-11-25 13:42:42', 1, 'Mime', NULL, 'List', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet\",\"application/x-vnd.oasis.opendocument.chart\",\"application/vnd.oasis.opendocument.chart\",\"application/vnd.oasis.opendocument.formula\",\"application/x-vnd.oasis.opendocument.formula\",\"application/vnd.oasis.opendocument.presentation-template\",\"application/x-vnd.oasis.opendocument.presentation-template\",\"application/x-vnd.oasis.opendocument.presentation\",\"application/vnd.oasis.opendocument.presentation\",\"application/x-vnd.oasis.opendocument.graphics-template\",\"application/vnd.oasis.opendocument.graphics-template\",\"application/x-vnd.oasis.opendocument.graphics\",\"application/vnd.oasis.opendocument.graphics\",\"application/x-vnd.oasis.opendocument.spreadsheet-template\",\"application/vnd.oasis.opendocument.spreadsheet-template\",\"application/x-vnd.oasis.opendocument.spreadsheet\",\"application/vnd.oasis.opendocument.spreadsheet\",\"application/vnd.oasis.opendocument.textmaster\",\"application/x-vnd.oasis.opendocument.textmaster\",\"application/doc\",\"application/ms-doc\",\"application/msword\",\"application/vnd.openxmlformats-officedocument.wordprocessingml.document\",\"application/vnd.openxmlformats-officedocument.wordprocessingml.template\",\"application/vnd.ms-word.template.macroEnabled.12\",\"application/vnd.ms-excel\",\"application/excel\",\"application/msexcel\",\"application/x-excel\",\"application/xlt\",\"application/x-msexcel\",\"application/x-ms-excel\",\"application/x-dos_ms_excel\",\"application/xls\",\"application/vnd.ms-excel.sheet.macroEnabled.12\",\"application/vnd.openxmlformats-officedocument.spreadsheetml.template\",\"application/vnd.ms-excel.template.macroEnabled.12\",\"application/vnd.ms-powerpoint\",\"application/mspowerpoint\",\"application/ms-powerpoint\",\"application/mspowerpnt\",\"application/vnd-mspowerpoint\",\"application/powerpoint\",\"application/x-powerpoint\",\"application/x-m\",\"application/x-mspowerpoint\",\"application/x-dos_ms_powerpnt\",\"application/pot\",\"application/x-soffic\",\"application/vnd.openxmlformats-officedocument.presentationml.presentation\",\"application/vnd.ms-powerpoint.presentation.macroEnabled.12\",\"application/vnd.openxmlformats-officedocument.presentationml.template\",\"application/vnd.ms-powerpoint.addin.macroEnabled.12\",\"application/vnd.openxmlformats-officedocument.presentationml.slideshow\",\"application/vnd.ms-powerpoint.slideshow.macroEnabled.12\",\"application/msaccess\",\"application/x-msaccess\",\"application/vnd.msaccess\",\"application/vnd.ms-access\",\"application/mdb\",\"application/x-mdb\",\"zz-application/zz-winassoc-mdb\",\"application/rtf\",\"application/x-rtf\",\"text/rtf\",\"text/richtext\",\"application/x-soffice\",\"application/vnd.oasis.opendocument.text\",\"application/x-vnd.oasis.opendocument.text\",\"application/vnd.oasis.opendocument.text-template\"]', '1', '1', '5fbe2c7339a697f29'),('5fbe2ec40afe0a7c0', 'Extension', 0, '2020-11-25 10:15:32', '2020-11-25 13:43:15', 1, 'Extension', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"txt\"]', NULL, '1', '1', '5fbe2eb87a6a53286'),('5fbe2eee373e84655', 'Mime', 0, '2020-11-25 10:16:14', '2020-11-25 13:43:10', 1, 'Mime', NULL, 'List', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"text/plain\",\"application/txt\"]', '1', '1', '5fbe2eb87a6a53286'),('5fbe2efdcd9704a03', 'Size', 0, '2020-11-25 10:16:29', '2020-11-25 13:43:04', 1, 'Size', NULL, 'List', NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe2eb87a6a53286'),('5fbe300cbfe3381a1', 'Size', 0, '2020-11-25 10:21:00', '2020-11-25 13:52:42', 1, 'Size', NULL, 'List', NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, '1', '1', '5fbe2ff5726cd54fe'),('5fbe30b334462400e', 'Mime', 0, '2020-11-25 10:23:47', '2020-11-25 13:52:48', 1, 'Mime', NULL, 'List', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"text/comma-separated-values\",\"text/csv\",\"application/csv\",\"application/excel\",\"application/vnd.ms-excel\",\"application/vnd.msexcel\",\"text/anytext\",\"text/plain\"]', '1', '1', '5fbe2ff5726cd54fe'),('5fbe61c9e757d3264', 'Extension', 0, '2020-11-25 13:53:13', '2020-11-25 13:53:13', 1, 'Extension', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"csv\"]', NULL, '1', NULL, '5fbe2ff5726cd54fe'),('5fbe6248823fb63b8', 'Size', 0, '2020-11-25 13:55:20', '2020-11-25 13:55:20', 1, 'Size', NULL, 'List', NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '5fbe621a705f6b661'),('5fbe62772d61b5916', 'Mime', 0, '2020-11-25 13:56:07', '2020-11-25 13:56:07', 1, 'Mime', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"application/pdf\",\"application/x-pdf\",\"application/acrobat\",\"applications/vnd.pdf\",\"text/pdf\",\"text/x-pdf\"]', '1', NULL, '5fbe621a705f6b661'),('5fbe6281974e1291a', 'Extension', 0, '2020-11-25 13:56:17', '2020-11-25 13:56:17', 1, 'Extension', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"pdf\"]', NULL, '1', NULL, '5fbe621a705f6b661'),('5fbe628a2e5925c8f', 'PDF Validation', 0, '2020-11-25 13:56:26', '2020-11-25 13:56:26', 1, 'PDF Validation', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '5fbe621a705f6b661'),('5fbe62b3cd32ede76', 'Size', 0, '2020-11-25 13:57:07', '2020-11-25 13:57:07', 1, 'Size', NULL, 'List', NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '5fbe62a63ad79a16b'),('5fbe6360db3c9defb', 'Mime', 0, '2020-11-25 14:00:00', '2020-11-25 14:00:00', 1, 'Mime', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"application/x-archive\",\"application/x-cpio\",\"application/x-shar\",\"application/x-iso9660-image\",\"application/x-sbx\",\"application/x-tar\",\"application/x-bzip2\",\"application/gzip\",\"application/x-gzip\",\"application/x-lzip\",\"application/x-lzma\",\"application/x-lzop\",\"application/x-snappy-framed\",\"application/x-xz\",\"application/x-compress\",\"application/x-7z-compressed\",\"application/x-ace-compressed\",\"application/x-astrotite-afa\",\"application/x-alz-compressed\",\"application/vnd.android.package-archive\",\"application/octet-stream\",\"application/x-freearc\",\"application/x-arj\",\"application/x-b1\",\"application/vnd.ms-cab-compressed\",\"application/x-cfs-compressed\",\"application/x-dar\",\"application/x-dgc-compressed\",\"application/x-apple-diskimage\",\"application/x-gca-compressed\",\"application/java-archive\",\"application/x-lzh\",\"application/x-lzx\",\"application/x-rar\",\"application/x-rar-compressed\",\"application/x-stuffit\",\"application/x-stuffitx\",\"application/x-gtar\",\"application/x-ms-wim\",\"application/x-xar\",\"application/zip\",\"application/x-zoo\",\"application/x-par2\"]', '1', NULL, '5fbe62a63ad79a16b'),('603d0f0435374022f', 'Extension', 0, '2021-03-01 15:57:56', '2021-03-01 15:57:56', 1, 'Extension', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"3g2\",\"3gp\",\"aaf\",\"asf\",\"avchd\",\"avi\",\"drc\",\"flv\",\"m2v\",\"m4p\",\"m4v\",\"mkv\",\"mng\",\"mov\",\"mp2\",\"mp4\",\"mpe\",\"mpeg\",\"mpg\",\"mpv\",\"mxf\",\"nsv\",\"ogg\",\"ogv\",\"qt\",\"rm\",\"rmvb\",\"roq\",\"svi\",\"vob\",\"webm\",\"wmv\",\"yuv\"]', NULL, '1', NULL, '603d0ed4662639434')");
        $this->execute("INSERT INTO `library` (`id`, `name`, `code`, `is_active`) VALUES ('1', 'Default Library', 'default_library', 1)");
    }

    /**
     * @param string $sql
     */
    protected function execute(string $sql)
    {
        try {
            $sth = $this
                ->getContainer()
                ->get('pdo')
                ->prepare($sql);
            $sth->execute();
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
        $attachmentRepository = $this->getContainer()->get('entityManager')->getRepository('Attachment');

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
