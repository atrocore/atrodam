<?php

declare(strict_types=1);

namespace Dam\Core\Preview\Icons;

use Dam\Core\Preview\Base;

/**
 * Class File
 * @package Dam\Core\Preview\Icons
 */
class File extends Base
{
    const ICON_PATH = "app/Data/PreviewIcons/";

    protected $type = "file";

    /**
     * @return mixed|void
     */
    public function show()
    {
        $icon = $this->getIconContent();

        header('Content-Type: image/svg+xml');
        header('Pragma: public');
        header('Cache-Control: max-age=360000, must-revalidate');
        header('Content-Length: ' . mb_strlen($icon, "8bit"));

        echo $icon;
        exit;
    }

    protected function getIconPath()
    {
        $modulePath = $this->getModuleManager()->getModule("Dam")->getPath();

        return $modulePath . self::ICON_PATH . "{$this->type}_icon.svg";
    }


    protected function getIconContent()
    {
        $iconPath = $this->getIconPath();

        return file_get_contents($iconPath);
    }
}