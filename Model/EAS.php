<?php
namespace Gw\EAS\Model;

use DateTimeImmutable;
use Exception;
use Gw\EAS\SDK\Dto\ConfirmPostSaleOrderRequest;
use Gw\EAS\SDK\Dto\CreatePostSaleOrderRequest;
use Gw\EAS\SDK\Dto\Order;
use Gw\EAS\SDK\Dto\OrderBreakdown;
use Gw\EAS\SDK\EASConnector;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Gw\HsCode\Model\HsCode;
use Magento\Sales\Api\Data\ShipmentInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class EAS
{
    /**
     * @var EASConnector
     */
    private $easConnector;

    /**
     * @var HsCode
     */
    private $hsCode;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        EASConnector $easConnector,
        HsCode $hsCode,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->easConnector = $easConnector;
        $this->hsCode = $hsCode;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function uploadOrder(OrderInterface $order)
    {
        try {
            $easToken = $order->getExtensionAttributes()->getEasToken();
            if ($easToken) {
                $this->logger->error(
                    "Gw/EAS/Model/EAS::uploadOrder() : Order has already been created on EAS",
                    [
                        'orderId' => $order->getIncrementId()
                    ]
                );
                return [
                    'success' => false,
                    'messages' => [
                        "Order has already been created on EAS"
                    ]
                ];
            }

            $easCustomerGroupId = (int)$this->scopeConfig->getValue(
                "gw_eas/general/customergroup"
            );
            if ($easCustomerGroupId !== $order->getCustomerGroupId()) {
                $this->logger->error(
                    "Gw/EAS/Model/EAS::uploadOrder() : Order is not IOSS",
                    [
                        'orderId' => $order->getIncrementId()
                    ]
                );
                return [
                    'success' => false,
                    'messages' => [
                        "Order is not IOSS"
                    ]
                ];
            }

            $saleDate = new DateTimeImmutable($order->getCreatedAt());
            $shippingAddress = $order->getShippingAddress();
            $streetLine1 = $shippingAddress->getStreetLine(1);
            $streetLine2 = $shippingAddress->getStreetLine(2);
            $streetLine3 = $shippingAddress->getStreetLine(3);

            if ($streetLine2) {
                if ($streetLine3) {
                    $streetLine2 = $streetLine2 . ', ' . $streetLine3;
                }
            } else {
                $streetLine2 = "";
            }
            $orderBreakdown = [];
            foreach ($order->getItems() as $item) {
                $orderBreakdown[] = new OrderBreakdown(
                    $item->getName(),
                    $item->getProduct()->getData("extended_customs_description"),
                    $item->getSku(),
                    $item->getQtyOrdered(),
                    ($item->getRowTotal() + $item->getTaxAmount() - $item->getDiscountAmount()) / $item->getQtyOrdered(),
                    $item->getWeight(),
                    $this->hsCode->getProductHsCode($item->getProduct(), $shippingAddress->getCountryId()),
                    "GB",
                    "GOODS",
                    false,
                    true,
                    "GB",
                    $item->getProduct()->getData("country_of_manufacture")
                );
            }
            $requestDto = new CreatePostSaleOrderRequest(
                $saleDate->format('Y-m-d'),
                new Order(
                    $order->getIncrementId(),
                    "postal", //TODO work this out
                    $order->getShippingInclTax(),
                    $order->getOrderCurrencyCode(),
                    true,
                    $shippingAddress->getFirstname(),
                    $shippingAddress->getLastname(),
                    $streetLine1,
                    $streetLine2,
                    $shippingAddress->getCity(),
                    $shippingAddress->getRegion() ?? "",
                    $shippingAddress->getPostcode(),
                    $shippingAddress->getCountryId(),
                    $shippingAddress->getTelephone(),
                    $shippingAddress->getEmail(),
                    $orderBreakdown,
                    $shippingAddress->getPrefix(),
                    null,
                    null
                )
            );
            $response = $this->easConnector->CreatePostSaleOrder($requestDto);
            if (!$response->failed()) {
                $responseDto = $response->dto();
                $extensionAttributes = $order->getExtensionAttributes();
                $extensionAttributes->setEasToken(trim($response->body(), "\""));
                $order->setExtensionAttributes($extensionAttributes);
                $this->orderRepository->save($order);
                return [
                    'success' => true,
                    'messages' => [
                        "Order " . $order->getIncrementId() . " created on EAS (" . $responseDto->id . ")"
                    ]
                ];
            } else {
                $this->logger->critical(
                    "Gw/EAS/Model/EAS::uploadOrder() : " .
                    "Network Error communicating with EAS API",
                    [
                        'response' => $response->body(),
                    ]
                );
                return [
                    'success' => false,
                    'messages' => [
                        $response->body(),
                    ]
                ];
            }
        } catch (Exception $exception) {
            $this->logger->critical(
                "Gw/EAS/Model/EAS::uploadOrder() : Exception",
                [
                    'orderId' => $order->getIncrementId(),
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ]
            );
            return [
                'success' => false,
                'messages' => [
                    "Exception: " . $exception->getCode() . " - " . $exception->getMessage()
                ]
            ];
        }
    }

    public function confirmOrder(OrderInterface $order)
    {
        try {
            $easToken = $order->getExtensionAttributes()->getEasToken();
            if (!$easToken) {
                $this->logger->critical(
                    "Gw/EAS/Model/EAS::confirmOrder() : Missing Order Token",
                    [
                        'orderId' => $order->getIncrementId()
                    ]
                );
                return [
                    'success' => false,
                    'messages' => [
                        "Missing Order Token"
                    ]
                ];
            }

            $requestDto = new ConfirmPostSaleOrderRequest(
                $easToken
            );
            $response = $this->easConnector->ConfirmPostSaleOrder($requestDto);
            if (!$response->failed()) {
                $this->logger->info(
                    "Gw/EAS/Model/EAS::confirmOrder() : Confirmed Order with EAS",
                    [
                        'orderId' => $order->getIncrementId()
                    ]
                );
                return [
                    'success' => true,
                    'messages' => []
                ];
            } else {
                $this->logger->error(
                    "Gw/EAS/Model/EAS::confirmOrder() : Unable to Confirm Order with EAS",
                    [
                        'orderId' => $order->getIncrementId(),
                        'response' => $response->body()
                    ]
                );
                return [
                    'success' => false,
                    'messages' => [
                        "Unable to Confirm Order with EAS"
                    ]
                ];
            }
        } catch (Exception $exception) {
            $this->logger->critical(
                "Gw/EAS/Model/EAS::confirmOrder() : Exception",
                [
                    'orderId' => $order->getIncrementId(),
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ]
            );
            return [
                'success' => false,
                'messages' => [
                    "Exception: " . $exception->getCode() . " - " . $exception->getMessage()
                ]
            ];
        }
    }
}
