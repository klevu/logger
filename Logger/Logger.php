<?php

namespace Klevu\Logger\Logger;

use Klevu\Logger\Api\KlevuLoggerInterface;
use Magento\Framework\Logger\Monolog;

/**
 * This class exists only for backward compatibility with 2.1.x, where injecting a LoggerInterface into
 *  a block causes the core ContextAggregation validator to fail as LoggerInterface is already defined
 *  within the context object argument
 * @todo Remove Klevu Logger when support for <2.2.0 is dropped
 */
class Logger extends Monolog implements KlevuLoggerInterface
{

}
