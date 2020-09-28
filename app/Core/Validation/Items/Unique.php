<?php
declare(strict_types=1);

namespace Dam\Core\Validation\Items;

use Dam\Core\Validation\Base;
use Dam\Repositories\Attachment;
use Espo\Core\Exceptions\BadRequest;

/**
 * Class Unique
 * @package Dam\Core\Validation\Items
 */
class Unique extends Base
{
    /**
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->skip()) {
            return true;
        }

        $md5 = md5(file_get_contents($this->attachment->get("tmpPath")));

        /**@var $repository Attachment* */
        $count = $this->getRepository("Attachment")
                      ->where([
                          'hash_md5'    => $md5,
                          'deleted'     => 0,
                          'relatedId!=' => null,
                          'createdById' => $this->getUser()->id,
                      ])->count();

        return $count == 0;
    }

    /**
     * @throws BadRequest
     */
    public function onValidateFail()
    {
        throw new BadRequest("Duplicate file");
    }
}