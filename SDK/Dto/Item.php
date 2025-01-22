<?php
namespace Gw\EAS\SDK\Dto;

class Item
{
    /**
     * @param string $item_id
     * @param int $quantity
     * @param float $item_cost
     * @param float $unit_cost_excl_vat
     * @param float $item_delivery_charge
     * @param float $item_delivery_charge_vat_excl
     * @param float $item_delivery_charge_vat
     * @param float $item_customs_duties
     * @param float $item_eas_fee
     * @param float $item_eas_fee_vat
     * @param string $applicable_hs6p
     * @param float $vat_rate
     * @param float $item_duties_and_taxes
     * @param string $customs_duties_rate
     */
    public function __construct(
        public string $item_id,
        public int $quantity,
        public float $item_cost,
        public float $unit_cost_excl_vat,
        public float $item_delivery_charge,
        public float $item_delivery_charge_vat_excl,
        public float $item_delivery_charge_vat,
        public float $item_customs_duties,
        public float $item_eas_fee,
        public float $item_eas_fee_vat,
        public string $applicable_hs6p,
        public float $vat_rate,
        public float $item_duties_and_taxes,
        public string $customs_duties_rate
    ) {}
}
