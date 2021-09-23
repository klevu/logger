<?php

namespace Klevu\Logger\Block\Adminhtml\Form\Button;

use Klevu\Logger\Api\KlevuLoggerInterface;
use Klevu\Logger\Api\LogFileNameProviderInterface;
use Klevu\Logger\Constants;
use Klevu\Logger\Validator\ArgumentValidationTrait;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Filesystem\DirectoryList as DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class LogDownloadButton extends Field
{
    use ArgumentValidationTrait;

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_template = 'Klevu_Logger::form/button/log-download.phtml';

    /**
     * @var KlevuLoggerInterface
     */
    private $logger;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var FileIo
     */
    private $fileIo;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LogFileNameProviderInterface
     */
    private $logFileNameProvider;

    /**
     * @var string
     */
    private $destinationUrl;

    /**
     * @var string
     */
    private $buttonLabel;

    /**
     * LogDownloadButton constructor.
     * @param Context $context
     * @param KlevuLoggerInterface $logger
     * @param DirectoryList $directoryList
     * @param FileIo $fileIo
     * @param LogFileNameProviderInterface $logFileNameProvider
     * @param string $destinationUrl
     * @param string $buttonLabel
     * @param array $data
     */
    public function __construct(
        Context $context,
        KlevuLoggerInterface $logger,
        DirectoryList $directoryList,
        FileIo $fileIo,
        LogFileNameProviderInterface $logFileNameProvider,
        $destinationUrl,
        $buttonLabel,
        array $data = []
    ) {
        $this->validateStringArgument($destinationUrl, __METHOD__, 'destinationUrl', false);
        $this->validateStringArgument($buttonLabel, __METHOD__, 'buttonLabel', false);

        parent::__construct($context, $data);

        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->logFileNameProvider = $logFileNameProvider;
        $this->destinationUrl = $destinationUrl;
        $this->buttonLabel = $buttonLabel;

        // Cannot inject this in via di otherwise compilation errors in 2.1.x
        $this->storeManager = $context->getStoreManager()
            ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        // Remove the scope information so it doesn't get printed out
        $element
            ->unsScope()
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $storeId = (int)$element->getData('scope_id');
        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage(), ['originalException' => $e]);

            return '';
        }

        $buttonLabel = $this->getButtonLabel($store);
        if (!$buttonLabel) {
            return '';
        }

        $fileName = $this->logFileNameProvider->execute($store);
        $commentText = __(
            'If file size is less than 1GB then you can click on button to download log file %1 from var/log folder.',
            $fileName
        );

        $element->setComment($commentText);
        $this->addData([
            "html_id" => $element->getHtmlId(),
            "button_label" => $buttonLabel,
            "destination_url" => $this->getUrl($this->destinationUrl, [
                Constants::ADMIN_STORE_ID_PARAM => $storeId,
            ]),
        ]);

        return $this->_toHtml();
    }

    /**
     * @param StoreInterface $store
     * @return string
     */
    private function getButtonLabel(StoreInterface $store)
    {
        $buttonLabel = '';

        try {
            $filePath = $this->directoryList->getPath('log')
                . DIRECTORY_SEPARATOR
                . $this->logFileNameProvider->execute($store);

            if ($this->fileIo->fileExists($filePath)) {
                $buttonLabel = sprintf(
                    '%s (%s)',
                    (string)__($this->buttonLabel),
                    $this->bytesToHumanReadable(filesize($filePath))
                );
            }
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf("Exception thrown in %s::%s - %s", __CLASS__, __METHOD__, $e->getMessage())
            );
        }

        return $buttonLabel;
    }

    /**
     * @param int $bytes
     * @param int $precision
     * @return string
     *
     */
    private function bytesToHumanReadable($bytes, $precision = 2)
    {
        if (!$bytes) {
            return '0b';
        }

        $suffixes = ["b", "k", "M", "G", "T", "P"];
        $base = log($bytes) / log(1024);

        return round(
            pow(1024, $base - floor($base)),
            $precision
        ) . $suffixes[floor($base)];
    }
}
