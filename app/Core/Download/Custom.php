<?php

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