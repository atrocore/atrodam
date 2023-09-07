<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.txt, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

declare(strict_types=1);

namespace Dam\EntryPoints;

use Dam\Core\Download\Custom;
use Espo\Entities\Attachment;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;

class Download extends \Espo\EntryPoints\Download
{
    public function run()
    {
        if (!empty($_GET['type']) && $_GET['type'] === 'custom') {
            $this->custom();
        }

        parent::run();
    }

    protected function custom(): void
    {
        $converter = $this
            ->getContainer()
            ->get(Custom::class)
            ->setAttachment($this->getAttachment())
            ->setParams($_GET)
            ->convert();

        header("Location: " . $converter->getFilePath(), true, 302);
        exit;
    }

    protected function getAttachment(): Attachment
    {
        if (empty($_GET['id'])) {
            throw new BadRequest();
        }

        $attachment = $this->getEntityManager()->getEntity('Attachment', $_GET['id']);
        if (empty($attachment)) {
            throw new NotFound();
        }

        if (!$this->getAcl()->checkEntity($attachment)) {
            throw new Forbidden();
        }

        return $attachment;
    }
}
