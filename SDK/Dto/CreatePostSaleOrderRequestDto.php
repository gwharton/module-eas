<?php
namespace Gw\EAS\SDK\Dto;

final class CreatePostSaleOrderRequestDto
{
    /**
     * @param string $sale_date
     * @param \Gw\EAS\SDK\Dto\Order $order
     * @param string|null $s10_code
     */
    public function __construct(
        public string $sale_date,
        public Order $order,
        public ?string $s10_code = null
    ) {}
}
