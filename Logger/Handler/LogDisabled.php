<?php

namespace Klevu\Logger\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;

/**
 * Handler class used to entirely disable logging
 * Used to prevent Klevu logs being pushed into core system logs in addition to
 *  custom log files
 */
class LogDisabled extends BaseHandler
{
    /**
     * Never handle message writing, disabling write operations
     *
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record): bool
    {
        return false;
    }
}
