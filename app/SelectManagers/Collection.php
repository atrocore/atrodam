<?php

declare(strict_types=1);

namespace Dam\SelectManagers;

/**
 * Class Collection
 *
 * @package Dam\SelectManagers
 */
class Collection extends AbstractSelectManager
{
    /**
     * NotEntity filter
     *
     * @param array $result
     */
    protected function boolFilterDefault(&$result)
    {
        $result['whereClause'][] = [
            'isDefault' => true,
        ];
    }
}
