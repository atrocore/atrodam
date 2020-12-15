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

namespace Dam\Core\Preview;

use Dam\Entities\Attachment;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use ImalH\PDFLib\PDFLib;
use Treo\Core\Container;

/**
 * Class Pdf
 */
class Pdf extends Base
{
    /**
     * @var \Imagick
     */
    protected $imagick;

    /**
     * @inheritDoc
     */
    public function __construct(Attachment $attachment, string $size, Container $container)
    {
        parent::__construct($attachment, $size, $container);

        $this->imagick = new \Imagick();
    }

    /**
     * Show PDF preview image
     *
     * @return mixed|void
     * @throws Error
     * @throws NotFound
     */
    public function show()
    {
        $filePath = $this->getEntityManager()->getRepository('Attachment')->getFilePath($this->attachment);
        if (!file_exists($filePath)) {
            throw new NotFound();
        }

        // create original
        $originalImagePath = $this->createImageFromPdf($filePath);

        $thumbFilePath = $originalImagePath;
        if (!empty($this->size) && !empty($this->imageSizes[$this->size])) {
            $createThumbPath = $this->createThumb($originalImagePath, $originalImagePath, $this->size);
            if (is_string($createThumbPath)) {
                $thumbFilePath = $createThumbPath;
            }
        }

        header('Content-Disposition:inline;filename="' . $this->attachment->get('name') . '"');
        header('Content-Type: image/png');
        header('Pragma: public');
        header('Cache-Control: max-age=360000, must-revalidate');
        $fileSize = filesize($thumbFilePath);
        if ($fileSize) {
            header('Content-Length: ' . $fileSize);
        }
        readfile($thumbFilePath);
        exit;
    }

    /**
     * @param string $pdfPath
     *
     * @return string
     * @throws \Exception
     */
    protected function createImageFromPdf(string $pdfPath): string
    {
        $dirPath = str_replace('.', '_', $this->buildPath($this->attachment, $this->size));
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $original = $dirPath . '/page-1.png';
        if (!file_exists($original)) {
            $pdflib = new PDFLib();
            $pdflib->setPdfPath($pdfPath);
            $pdflib->setOutputPath($dirPath);
            $pdflib->setImageFormat(PDFLib::$IMAGE_FORMAT_PNG);
            $pdflib->setPageRange(1, 1);
            $pdflib->setFilePrefix('page-');
            $pdflib->convert();
        }

        return $original;
    }
}