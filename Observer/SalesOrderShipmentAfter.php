<?php
namespace Gw\EAS\Observer;

use Exception;
use Gw\Amazon\Model\Shipment as ShipmentModel;
use Gw\EAS\Model\EAS;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Shipment;

class SalesOrderShipmentAfter implements ObserverInterface
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
                $shipment = $observer->getEvent()->getShipment();
                if ($shipment && $shipment->getOrder()->getExtensionAttributes()->getEasToken()) {
                    $result = $this->eas->CreateShipment($shipment);
                    if ($result['success']) {
                        $this->logger->info(
                            "Gw/EAS/Observer/SalesOrderShipmentAfter::execute() : Shipped Order on EAS",
                            [
                                'orderId' => $shipment->getOrder()->getIncrementId()
                            ]
                        );
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical(
                    "Gw/EAS/Observer/SalesOrderShipmentAfter::execute() : Exception",
                    [
                        'orderId' => $shipment->getOrder()->getIncrementId(),
                        'message' => $e->getMessage()
                    ]
                );
            }
        }
    }
}
