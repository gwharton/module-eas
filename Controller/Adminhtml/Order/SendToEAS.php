<?php
namespace Gw\EAS\Controller\Adminhtml\Order;

use Exception;
use Gw\EAS\Model\EAS;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class SendToEAS extends Action implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EAS
     */
    private $eas;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param EAS $eas
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        EAS $eas,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->eas = $eas;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute()
    {
        try {
            $selected = $this->getRequest()->getParam('selected');
            if (is_array($selected)) {
                $selected = array_unique($selected);
            }
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(
                    'entity_id',
                    $selected,
                    'in'
                )->create();
            $orders = $this->orderRepository->getList($searchCriteria);
            foreach ($orders as $order) {
                $result = $this->eas->uploadOrder($order);
                if ($result['success']) {
                    foreach ($result['messages'] as $message) {
                        $this->messageManager->addSuccessMessage($message);
                    }
                    $result = $this->eas->confirmOrder($order);
                    if ($result['success']) {
                        foreach ($result['messages'] as $message) {
                            $this->messageManager->addSuccessMessage($message);
                        }
                    } else {
                        foreach ($result['messages'] as $message) {
                            $this->messageManager->addErrorMessage($message);
                        }
                    }
                } else {
                    foreach ($result['messages'] as $message) {
                        $this->messageManager->addErrorMessage($message);
                    }
                }
            }
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e);
            $this->logger->critical(
                "Gw/EAS/Controller/Adminhtml/Order/SendToEAS::execute() : Exception : " . $e->getMessage()
            );
        }

        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
    }
}
