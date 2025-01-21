<?php
namespace Gw\EAS\SDK\Dto;

final class CreateShipmentRequestDto
{
    /**
     * @param string $order_token
     * @param string $s10_code
     */
    public function __construct(
        public string $order_token,
        public string $s10_code
    ) {}
}
