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

namespace Dam\Migrations;

use Treo\Core\Migration\Base;

/**
 * Class V1Dot1Dot0
 */
class V1Dot1Dot0 extends Base
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("CREATE TABLE `asset_type` (`id` VARCHAR(24) NOT NULL COLLATE utf8mb4_unicode_ci, `name` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, `created_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `nature` VARCHAR(255) DEFAULT 'File' COLLATE utf8mb4_unicode_ci, `created_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, INDEX `IDX_CREATED_BY_ID` (created_by_id), INDEX `IDX_MODIFIED_BY_ID` (modified_by_id), INDEX `IDX_NAME` (name, deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->execute("CREATE TABLE `validation_rule` (`id` VARCHAR(24) NOT NULL COLLATE utf8mb4_unicode_ci, `name` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, `created_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `is_active` TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci, `type` VARCHAR(255) DEFAULT 'Mime' COLLATE utf8mb4_unicode_ci, `ratio` DOUBLE PRECISION DEFAULT NULL COLLATE utf8mb4_unicode_ci, `validate_by` VARCHAR(255) DEFAULT 'List' COLLATE utf8mb4_unicode_ci, `pattern` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `min` INT DEFAULT '0' COLLATE utf8mb4_unicode_ci, `max` INT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `color_depth` MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `color_space` MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `min_width` INT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `min_height` INT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `extension` MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `mime_list` MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `created_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `asset_type_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, INDEX `IDX_CREATED_BY_ID` (created_by_id), INDEX `IDX_MODIFIED_BY_ID` (modified_by_id), INDEX `IDX_ASSET_TYPE_ID` (asset_type_id), INDEX `IDX_NAME` (name, deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->execute("ALTER TABLE `asset` CHANGE `type` type VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("INSERT INTO `asset_type` (`id`, `name`, `deleted`, `created_at`, `modified_at`, `created_by_id`, `modified_by_id`, `nature`) VALUES ('5fbccf520e185ac0b', 'Icon', 0, '2020-11-24 09:16:02', '2020-11-25 13:53:44', '1', '1', 'Image'),('5fbe1cdfa364772fe', 'Gallery Image', 0, '2020-11-25 08:59:11', '2020-11-25 13:53:36', '1', '1', 'Image'),('5fbe2b489bf7238b3', 'Description Image', 0, '2020-11-25 10:00:40', '2020-11-25 13:53:55', '1', '1', 'Image'),('5fbe2c7339a697f29', 'Office Document', 0, '2020-11-25 10:05:39', '2020-11-25 13:54:04', '1', '1', 'File'),('5fbe2eb87a6a53286', 'Text', 0, '2020-11-25 10:15:20', '2020-11-25 13:54:17', '1', '1', 'File'),('5fbe2ff5726cd54fe', 'Csv', 0, '2020-11-25 10:20:37', '2020-11-25 13:54:23', '1', '1', 'File'),('5fbe621a705f6b661', 'Pdf Document', 0, '2020-11-25 13:54:34', '2020-11-25 13:54:34', '1', NULL, 'File'),('5fbe62a63ad79a16b', 'Archive', 0, '2020-11-25 13:56:54', '2020-11-25 13:56:54', '1', NULL, 'File')");
        $this->execute("INSERT INTO `validation_rule` (`id`, `name`, `deleted`, `created_at`, `modified_at`, `created_by_id`, `modified_by_id`, `asset_type_id`, `type`, `ratio`, `pattern`, `min`, `max`, `color_depth`, `color_space`, `min_width`, `min_height`, `extension`, `mime_list`, `is_active`, `validate_by`) VALUES ('5fbe1cefaffe2f282', 'Mime', 0, '2020-11-25 08:59:27', '2020-11-25 13:46:51', '1', '1', '5fbe1cdfa364772fe', 'Mime', NULL, 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', 1, 'Pattern'),('5fbe2a34adb706d7d', 'Mime', 0, '2020-11-25 09:56:04', '2020-11-25 13:47:16', '1', '1', '5fbccf520e185ac0b', 'Mime', NULL, 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', 1, 'Pattern'),('5fbe2a3b8e991c4de', 'Ratio', 0, '2020-11-25 09:56:11', '2020-11-25 13:47:09', '1', '1', '5fbccf520e185ac0b', 'Ratio', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2a46529506918', 'Size', 0, '2020-11-25 09:56:22', '2020-11-25 09:56:22', '1', NULL, '5fbccf520e185ac0b', 'Size', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'List'),('5fbe2a6527ca22241', 'Size', 0, '2020-11-25 09:56:53', '2020-11-25 13:46:42', '1', '1', '5fbe1cdfa364772fe', 'Size', NULL, NULL, 0, 5000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2acb105f82186', 'Quality', 0, '2020-11-25 09:58:35', '2020-11-25 13:46:38', '1', '1', '5fbe1cdfa364772fe', 'Quality', NULL, NULL, 10, 100, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2adab3a88d1b0', 'Color Depth', 0, '2020-11-25 09:58:50', '2020-11-25 13:46:33', '1', '1', '5fbe1cdfa364772fe', 'Color Depth', NULL, NULL, NULL, NULL, '[\"8\"]', NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2ae704ed96253', 'Color Space', 0, '2020-11-25 09:59:03', '2020-11-25 13:46:29', '1', '1', '5fbe1cdfa364772fe', 'Color Space', NULL, NULL, NULL, NULL, NULL, '[\"RGB\",\"SRGB\"]', NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2b2a5ba5c3633', 'Scale', 0, '2020-11-25 10:00:10', '2020-11-25 13:46:23', '1', '1', '5fbe1cdfa364772fe', 'Scale', NULL, NULL, NULL, NULL, NULL, NULL, 150, 150, NULL, NULL, 1, 'List'),('5fbe2b58e84f859a7', 'Mime', 0, '2020-11-25 10:00:56', '2020-11-25 13:46:03', '1', '1', '5fbe2b489bf7238b3', 'Mime', NULL, 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', 1, 'Pattern'),('5fbe2b66899e859bb', 'Size', 0, '2020-11-25 10:01:10', '2020-11-25 13:45:55', '1', '1', '5fbe2b489bf7238b3', 'Size', NULL, NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2b7c7d73904fe', 'Quality', 0, '2020-11-25 10:01:32', '2020-11-25 13:45:50', '1', '1', '5fbe2b489bf7238b3', 'Quality', NULL, NULL, 10, 100, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2c138b6a4c7a7', 'Color Depth', 0, '2020-11-25 10:04:03', '2020-11-25 13:45:45', '1', '1', '5fbe2b489bf7238b3', 'Color Depth', NULL, NULL, NULL, NULL, '[\"8\"]', NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2c1bf0ce1d175', 'Color Space', 0, '2020-11-25 10:04:11', '2020-11-25 13:44:52', '1', '1', '5fbe2b489bf7238b3', 'Color Space', NULL, NULL, NULL, NULL, NULL, '[\"RGB\",\"SRGB\"]', NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2c5915eb961a5', 'Scale', 0, '2020-11-25 10:05:13', '2020-11-25 13:44:42', '1', '1', '5fbe2b489bf7238b3', 'Scale', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, 'List'),('5fbe2c7f55e59659e', 'Size', 0, '2020-11-25 10:05:51', '2020-11-25 13:42:53', '1', '1', '5fbe2c7339a697f29', 'Size', NULL, NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe2d30270d40ab5', 'Extension', 0, '2020-11-25 10:08:48', '2020-11-25 13:42:48', '1', '1', '5fbe2c7339a697f29', 'Extension', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"doc\",\"dot\",\"docx\",\"dotx\",\"dotm\",\"docb\",\"xls\",\"xlt\",\"xlm\",\"xlxs\",\"xlsm\",\"xltx\",\"xltm\",\"ppt\",\"pot\",\"pps\",\"pptx\",\"pptm\",\"potx\",\"potm\",\"ppam\",\"ppsx\",\"ppsm\",\"sldx\",\"sldm\",\"mdb\",\"accdb\",\"accdr\",\"accdt\",\"vaccdr\",\"rtf\",\"odt\",\"ott\",\"odm\",\"ods\",\"ots\",\"odg\",\"otg\",\"odp\",\"otp\",\"odf\",\"odc\",\"odb\",\"xlsx\"]', NULL, 1, 'List'),('5fbe2e9b845ffbbcc', 'Mime', 0, '2020-11-25 10:14:51', '2020-11-25 13:42:42', '1', '1', '5fbe2c7339a697f29', 'Mime', NULL, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"application\\/vnd.openxmlformats-officedocument.spreadsheetml.sheet\",\"application\\/x-vnd.oasis.opendocument.chart\",\"application\\/vnd.oasis.opendocument.chart\",\"application\\/vnd.oasis.opendocument.formula\",\"application\\/x-vnd.oasis.opendocument.formula\",\"application\\/vnd.oasis.opendocument.presentation-template\",\"application\\/x-vnd.oasis.opendocument.presentation-template\",\"application\\/x-vnd.oasis.opendocument.presentation\",\"application\\/vnd.oasis.opendocument.presentation\",\"application\\/x-vnd.oasis.opendocument.graphics-template\",\"application\\/vnd.oasis.opendocument.graphics-template\",\"application\\/x-vnd.oasis.opendocument.graphics\",\"application\\/vnd.oasis.opendocument.graphics\",\"application\\/x-vnd.oasis.opendocument.spreadsheet-template\",\"application\\/vnd.oasis.opendocument.spreadsheet-template\",\"application\\/x-vnd.oasis.opendocument.spreadsheet\",\"application\\/vnd.oasis.opendocument.spreadsheet\",\"application\\/vnd.oasis.opendocument.textmaster\",\"application\\/x-vnd.oasis.opendocument.textmaster\",\"application\\/doc\",\"application\\/ms-doc\",\"application\\/msword\",\"application\\/vnd.openxmlformats-officedocument.wordprocessingml.document\",\"application\\/vnd.openxmlformats-officedocument.wordprocessingml.template\",\"application\\/vnd.ms-word.template.macroEnabled.12\",\"application\\/vnd.ms-excel\",\"application\\/excel\",\"application\\/msexcel\",\"application\\/x-excel\",\"application\\/xlt\",\"application\\/x-msexcel\",\"application\\/x-ms-excel\",\"application\\/x-dos_ms_excel\",\"application\\/xls\",\"application\\/vnd.ms-excel.sheet.macroEnabled.12\",\"application\\/vnd.openxmlformats-officedocument.spreadsheetml.template\",\"application\\/vnd.ms-excel.template.macroEnabled.12\",\"application\\/vnd.ms-powerpoint\",\"application\\/mspowerpoint\",\"application\\/ms-powerpoint\",\"application\\/mspowerpnt\",\"application\\/vnd-mspowerpoint\",\"application\\/powerpoint\",\"application\\/x-powerpoint\",\"application\\/x-m\",\"application\\/x-mspowerpoint\",\"application\\/x-dos_ms_powerpnt\",\"application\\/pot\",\"application\\/x-soffic\",\"application\\/vnd.openxmlformats-officedocument.presentationml.presentation\",\"application\\/vnd.ms-powerpoint.presentation.macroEnabled.12\",\"application\\/vnd.openxmlformats-officedocument.presentationml.template\",\"application\\/vnd.ms-powerpoint.addin.macroEnabled.12\",\"application\\/vnd.openxmlformats-officedocument.presentationml.slideshow\",\"application\\/vnd.ms-powerpoint.slideshow.macroEnabled.12\",\"application\\/msaccess\",\"application\\/x-msaccess\",\"application\\/vnd.msaccess\",\"application\\/vnd.ms-access\",\"application\\/mdb\",\"application\\/x-mdb\",\"zz-application\\/zz-winassoc-mdb\",\"application\\/rtf\",\"application\\/x-rtf\",\"text\\/rtf\",\"text\\/richtext\",\"application\\/x-soffice\",\"application\\/vnd.oasis.opendocument.text\",\"application\\/x-vnd.oasis.opendocument.text\",\"application\\/vnd.oasis.opendocument.text-template\"]', 1, 'List'),('5fbe2ec40afe0a7c0', 'Extension', 0, '2020-11-25 10:15:32', '2020-11-25 13:43:15', '1', '1', '5fbe2eb87a6a53286', 'Extension', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"txt\"]', NULL, 1, 'List'),('5fbe2eee373e84655', 'Mime', 0, '2020-11-25 10:16:14', '2020-11-25 13:43:10', '1', '1', '5fbe2eb87a6a53286', 'Mime', NULL, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"text\\/plain\",\"application\\/txt\"]', 1, 'List'),('5fbe2efdcd9704a03', 'Size', 0, '2020-11-25 10:16:29', '2020-11-25 13:43:04', '1', '1', '5fbe2eb87a6a53286', 'Size', NULL, NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe300cbfe3381a1', 'Size', 0, '2020-11-25 10:21:00', '2020-11-25 13:52:42', '1', '1', '5fbe2ff5726cd54fe', 'Size', NULL, NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe30b334462400e', 'Mime', 0, '2020-11-25 10:23:47', '2020-11-25 13:52:48', '1', '1', '5fbe2ff5726cd54fe', 'Mime', NULL, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"text\\/comma-separated-values\",\"text\\/csv\",\"application\\/csv\",\"application\\/excel\",\"application\\/vnd.ms-excel\",\"application\\/vnd.msexcel\",\"text\\/anytext\",\"text\\/plain\"]', 1, 'List'),('5fbe61c9e757d3264', 'Extension', 0, '2020-11-25 13:53:13', '2020-11-25 13:53:13', '1', NULL, '5fbe2ff5726cd54fe', 'Extension', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"csv\"]', NULL, 1, 'List'),('5fbe6248823fb63b8', 'Size', 0, '2020-11-25 13:55:20', '2020-11-25 13:55:20', '1', NULL, '5fbe621a705f6b661', 'Size', NULL, NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe62772d61b5916', 'Mime', 0, '2020-11-25 13:56:07', '2020-11-25 13:56:07', '1', NULL, '5fbe621a705f6b661', 'Mime', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"application\\/pdf\",\"application\\/x-pdf\",\"application\\/acrobat\",\"applications\\/vnd.pdf\",\"text\\/pdf\",\"text\\/x-pdf\"]', 1, 'List'),('5fbe6281974e1291a', 'Extension', 0, '2020-11-25 13:56:17', '2020-11-25 13:56:17', '1', NULL, '5fbe621a705f6b661', 'Extension', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"pdf\"]', NULL, 1, 'List'),('5fbe628a2e5925c8f', 'PDF Validation', 0, '2020-11-25 13:56:26', '2020-11-25 13:56:26', '1', NULL, '5fbe621a705f6b661', 'PDF Validation', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe62b3cd32ede76', 'Size', 0, '2020-11-25 13:57:07', '2020-11-25 13:57:07', '1', NULL, '5fbe62a63ad79a16b', 'Size', NULL, NULL, 0, 100000000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'List'),('5fbe6360db3c9defb', 'Mime', 0, '2020-11-25 14:00:00', '2020-11-25 14:00:00', '1', NULL, '5fbe62a63ad79a16b', 'Mime', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"application\\/x-archive\",\"application\\/x-cpio\",\"application\\/x-shar\",\"application\\/x-iso9660-image\",\"application\\/x-sbx\",\"application\\/x-tar\",\"application\\/x-bzip2\",\"application\\/gzip\",\"application\\/x-gzip\",\"application\\/x-lzip\",\"application\\/x-lzma\",\"application\\/x-lzop\",\"application\\/x-snappy-framed\",\"application\\/x-xz\",\"application\\/x-compress\",\"application\\/x-7z-compressed\",\"application\\/x-ace-compressed\",\"application\\/x-astrotite-afa\",\"application\\/x-alz-compressed\",\"application\\/vnd.android.package-archive\",\"application\\/octet-stream\",\"application\\/x-freearc\",\"application\\/x-arj\",\"application\\/x-b1\",\"application\\/vnd.ms-cab-compressed\",\"application\\/x-cfs-compressed\",\"application\\/x-dar\",\"application\\/x-dgc-compressed\",\"application\\/x-apple-diskimage\",\"application\\/x-gca-compressed\",\"application\\/java-archive\",\"application\\/x-lzh\",\"application\\/x-lzx\",\"application\\/x-rar\",\"application\\/x-rar-compressed\",\"application\\/x-stuffit\",\"application\\/x-stuffitx\",\"application\\/x-gtar\",\"application\\/x-ms-wim\",\"application\\/x-xar\",\"application\\/zip\",\"application\\/x-zoo\",\"application\\/x-par2\"]', 1, 'List')");
        $this->execute("CREATE TABLE `library` (`id` VARCHAR(24) NOT NULL COLLATE utf8mb4_unicode_ci, `name` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, `description` MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `created_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `code` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `is_active` TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci, `created_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `assigned_user_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `owner_user_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, INDEX `IDX_CREATED_BY_ID` (created_by_id), INDEX `IDX_MODIFIED_BY_ID` (modified_by_id), INDEX `IDX_ASSIGNED_USER_ID` (assigned_user_id), INDEX `IDX_OWNER_USER_ID` (owner_user_id), INDEX `IDX_NAME` (name, deleted), INDEX `IDX_ASSIGNED_USER` (assigned_user_id, deleted), INDEX `IDX_OWNER_USER` (owner_user_id, deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->execute("CREATE TABLE `library_asset_category` (`id` INT AUTO_INCREMENT NOT NULL UNIQUE COLLATE utf8mb4_unicode_ci, `library_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `asset_category_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, INDEX `IDX_370C9561137ABCF` (library_id), INDEX `IDX_370C956993EC4EB` (asset_category_id), UNIQUE INDEX `UNIQ_370C9561137ABCF993EC4EB` (library_id, asset_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;");
        $this->execute("DROP TABLE collection");
        $this->execute("DROP TABLE collection_asset_category");
        $this->execute("DROP INDEX IDX_COLLECTION_ID ON `asset`");
        $this->execute("ALTER TABLE `asset` ADD library_id VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("CREATE INDEX IDX_LIBRARY_ID ON `asset` (library_id)");
        $this->execute("INSERT INTO `library` (`id`, `name`, `code`, `is_active`) VALUES ('1', 'Default Library', 'default_library', 1)");
        $this->execute("UPDATE asset SET library_id='1' WHERE library_id IS NULL");
        $this->execute("ALTER TABLE `asset` DROP name_of_file, DROP code, DROP collection_id, CHANGE `private` private TINYINT(1) DEFAULT '1' NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE `library_id` library_id VARCHAR(24) DEFAULT '1' COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `category_asset` ADD sorting INT DEFAULT '100000' COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `product_asset` ADD sorting INT DEFAULT '100000' COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `product_asset` ADD channel VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `category_asset` ADD channel VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci");
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute("DROP TABLE asset_type");
        $this->execute("DROP TABLE validation_rule");
        $this->execute("ALTER TABLE `asset` CHANGE `type` type VARCHAR(255) DEFAULT 'Gallery Image' COLLATE utf8mb4_unicode_ci");
        $this->execute("CREATE TABLE `collection` (`id` VARCHAR(24) NOT NULL COLLATE utf8mb4_unicode_ci, `name` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, `description` MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `created_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `code` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `is_active` TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci, `created_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `assigned_user_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `owner_user_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, INDEX `IDX_CREATED_BY_ID` (created_by_id), INDEX `IDX_MODIFIED_BY_ID` (modified_by_id), INDEX `IDX_ASSIGNED_USER_ID` (assigned_user_id), INDEX `IDX_OWNER_USER_ID` (owner_user_id), INDEX `IDX_NAME` (name, deleted), INDEX `IDX_ASSIGNED_USER` (assigned_user_id, deleted), INDEX `IDX_OWNER_USER` (owner_user_id, deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->execute("CREATE TABLE `collection_asset_category` (`id` INT AUTO_INCREMENT NOT NULL UNIQUE COLLATE utf8mb4_unicode_ci, `asset_category_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `collection_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, INDEX `IDX_8F27FD8A993EC4EB` (asset_category_id), INDEX `IDX_8F27FD8A514956FD` (collection_id), UNIQUE INDEX `UNIQ_8F27FD8A993EC4EB514956FD` (asset_category_id, collection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->execute("DROP TABLE library");
        $this->execute("DROP TABLE library_asset_category");
        $this->execute("DROP INDEX IDX_LIBRARY_ID ON `asset`");
        $this->execute("ALTER TABLE `asset` DROP library_id, CHANGE `private` private TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci, ADD name_of_file VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD code VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD collection_id VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("CREATE INDEX IDX_COLLECTION_ID ON `asset` (collection_id)");
        $this->execute("ALTER TABLE `category_asset` DROP sorting");
        $this->execute("ALTER TABLE `product_asset` DROP sorting");
    }

    /**
     * @param string $sql
     */
    protected function execute(string $sql)
    {
        try {
            $this->getPDO()->exec($sql);
        } catch (\Throwable $e) {
            // ignore all
        }
    }
}
