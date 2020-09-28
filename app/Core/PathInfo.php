<?php

namespace Dam\Core;

/**
 * Interface PathInfo
 * @package Dam\Core
 */
interface PathInfo
{
    /**
     * @return array
     */
    public function getPathInfo(): array;

    /**
     * @return string
     */
    public function getMainFolder(): string;
}