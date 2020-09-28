<?php

declare(strict_types=1);

namespace Dam\Core\Validation\Items;

use Dam\Core\Validation\Base;
use Espo\Core\Exceptions\BadRequest;
use ReflectionClass;
use Treo\Core\Container;

/**
 * Class ColorSpace
 * @package Dam\Core\Validation\Items
 */
class ColorSpace extends Base
{
    /**
     * @var array|bool
     */
    private $map = [];

    /**
     * ColorSpace constructor.
     * @param Container $container
     * @throws \ReflectionException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->map = $this->createMap();
    }

    /**
     * @return bool
     * @throws \ImagickException
     */
    public function validate(): bool
    {
        $img = new \Imagick($this->attachment->get('tmpPath'));

        $colorSpace = $img->getImageColorspace();

        return in_array($this->map[$colorSpace], $this->params);
    }

    /**
     * @throws BadRequest
     */
    public function onValidateFail()
    {
        throw new BadRequest("Color space must been in list " . implode(", ", $this->params));
    }

    /**
     * @return array|bool
     * @throws \ReflectionException
     */
    private function createMap()
    {
        if ($this->skip()) {
            return true;
        }

        $imagick = new ReflectionClass(\Imagick::class);
        $res     = [];
        foreach ($imagick->getConstants() as $constantName => $constantValue) {
            if (stripos($constantName, "COLORSPACE_") === false) {
                continue;
            }

            $res[$constantValue] = str_replace("COLORSPACE_", "", $constantName);
        }

        return $res;
    }
}