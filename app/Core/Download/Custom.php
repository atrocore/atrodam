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

namespace Dam\Core\Download;

use Dam\Entities\Attachment;
use Imagick;

/**
 * Class Custom
 * @package Dam\Core\Download
 */
class Custom
{
    /**
     * @var Imagick|null
     */
    protected $imagick = null;
    /**
     * @var string
     */
    protected $scale;
    /**
     * @var integer
     */
    protected $width;
    /**
     * @var integer
     */
    protected $height;
    /**
     * @var integer
     */
    protected $quality;
    /**
     * @var string
     */
    protected $format;
    /**
     * @var Attachment
     */
    protected $attachment;

    /**
     * Custom constructor.
     * @param string $filePath
     * @throws \ImagickException
     */
    public function __construct(string $filePath)
    {
        $this->imagick = new \Imagick($filePath);
    }

    /**
     * @param Attachment $attachment
     * @return $this
     */
    public function setAttachment(Attachment $attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        foreach ($params as $propName => $value) {
            if (!property_exists($this, $propName)) {
                continue;
            }

            $this->{$propName} = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->imagick->getImageBlob();
    }

    /**
     * @return Custom
     */
    public function convert()
    {
        return $this->resize()->quality()->format();
    }

    /**
     * @return int
     */
    public function getImageSize()
    {
        return mb_strlen($this->imagick->getImageBlob(), "8bit");
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        $name = explode(".", $this->attachment->get("name"));
        array_pop($name);
        $name[] = $this->format === "png" ? "png" : "jpeg";

        return str_replace("\"", "\\\"", implode(".", $name));
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->format === "png" ? "image/png" : "image/jpeg";
    }

    /**
     * @return $this
     */
    protected function resize()
    {
        switch ($this->scale) {
            case "resize":
                $this->imagick->resizeImage(
                    (int)$this->width,
                    (int)$this->height,
                    Imagick::FILTER_HAMMING,
                    1, false
                );
                break;
            case "byWidth":
                $this->imagick->resizeImage(
                    (int)$this->width,
                    1000000000,
                    Imagick::FILTER_HAMMING,
                    1,
                    true
                );
                break;
            case "byHeight" :
                $this->imagick->resizeImage(
                    1000000000,
                    (int)$this->height,
                    Imagick::FILTER_HAMMING,
                    1,
                    true
                );
                break;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function quality()
    {
        switch (true) {
            case $this->format === "jpeg":
                $this->imagick->setImageCompressionQuality((int)$this->quality);
                break;
            case $this->format === "png" :
                break;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \ImagickException
     */
    protected function format()
    {
        if ($this->format === "jpeg") {
            $this->imagick->setBackgroundColor("#ffffff");
            $this->imagick = $this->imagick->flattenImages();
        }
        $this->imagick->setImageFormat($this->format);

        return $this;
    }
}