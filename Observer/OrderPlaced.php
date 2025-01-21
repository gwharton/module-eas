<?php
namespace Gw\EAS\Observer;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Gw\EAS\Model\EAS;
use Psr\Log\LoggerInterface;

class OrderPlaced implements ObserverInterface
{
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
            try {
                $order = $observer->getOrder();
                $easCustomerGroupId = (int)$this->scopeConfig->getValue(
                    "gw_eas/general/customergroup"
                );
                if ($easCustomerGroupId === $order->getCustomerGroupId()) {
                    //Valid IOSS Order
                    $result = $this->eas->uploadOrder($order);
                    if ($result['success']) {
                        $result = $this->eas->confirmOrder($order);
                        if ($result['success']) {
                            $this->logger->info(
                                "Gw/EAS/Observer/OrderPlaced::execute() : Uploaded and Confirmed Order on EAS",
                                [
                                    'orderId' => $order->getIncrementId()
                                ]
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical(
                    "Gw/EAS/Observer/OrderPlaced::execute() : Exception",
                    [
                        'orderId' => $order->getIncrementId(),
                        'message' => $e->getMessage()
                    ]
                );
            }
        }
    }
}
