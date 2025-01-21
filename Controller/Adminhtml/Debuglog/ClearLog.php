<?php
namespace Gw\EAS\Controller\Adminhtml\Debuglog;

use Exception;
use Gw\EAS\Model\ResourceModel\DebugLog;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;

class ClearLog extends Action implements HttpGetActionInterface
{
    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var DebugLog
     */
    private $debugLog;

    /**
     * @param Context $context
     * @param RedirectInterface $redirect
     * @param DebugLog $debugLog
     */
    public function __construct(
        Context $context,
        RedirectInterface $redirect,
        DebugLog $debugLog
    ) {
        $this->redirect = $redirect;
        $this->debugLog = $debugLog;
        parent::__construct($context);
    }

    /**
     * @return RedirectInterface
     * @throws LocalizedException|Exception
     */
    public function execute()
    {
        try {
            $this->debugLog->clearDebugLog();
            $this->messageManager->addSuccessMessage('Debug log Cleared.');
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }
        return $this->resultRedirectFactory->create()->setUrl($this->redirect->getRefererUrl());
    }
}
