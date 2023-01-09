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

namespace Dam\Core\Thumbnail;

use Dam\Core\Utils\PDFLib;
use Espo\Entities\Attachment;

/**
 * Class Pdf
 */
class Image extends \Espo\Core\Thumbnail\Image
{
    /**
     * @inheritDoc
     */
    protected function getImageFilePath(Attachment $attachment): string
    {
        if ($this->isPdf($attachment)) {
            return $this->createImageFromPdf($attachment->getFilePath());
        }

        return parent::getImageFilePath($attachment);
    }

    /**
     * @param Attachment $attachment
     *
     * @return bool
     */
    protected function isPdf(Attachment $attachment): bool
    {
        $parts = explode('.', $attachment->get('name'));

        return strtolower(array_pop($parts)) === 'pdf';
    }

    /**
     * @param string $pdfPath
     *
     * @return string
     * @throws \Exception
     */
    protected function createImageFromPdf(string $pdfPath): string
    {
        $pathParts = explode('/', $pdfPath);
        $fileName = array_pop($pathParts);
        $dirPath = implode('/', $pathParts);

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