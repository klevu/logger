<?php

namespace Klevu\Logger;

/**
 * Static class containing commonly referenced constants
 */
class Constants
{
    /**#@+
     * Zend\Log log level values for internal reference following removal of zend-log module
     * @var int
     */
    const ZEND_LOG_EMERG = 0;
    const ZEND_LOG_ALERT = 1;
    const ZEND_LOG_CRIT = 2;
    const ZEND_LOG_ERR = 3;
    const ZEND_LOG_WARN = 4;
    const ZEND_LOG_NOTICE = 5;
    const ZEND_LOG_INFO = 6;
    const ZEND_LOG_DEBUG = 7;
    /**#@-*/

    const ADMIN_RESOURCE_CONFIGURATION = 'Klevu_Logger::configuration';
    const ADMIN_STORE_ID_PARAM = 'store_id';

    const XML_PATH_ENABLE_LOGGING = 'klevu_logger/configuration/enable_logging';
    const XML_PATH_MIN_LOG_LEVEL = 'klevu_logger/configuration/min_log_level';
}
