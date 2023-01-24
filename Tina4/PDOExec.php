<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

class PDOExec extends DataConnection implements DataBaseExec
{

    /**
     * Execute a PDO Query Statement which ordinarily does not retrieve results
     */
    final public function exec($params, $tranId): void
    {
        if (!empty($params) ) {
            $preparedQuery = $this->getDbh()->prepare($params[0]);
            if (!empty($preparedQuery)) {
                unset($params[0]);
                $preparedQuery->execute(...$params);
            }
        }
    }
}