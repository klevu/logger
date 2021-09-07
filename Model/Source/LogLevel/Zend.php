<?php

namespace Klevu\Logger\Model\Source\LogLevel;

use Klevu\Logger\Constants;
use Magento\Framework\Data\OptionSourceInterface;

class Zend implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Constants::ZEND_LOG_EMERG,
                'label' => __('Emergency'),
            ], [
                'value' => Constants::ZEND_LOG_ALERT,
                'label' => __('Alert'),
            ], [
                'value' => Constants::ZEND_LOG_CRIT,
                'label' => __('Critical'),
            ], [
                'value' => Constants::ZEND_LOG_ERR,
                'label' => __('Error'),
            ], [
                'value' => Constants::ZEND_LOG_WARN,
                'label' => __('Warning'),
            ], [
                'value' => Constants::ZEND_LOG_NOTICE,
                'label' => __('Notice'),
            ], [
                'value' => Constants::ZEND_LOG_INFO,
                'label' => __('Information'),
            ], [
                'value' => Constants::ZEND_LOG_DEBUG,
                'label' => __('Debug'),
            ]
        ];
    }
}
