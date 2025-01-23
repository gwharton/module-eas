<?php
namespace Gw\EAS\Observer;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Gw\EAS\Model\EAS;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class ShipmentSaved implements ObserverInterface
{
    const array VALID_STATES = [
        Order::STATE_PROCESSING,
        Order::STATE_COMPLETE
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
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            try {
                $easToken = $order->getExtensionAttributes()->getEasToken();
                $easCustomerGroupId = (int)$this->scopeConfig->getValue(
                    "gw_eas/general/customergroup"
                );
                if ($easCustomerGroupId === (int)$order->getCustomerGroupId() &&
                    in_array($order->getState(), self::VALID_STATES) &&
                    $easToken === null
                ) {
                    //Valid IOSS Order
                    $result = $this->eas->uploadOrder($order);
                    if ($result['success']) {
                        $result = $this->eas->confirmOrder($order);
                        if ($result['success']) {
                            $this->logger->info(
                                "Gw/EAS/Observer/ShipmentSaved::execute() : Uploaded and Confirmed Order on EAS",
                                [
                                    'orderId' => $order->getIncrementId()
                                ]
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical(
                    "Gw/EAS/Observer/ShipmentSaved::execute() : Exception",
                    [
                        'orderId' => $order->getIncrementId(),
                        'message' => $e->getMessage()
                    ]
                );
            }
        }
    }
}
