<?php

declare(strict_types=1);

namespace Dam\Services;

use Dam\Core\ConfigManager;
use Dam\Core\FileManager;
use Dam\Core\FilePathBuilder;
use Dam\Core\FileStorage\DAMUploadDir;
use Dam\Core\Utils\Util;
use Dam\Core\Validation\Validator;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\ORM\Entity;
use Imagick;
use Treo\Core\FileStorage\Manager;

/**
 * Class Attachment
 *
 * @package Dam\Services
 */
class Attachment extends \Treo\Services\Attachment
{
    /**
     * Attachment constructor.
     */
    public function __construct()
    {
        $this->addDependency("Validator");
        $this->addDependency("DAMFileManager");
        $this->addDependency("fileStorageManager");
        $this->addDependency("ConfigManager");
        $this->addDependency("filePathBuilder");

        parent::__construct();
    }

    /**
     * @param      $attachment
     * @param null $path
     * @return array
     * @throws \ImagickException
     * @throws \ReflectionException
     */
    public function getImageInfo($attachment, $path = null): array
    {
        if (stripos($attachment->get("type"), "image/") === false) {
            return [];
        }

        $path = $path ?? $this->getPath($attachment);

        $image = new Imagick($path);

        if ($imageInfo = getimagesize($path)) {
            $result = [
                "width"       => $image->getImageWidth(),
                "height"      => $image->getImageHeight(),
                "color_space" => Util::getColorSpace($image),
                "color_depth" => $image->getImageDepth(),
                'orientation' => $this->getPosition($image->getImageWidth(), $image->getImageHeight()),
                'mime'        => $image->getImageMimeType(),
            ];
        }

        return $result ?? [];
    }

    /**
     * @param      $attachment
     * @param null $path
     * @return array
     */
    public function getFileInfo($attachment, $path = null): array
    {
        $path = $path ?? $this->getPath($attachment);

        if ($pathInfo = pathinfo($path)) {
            $result['extension'] = $pathInfo['extension'];
            $result['base_name'] = $pathInfo['basename'];
        }

        $result['size'] = filesize($path);

        return $result;
    }

    /**
     * @param $attachment
     *
     * @return mixed
     * @throws \Espo\Core\Exceptions\BadRequest
     * @throws \Espo\Core\Exceptions\Error
     * @throws \Espo\Core\Exceptions\Forbidden
     */
    public function createEntity($attachment)
    {
        $entity = parent::createEntity($attachment);

        try {
            $this->validateAttachment($attachment, $entity);
        } catch (\Exception $exception) {
            $this->getFileManager()->removeFile([$entity->get('tmpPath')]);
            /**@var $repo \Dam\Repositories\Attachment* */
            $repo = $this->getRepository();
            $repo->deleteFromDb($entity->id);

            throw $exception;
        }

        if (!empty($attachment->file)) {
            $entity->clear('contents');
        }

        return $entity;
    }

    /**
     * @param string $attachmentId
     * @return bool
     */
    public function toDelete(string $attachmentId)
    {
        $entity = $this->getEntity($attachmentId);

        if (!$entity) {
            return false;
        }

        $entity->set("deleted", true);

        return $this->getRepository()->save($entity);
    }

    public function deleteAttachment($attachmentId, $entityType = null)
    {
        $attachmentRepository = $this->getRepository();

        $where = [
            'id' => $attachmentId
        ];

        if ($entityType) {
            $where["relatedType"] = $entityType;
        }

        $attachment = $attachmentRepository->where($where)->findOne();

        if (!$attachment) {
            return false;
        }

        return $attachmentRepository->remove($attachment);
    }

