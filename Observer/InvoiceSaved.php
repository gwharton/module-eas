<?php
namespace Gw\EAS\Observer;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Gw\EAS\Model\EAS;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class InvoiceSaved implements ObserverInterface
{
    //Any order that is being invoiced is valid.
    const array VALID_STATES = [
        Order::STATE_NEW,
        Order::STATE_PAYMENT_REVIEW,
        Order::STATE_PENDING_PAYMENT,
        Order::STATE_PROCESSING
    ];

    /**
     * @var EAS
     */
    private $eas;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EAS $eas
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        EAS $eas,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->eas = $eas;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        if ($this->scopeConfig->isSetFlag(
            'gw_eas/general/enabled_auto',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        )) {
            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();
            try {
                $easToken = $order->getExtensionAttributes()->getEasToken();
                $easCustomerGroupId = (int)$this->scopeConfig->getValue(
                    "gw_eas/general/customergroup"
                );
                if ($easCustomerGroupId === (int)$order->getCustomerGroupId() &&
                    in_array($order->getState(), self::VALID_STATES) &&
                    $easToken === null
                ) {
                    $result = $this->eas->uploadOrder($order);
                    if ($result['success']) {
                        $result = $this->eas->confirmOrder($order);
                        if ($result['success']) {
                            $this->logger->info(
                                "Gw/EAS/Observer/InvoiceSaved::execute() : Uploaded and Confirmed Order on EAS",
                                [
                                    'orderId' => $order->getIncrementId()
                                ]
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical(
                    "Gw/EAS/Observer/InvoiceSaved::execute() : Exception",
                    [
                        'orderId' => $order->getIncrementId(),
                        'message' => $e->getMessage()
                    ]
                );
            }
        }
    }
}
