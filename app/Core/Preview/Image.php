<?php

declare(strict_types=1);

namespace Dam\Core\Preview;

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use Treo\Core\Container;

/**
 * Class Image
 * @package Dam\Core\Preview
 */
class Image extends Base
{
    /**
     * @throws Error
     * @throws NotFound
     * @throws \Gumlet\ImageResizeException
     */
    public function show()
    {
        $filePath = $this->getEntityManager()->getRepository('Attachment')->getFilePath($this->attachment);

        $fileType = $this->attachment->get('type');

        if (!file_exists($filePath)) {
            throw new NotFound();
        }

        if (!empty($this->size) && $this->size !== "original") {
            if (!empty($this->imageSizes[$this->size])) {
                $thumbFilePath = $this->buildPath($this->attachment, $this->size);

                if (!file_exists($thumbFilePath)) {
                    $thumbFilePath = $this->createThumb($thumbFilePath, $filePath, $this->size);
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
}