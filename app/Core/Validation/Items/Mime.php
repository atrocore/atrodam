<?php

declare(strict_types=1);

namespace Dam\Core\Validation\Items;

use Dam\Core\Validation\Base;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;

/**
 * Class Mime
 * @package Dam\Core\Validation\Items
 */
class Mime extends Base
{
    /**
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->skip()) {
            return true;
        }

        $mimeType = mime_content_type($this->attachment->get('tmpPath'));

        if (isset($this->params['list'])) {
            return in_array($mimeType, $this->params['list']);
        } elseif (isset($this->params['pattern'])) {
            return stripos($mimeType, $this->params['pattern']) === false ? false : true;
        }

        return true;
    }

    /**
     * @throws BadRequest
     */
    public function onValidateFail()
    {
        $message = $this->params['message'] ?? "Incorrect MIME type";

        throw new BadRequest($message);
    }
}