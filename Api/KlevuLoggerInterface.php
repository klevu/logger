<?php

namespace Klevu\Logger\Api;

use Psr\Log\LoggerInterface;

/**
 * This interface exists only for backward compatibility with 2.1.x, where injecting a LoggerInterface into
 *  a block causes the core ContextAggregation validator to fail as LoggerInterface is already defined
 *  within the context object argument
 * @todo Remove KlevuLoggerInterface when support for <2.2.0 is dropped
 */
interface KlevuLoggerInterface extends LoggerInterface
{

}
