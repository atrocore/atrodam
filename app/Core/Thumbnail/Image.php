<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
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