<?php
namespace Gw\EAS\Controller\Adminhtml\Debuglog;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Gw_EAS::easdebuglog');
        $resultPage->addBreadcrumb(__('EAS Debug Log'), __('EAS Debug Log'));
        $resultPage->getConfig()->getTitle()->prepend(__('EAS Debug Log'));
        return $resultPage;
    }
}
