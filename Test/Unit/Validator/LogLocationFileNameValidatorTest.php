<?php

namespace Klevu\Logger\Test\Unit\Validator;

use Klevu\Logger\Validator\LogLocationFileNameValidator;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class LogLocationFileNameValidatorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Tests list of file names which should validate successfully
     * Happy Path
     *
     * @return void
     */
    public function testValidFileName()
    {
        $this->setupPhp5();

        /** @var LogLocationFileNameValidator $validator */
        $validator = $this->objectManager->getObject(
            LogLocationFileNameValidator::class,
            [
                'fileIo' => $this->objectManager->getObject(FileIo::class),
            ]
        );

        $fixtures = [
            'Klevu_Search.log',
            'Klevu_Search_default.log',
            'Klevu_Search-default.log',
            'Klevu_Search.default.log',
        ];
        foreach ($fixtures as $fixture) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->assertTrue(
                $validator->isValid($fixture),
                var_export($fixture, true) . PHP_EOL . implode(PHP_EOL, $validator->getMessages())
            );
        }
    }

    /**
     * Tests list of file name values which should be flagged as invalid
     *
     * @return void
     */
    public function testInvalidFileName()
    {
        $this->setupPhp5();

        /** @var LogLocationFileNameValidator $validator */
        $validator = $this->objectManager->getObject(
            LogLocationFileNameValidator::class,
            [
                'fileIo' => $this->objectManager->getObject(FileIo::class),
            ]
        );

        $fixtures = [
            'Klevu_Searchlog', // Missing extension
            'Klevu_Search/default.log', // Nested path
            'Klevu_Search@.log', // Illegal character
            '.Klevu_Search.log', // Hidden file
        ];
        foreach ($fixtures as $fixture) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->assertFalse($validator->isValid($fixture), var_export($fixture, true));
        }
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = new ObjectManager($this);
    }
}
