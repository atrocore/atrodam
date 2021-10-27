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

namespace Dam\Repositories;

use Dam\Core\ConfigManager;
use Espo\Core\Exceptions\Error;
use Dam\Entities\Asset;
use Espo\ORM\Entity;
use Throwable;

/**
 * Class Attachment
 *
 * @package Dam\Repositories
 */
class Attachment extends \Espo\Repositories\Attachment
{
    /**
     * @inheritDoc
     */
    public function isPrivate(Entity $entity): bool
    {
        if (!empty($asset = $entity->getAsset())) {
            return $asset->get('private');
        }

        return parent::isPrivate($entity);
    }

    /**
     * @param Entity $entity
     *
     * @return Asset|null
     */
    public function getAsset(Entity $entity): ?Asset
    {
        return $this->getEntityManager()->getRepository('Asset')->where(['fileId' => $entity->get('id')])->findOne();
    }

    /**
     * Create asset if it needs
     *
     * @param Entity      $attachment
     * @param bool        $skipValidation
     * @param string|null $type
     *
     * @throws Error
     * @throws Throwable
     */
    public function createAsset(Entity $attachment, bool $skipValidation = false, string $type = null)
    {
        if (!empty($this->where(['fileId' => $attachment->get('id')])->findOne())) {
            return;
        }

        if ($type === null) {
            $type = $this->getMetadata()->get(['entityDefs', $attachment->get('relatedType'), 'fields', $attachment->get('field'), 'assetType']);
        }

        $asset = $this->getEntityManager()->getEntity('Asset');
        $asset->set('name', $attachment->get('name'));
        $asset->set('private', $this->getConfig()->get('isUploadPrivate', true));
        $asset->set('fileId', $attachment->get('id'));
        $asset->set('type', $type);

        try {
            if (!$skipValidation) {
                $config = $this->getInjection("ConfigManager")->getByType([ConfigManager::getType($asset->get('type'))]);
                if (!empty($config['validations']) && is_array($config['validations'])) {
                    foreach ($config['validations'] as $type => $value) {
                        $this->getInjection('Validator')->validate($type, $attachment, ($value['private'] ?? $value));
                    }
                }
            }
            $this->getEntityManager()->saveEntity($asset);
        } catch (Throwable $exception) {
            $this->getEntityManager()->removeEntity($attachment);

            throw $exception;
        }
    }

    /**
     * Init
     */
    protected function init()
    {
        parent::init();

        $this->addDependency("DAMFileManager");
        $this->addDependency("Validator");
        $this->addDependency("ConfigManager");
    }

    /**
     * @param Entity $entity
     * @param string $path
     *
     * @return mixed
     * @throws Error
     */
    public function updateStorage(Entity $entity, string $path)
    {
        $entity->set("storageFilePath", $path);

        return $this->save($entity);
    }

    /**
     * @param Entity $attachment
     * @param string $newFileName
     *
     * @return bool
     * @throws Error
     */
    public function renameFile(Entity $attachment, string $newFile): bool
    {
        $path = $this->getFilePath($attachment);

        setlocale(LC_ALL, 'en_US.UTF-8');
        $pathInfo = pathinfo($path);
        $newFileInfo = pathinfo($newFile);
        if ($pathInfo['basename'] == $newFileInfo['basename']) {
            return true;
        }

        $attachment->setName($newFileInfo['filename']);

        if ($this->getFileManager()->move($path, $this->getFilePath($attachment))) {
            return $this->save($attachment) ? true : false;
        }

        return false;
    }

    /**
     * @param Entity $entity
     * @param array  $options
     */
    protected function afterRemove(Entity $entity, array $options = [])
    {
        // if uploaded new attachment with previous name
        $res = $this
            ->where(
                [
                    "relatedId"       => $entity->get("relatedId"),
                    "relatedType"     => $entity->get("relatedType"),
                    "storageFilePath" => $entity->get("storageFilePath"),
                    "name"            => $entity->get("name"),
                    "deleted"         => 0
                ]
            )
            ->count();

        if (!$res) {
            parent::afterRemove($entity, $options);
        }
    }
}
