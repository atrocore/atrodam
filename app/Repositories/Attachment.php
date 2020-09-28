<?php

declare(strict_types=1);

namespace Dam\Repositories;

use Dam\Core\FileManager;
use Dam\Core\FileStorage\DAMUploadDir;
use Dam\Core\PathInfo;
use Espo\Core\Exceptions\Error;
use Espo\ORM\Entity;

/**
 * Class Attachment
 *
 * @package Dam\Repositories
 */
class Attachment extends \Treo\Repositories\Attachment
{
    /**
     * Init
     */
    protected function init()
    {
        parent::init();
        $this->addDependency("DAMFileManager");
    }

    /**
     * @param Entity $entity
     * @param \Dam\Entities\Asset $asset
     * @return bool
     * @throws Error
     */
    public function moveFile(Entity $entity, \Dam\Entities\Asset $asset): bool
    {
        $file = $this->getFileStorageManager()->getLocalFilePath($entity);
        $fileManager = $this->getFileManager();

        $path = $asset->get('private') ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH;
        $storePath = $asset->get('path');

        $path = $path . $storePath . "/" . $entity->get('name');

        if ($fileManager->move($file, $path)) {
            $entity->set('storageFilePath', $storePath);
            return $this->save($entity) ? true : false;
        }

        return false;
    }

    /**
     * @param Entity $entity
     * @param null $role
     *
     * @return |null
     * @throws \Espo\Core\Exceptions\Error
     */
    public function getCopiedAttachment(Entity $entity, $role = null)
    {
        $attachment = $this->get();

        $attachment->set([
            'sourceId' => $entity->getSourceId(),
            'name' => $entity->get('name'),
            'type' => $entity->get('type'),
            'size' => $entity->get('size'),
            'role' => $entity->get('role'),
            'storageFilePath' => $entity->get('storageFilePath'),
            'relatedType' => $entity->get('relatedType'),
            'relatedId' => $entity->get('relatedId'),
            'hash_md5' => $entity->get('hash_md5')
        ]);

        if ($role) {
            $attachment->set('role', $role);
        }

        $this->save($attachment);

        return $attachment;

    }

    /**
     * @param \Espo\ORM\Entity $entity
     * @return bool
     */
    public function removeThumbs(\Espo\ORM\Entity $entity)
    {
        foreach (DAMUploadDir::thumbsFolderList() as $path) {
            $dirPath = $path . $entity->get('storageFilePath');
            if (!is_dir($dirPath)) {
                continue;
            }

            return $this->getFileManager()->removeInDir($dirPath);
        }
    }

    /**
     * @param Entity $entity
     * @param string $path
     * @return mixed
     * @throws Error
     */
    public function updateStorage(Entity $entity, string $path)
    {
        $entity->set("storageFilePath", $path);
        $entity->set("tmpPath", null);

        return $this->save($entity);
    }

    /**
     * @param Entity $attachment
     * @param string $newFileName
     * @param PathInfo|null $entity
     * @return bool
     * @throws Error
     */
    public function renameFile(Entity $attachment, string $newFileName, PathInfo $entity = null): bool
    {
        $path = $this->buildPath($entity, $attachment);
        $pathInfo = pathinfo($path);
        if ($pathInfo['basename'] == $newFileName) {
            return true;
        }

        $attachment->setName($newFileName);

        if ($this->getFileManager()->renameFile($path, (string)$attachment->get("name"))) {
            return $this->save($attachment) ? true : false;
        }

        return false;
    }

    /**
     * @param Entity $entity
     * @param array  $options
     */
    public function afterRemove(\Espo\ORM\Entity $entity, array $options = [])
    {
        //if uploaded new attachment with previous name
        $res = $this->where([
            "relatedId" => $entity->get("relatedId"),
            "relatedType" => $entity->get("relatedType"),
            "storageFilePath" => $entity->get("storageFilePath"),
            "name" => $entity->get("name"),
            "deleted" => 0
        ])->count();

        if (!$res) {
            parent::afterRemove($entity, $options);
        }
    }

    /**
     * @return FileManager
     */
    protected function getFileManager(): FileManager
    {
        return $this->getInjection("DAMFileManager");
    }

    /**
     * @param PathInfo $entity
     * @param Entity   $attachment
     * @return string
     */
    private function buildPath(PathInfo $entity, Entity $attachment): string
    {
        $path = $entity->getPathInfo()[0];
        return $path . $attachment->get('storageFilePath') . "/" . $attachment->get('name');
    }
}
