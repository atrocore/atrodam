<?php

declare(strict_types=1);

namespace Dam\Core\Preview;

use Dam\Entities\Attachment;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use Imagick;
use Treo\Core\Container;

/**
 * Class Pdf
 * @package Dam\Core\Preview
 */
class Pdf extends Base
{
    /**
     * @var Imagick|null
     */
    protected $imagick = null;

    /**
     * Pdf constructor.
     * @param Attachment $attachment
     * @param string     $size
     * @param Container  $container
     * @throws \ImagickException
     */
    public function __construct(Attachment $attachment, string $size, Container $container)
    {
        parent::__construct($attachment, $size, $container);

        $this->imagick = new Imagick();
    }

    public function show()
    {
        $filePath = $this->getEntityManager()->getRepository('Attachment')->getFilePath($this->attachment);

        $fileType = $this->attachment->get('type');

        if (!file_exists($filePath)) {
            throw new NotFound();
        }

        if (!empty($this->size)) {
            if (!empty($this->imageSizes[$this->size]) || $this->size === "original") {
                $thumbFilePath = $this->buildPath($this->attachment, $this->size);

                if (!file_exists($thumbFilePath)) {
                    $image         = $this->createImageFromPdf($filePath);
                    $thumbFilePath = $this->createThumb($thumbFilePath, $image, $this->size);
                }
                $filePath = $thumbFilePath;
            } else {
                throw new Error();
            }
        }

        $fileName = $this->attachment->get('name');

        header('Content-Disposition:inline;filename="' . $fileName . '"');
        if (!empty($fileType)) {
            header('Content-Type: ' . ($fileType === "application/pdf" ? "image/png" : $fileType));
        }
        header('Pragma: public');
        header('Cache-Control: max-age=360000, must-revalidate');
        $fileSize = filesize($filePath);
        if ($fileSize) {
            header('Content-Length: ' . $fileSize);
        }
        readfile($filePath);
        exit;
    }

    /**
     * @param $thumbFilePath
     * @param $filePath
     * @param $size
     * @return mixed
     * @throws Error
     * @throws \Gumlet\ImageResizeException
     * @throws \ImagickException
     */
    protected function createThumb($thumbFilePath, $filePath, $size)
    {
        $pathInfo      = pathinfo($thumbFilePath);
        $thumbFilePath = $pathInfo['dirname'] . "/" . $pathInfo['filename'] . ".png";

        if ($size !== "original") {
            list($w, $h) = $this->imageSizes[$size];

            $this->imagick->resizeImage($w, $h, Imagick::FILTER_HAMMING, 1, true);
        }

        if ($this->getFileManager()->putContents($thumbFilePath, $this->imagick->getImageBlob())) {
            return $thumbFilePath;
        }

        return false;
    }

    /**
     * @param $filePath
     * @return $this
     * @throws \ImagickException
     */
    protected function createImageFromPdf($filePath)
    {
        $this->imagick->setResolution(120, 120);
        $this->imagick->readImage($filePath . "[0]");
        $this->imagick->setimageformat("png");
        $this->imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

        return $this;
    }
}