<?php

declare(strict_types=1);

namespace Dam\EntryPoints;

use Dam\Core\Download\Custom;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Imagick;

/**
 * Class Download
 * @package Dam\EntryPoints
 */
class Download extends \Espo\EntryPoints\Download
{
    /**@var Imagick * */
    protected $image = null;

    /**
     * Run download method
     */
    public function run()
    {
        switch ($_GET['type']) {
            case "custom" :
                $this->custom();
                break;
            default:
                $this->runDownload();
        }
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     * @throws \Espo\Core\Exceptions\Error
     */
    public function runDownload()
    {
        if (empty($_GET['id'])) {
            throw new BadRequest();
        }
        $id = $_GET['id'];

        $attachment = $this->getEntityManager()->getEntity('Attachment', $id);

        if (!$attachment) {
            throw new NotFound();
        }

        if (!$this->getAcl()->checkEntity($attachment)) {
            throw new Forbidden();
        }

        $sourceId = $attachment->getSourceId();

        if ($this->getEntityManager()->getRepository('Attachment')->hasDownloadUrl($attachment)) {
            $downloadUrl = $this->getEntityManager()->getRepository('Attachment')->getDownloadUrl($attachment);
            header('Location: ' . $downloadUrl);
            exit;
        }

        $fileName = $this->getEntityManager()->getRepository('Attachment')->getFilePath($attachment);

        if (!file_exists($fileName)) {
            throw new NotFound();
        }

        $outputFileName = $attachment->get('name');
        $outputFileName = str_replace("\"", "\\\"", $outputFileName);

        $type = $attachment->get('type');

        $disposition = 'attachment';
        if (in_array($type, $this->fileTypesToShowInline) && $this->showInline()) {
            $disposition = 'inline';
        }

        header('Content-Description: File Transfer');
        if ($type) {
            header('Content-Type: ' . $type);
        }
        header("Content-Disposition: " . $disposition . ";filename=\"" . $outputFileName . "\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fileName));

        readfile($fileName);
        exit;
    }

    /**
     * @throws \ImagickException
     */
    protected function custom()
    {
        $attachment = $this->getAttachment();

        $filePath = $this->getEntityManager()->getRepository('Attachment')->getFilePath($attachment);

        if (!file_exists($filePath)) {
            throw new NotFound();
        }

        $file = (new Custom($filePath))
            ->setAttachment($attachment)
            ->setParams($_GET)
            ->convert();

        $type = $file->getType();

        header('Content-Description: File Transfer');
        if ($type) {
            header('Content-Type: ' . $type);
        }
        header("Content-Disposition: attachment;filename=\"" . $file->getName() . "\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $file->getImageSize());

        echo $file->getImage();
        exit;
    }

    /**
     * @return bool
     */
    protected function showInline(): bool
    {
        if (!isset($_GET['showInline'])) {
            return true;
        }

        return $_GET['showInline'] == 'true' ? true : false;
    }

    /**
     * @return mixed
     */
    protected function getAttachment()
    {
        if (empty($_GET['id'])) {
            throw new BadRequest();
        }
        $id = $_GET['id'];

        $attachment = $this->getEntityManager()->getEntity('Attachment', $id);

        if (!$attachment) {
            throw new NotFound();
        }

        if (!$this->getAcl()->checkEntity($attachment)) {
            throw new Forbidden();
        }

        return $attachment;
    }

}