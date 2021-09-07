<?php

namespace Klevu\Logger\Model\Source\LogLevel;

use Magento\Framework\Data\OptionSourceInterface;
use Psr\Log\LogLevel;

class Psr implements OptionSourceInterface
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
                'value' => LogLevel::EMERGENCY,
                'label' => __('Emergency'),
            ], [
                'value' => LogLevel::ALERT,
                'label' => __('Alert'),
            ], [
                'value' => LogLevel::CRITICAL,
                'label' => __('Critical'),
            ], [
                'value' => LogLevel::ERROR,
                'label' => __('Error'),
            ], [
                'value' => LogLevel::WARNING,
                'label' => __('Warning'),
            ], [
                'value' => LogLevel::NOTICE,
                'label' => __('Notice'),
            ], [
                'value' => LogLevel::INFO,
                'label' => __('Information'),
            ], [
                'value' => LogLevel::DEBUG,
                'label' => __('Debug'),
            ]
        ];
    }
}
