<?php

declare(strict_types=1);

namespace Dam\Core\Utils;

use Imagick;
use ReflectionClass;

/**
 * Class Util
 * @package Dam\Core\Utils
 */
class Util extends \Treo\Core\Utils\Util
{
    /**
     * @param Imagick $imagick
     * @return |null
     * @throws \ReflectionException
     */
    public static function getColorSpace(Imagick $imagick)
    {
        $colorId = $imagick->getImageColorspace();

        if (!$colorId) {
            return null;
        }

        foreach ((new ReflectionClass($imagick))->getConstants() as $name => $value) {
            if (stripos($name, "COLORSPACE_") !== false && $value == $colorId) {
                $el = explode("_", $name);
                array_shift($el);

                return implode("_", $el);
            }
        }

        return null;
    }
}