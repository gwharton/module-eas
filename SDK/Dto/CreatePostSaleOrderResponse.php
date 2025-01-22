<?php
namespace Gw\EAS\SDK\Dto;

class CreatePostSaleOrderResponse
{
    /**
     * @param float $delivery_charge_vat
     * @param float $merchandise_cost_vat_excl
     * @param float $merchandise_cost
     * @param float $delivery_charge
     * @param float $delivery_charge_vat_excl
     * @param string $delivery_country
     * @param string $payment_currency
     * @param float $merchandise_vat
     * @param float $eas_fee_vat
     * @param float $total_order_amount
     * @param float $total_customs_duties
     * @param float $eas_fee
     * @param string $order_id
     * @param \Gw\EAS\SDK\Dto\Item[] $items
     * @param float $taxes_and_duties
     * @param string $FID
     * @param string $id
     * @param string $timestamp_year
     * @param int $iat
     * @param int $exp
     * @param string $aud
     * @param string $iss
     * @param string $sub
     * @param string $jti
     * @param string|null $delivery_address
     */
    public function __construct(
        public float $delivery_charge_vat,
        public float $merchandise_cost_vat_excl,
        public float $merchandise_cost,
        public float $delivery_charge,
        public float $delivery_charge_vat_excl,
        public string $delivery_country,
        public string $payment_currency,
        public float $merchandise_vat,
        public float $eas_fee_vat,
        public float $total_order_amount,
        public float $total_customs_duties,
        public float $eas_fee,
        public string $order_id,
        public array $items,
        public float $taxes_and_duties,
        public string $FID,
        public string $id,
        public string $timestamp_year,
        public int $iat,
        public int $exp,
        public string $aud,
        public string $iss,
        public string $sub,
        public string $jti,
        public ?string $delivery_address = null
    ) {}
}