    /**
     * @param \Dam\Entities\Asset $asset
     * @return mixed
     * @throws Error
     * @throws Forbidden
     */
    public function moveToMaster(\Dam\Entities\Asset $asset)
    {
        $attachment = $this->getEntity($asset->get("fileId"));

        if ($asset->get("nameOfFile")) {
            $attachment->setName($asset->get("nameOfFile"));
        }

        $sourcePath = $attachment->get("tmpPath");
        $destPath   = ($asset->get("private") ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "master/" . $asset->get('path') . "/" . $attachment->get('name');

        if ($this->getFileManager()->move($sourcePath, $destPath, false)) {
            return $this->getEntityManager()->getRepository("Attachment")->updateStorage($attachment,
                $asset->get('path'));
        }

        return false;
    }

    /**
     * @param $entity
     * @param $attachment
     * @return bool
     */
    public function moveToRendition($entity, $attachment)
    {
        $sourcePath = $attachment->get("tmpPath");

        if (!$entity->get("path")) {
            $type = $entity->get("private") ? FilePathBuilder::PRIVATE : FilePathBuilder::PUBLIC;
            $entity->set("path", $this->getFilePathBuilder()->createPath($type, $entity->get('type')));
        }

        $destPath = ($entity->get("private") ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . $entity->get("type") . "/" . $entity->get('path') . "/" . $attachment->get('name');

        if ($this->getFileManager()->move($sourcePath, $destPath, false)) {
            return $this
                ->getEntityManager()
                ->getRepository("Attachment")
                ->updateStorage($attachment, $entity->get('path'));
        }

        return false;
    }

    /**
     * @param \Dam\Entities\Asset $asset
     * @return bool
     */
    public function copyDuplicate(\Dam\Entities\Asset $asset)
    {
        $attachment = $this->getEntity($asset->get("fileId"));

        $sourcePath = $this->getFileStorageManager()->getLocalFilePath($attachment);
        $destPath   = ($asset->get("private") ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "master/" . $asset->get('path');

        if ($this->getFileManager()->copy($sourcePath, $destPath, false, null, true)) {
            $attachment->set("storageFilePath", $asset->get('path'));
            $attachment->set('sourceId', null);

            return $this->getEntityManager()->getRepository("Attachment")->save($attachment);
        }

        return false;
    }

    /**
     * @param Entity $entity
     * @return mixed
     * @throws Error
     * @throws Forbidden
     */
    public function changeAccess(Entity $entity)
    {
        $source = ($entity->getFetched("private") ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "{$entity->getMainFolder()}/" . $entity->getFetched("path");
        $dest   = ($entity->get("private") ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "{$entity->getMainFolder()}/" . $entity->get("path");

        $attachment = $this->getEntity($entity->get("fileId"));

        if ($this->getFileManager()->moveFolder($source, $dest)) {
            return $this->getEntityManager()
                        ->getRepository("Attachment")
                        ->updateStorage($attachment, $entity->get('path'));
        }
    }

    /**
     * @param \Dam\Entities\Attachment $attachment
     * @param string                   $newName
     * @param Entity                   $entity
     * @return mixed
     */
    public function changeName(\Dam\Entities\Attachment $attachment, string $newName, Entity $entity = null)
    {
        return $this->getRepository()->renameFile($attachment, $newName, $entity);
    }

    /**
     * @param \Dam\Entities\Attachment $attachment
     * @return array|mixed
     * @throws Error
     * @throws \ImagickException
     */
    public function getFileMetaData(\Dam\Entities\Attachment $attachment)
    {
        $mime = $attachment->get('type');
        $meta = [];

        switch (true) {
            case (stripos($mime, "image") !== false):
                $meta = $this->getImageMeta($attachment);
                break;
        }

        return $meta;
    }

    /**
     * @param \Dam\Entities\Attachment $attachment
     * @return array
     * @throws Error
     * @throws \ImagickException
     */
    public function getImageMeta(\Dam\Entities\Attachment $attachment)
    {
        $path = $this->getFileStorageManager()->getLocalFilePath($attachment);

        $imagick = new \Imagick();
        $imagick->readImage($path);

        return $imagick->getImageProperties();
    }

    /**
     * @param $attachment
     * @throws BadRequest
     * @throws Error
     * @throws Forbidden
     */
    protected function validateAttachment($attachment, $entity)
    {
        if (!empty($attachment->file)) {
            $arr      = explode(',', $attachment->file);
            $contents = '';
            if (count($arr) > 1) {
                $contents = $arr[1];
            }

            $contents             = base64_decode($contents);
            $attachment->contents = $contents;

            $relatedEntityType = null;
            $field             = null;
            $role              = 'Attachment';
            if (isset($attachment->parentType)) {
                $relatedEntityType = $attachment->parentType;
            } elseif (isset($attachment->relatedType)) {
                $relatedEntityType = $attachment->relatedType;
            }
            if (isset($attachment->field)) {
                $field = $attachment->field;
            }

            if (isset($attachment->role)) {
                $role = $attachment->role;
            }
            if (!$relatedEntityType || !$field) {
                throw new BadRequest("Params 'field' and 'parentType' not passed along with 'file'.");
            }

            $fieldType = $this->getMetadata()->get(['entityDefs', $relatedEntityType, 'fields', $field, 'type']);
            if (!$fieldType) {
                throw new Error("Field '{$field}' does not exist.");
            }

            if ($this->hasAcl($relatedEntityType)) {
                throw new Forbidden("No access to " . $relatedEntityType . ".");
            }

            if (in_array($field, $this->getAcl()->getScopeForbiddenFieldList($relatedEntityType, 'edit'))) {
                throw new Forbidden("No access to field '" . $field . "'.");
            }

            if ($role === 'Attachment') {
                if (!in_array($fieldType, $this->attachmentFieldTypeList)) {
                    throw new Error("Field type '{$fieldType}' is not allowed for attachment.");
                }

                if (isset($attachment->modelAttributes)) {

                    $model   = $attachment->modelAttributes;
                    $private = $model->private ? "private" : "public";

                    $config = $this->getConfigManager()->getByType([ConfigManager::getType($model->type)]);

                    foreach ($config['validations'] as $type => $value) {
                        $this->getValidator()->validate($type, $entity, ($value[$private] ?? $value));
                    }
                }
            }
        }
    }

    /**
     * @return Validator
     */
    protected function getValidator(): Validator
    {
        return $this->getInjection("Validator");
    }

    /**
     * @param \Treo\Entities\Attachment $attachment
     *
     * @return mixed
     */
    private function getPath(\Treo\Entities\Attachment $attachment)
    {
        if ($attachment->get('sourceId')) {
            $attachment = $this->getRepository()->where(['id' => $attachment->get('sourceId')])->findOne();
        }

        if ($attachment->get("tmpPath")) {
            return $attachment->get("tmpPath");
        } else {
            return $this->getRepository()->getFilePath($attachment);
        }
    }

    /**
     * @param $width
     * @param $height
     *
     * @return string
     */
    private function getPosition($width, $height): string
    {
        $result = "Square";

        if ($width > $height) {
            $result = "Landscape";
        } elseif ($width < $height) {
            $result = "Portrait";
        }

        return $result;
    }

    /**
     * @param $relatedEntityType
     *
     * @return bool
     */
    private function hasAcl($relatedEntityType): bool
    {
        return !$this->getAcl()->checkScope($relatedEntityType,
                'create') && !$this->getAcl()->checkScope($relatedEntityType, 'edit');
    }

    /**
     * @return FileManager
     */
    protected function getFileManager(): FileManager
    {
        return $this->getInjection("DAMFileManager");
    }

    /**
     * @return Manager
     */
    protected function getFileStorageManager(): Manager
    {
        return $this->getInjection("fileStorageManager");
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager(): ConfigManager
    {
        return $this->getInjection("ConfigManager");
    }

    /**
     * @return FilePathBuilder
     */
    protected function getFilePathBuilder(): FilePathBuilder
    {
        return $this->getInjection("filePathBuilder");
    }

}
