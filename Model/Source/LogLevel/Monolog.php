<?php

namespace Klevu\Logger\Model\Source\LogLevel;

use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Logger;

class Monolog implements OptionSourceInterface
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
                'value' => Logger::EMERGENCY,
                'label' => __('Emergency'),
            ], [
                'value' => Logger::ALERT,
                'label' => __('Alert'),
            ], [
                'value' => Logger::CRITICAL,
                'label' => __('Critical'),
            ], [
                'value' => Logger::ERROR,
                'label' => __('Error'),
            ], [
                'value' => Logger::WARNING,
                'label' => __('Warning'),
            ], [
                'value' => Logger::NOTICE,
                'label' => __('Notice'),
            ], [
                'value' => Logger::INFO,
                'label' => __('Information'),
            ], [
                'value' => Logger::DEBUG,
                'label' => __('Debug'),
            ]
        ];
    }
}
