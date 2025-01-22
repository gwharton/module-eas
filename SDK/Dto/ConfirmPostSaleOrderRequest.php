<?php
namespace Gw\EAS\SDK\Dto;

final class ConfirmPostSaleOrderRequest
{
    /**
     * @param string $order_token
     */
    public function __construct(
        public string $order_token
    ) {}
}
