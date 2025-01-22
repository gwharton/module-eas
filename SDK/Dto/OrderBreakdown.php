<?php
namespace Gw\EAS\SDK\Dto;

class OrderBreakdown
{
    /**
     * @param string $short_description
     * @param string $long_description
     * @param string $id_provided_by_em
     * @param int $quantity
     * @param float $cost_provided_by_em
     * @param float $weight
     * @param string $hs6p_received
     * @param string $location_warehouse_country
     * @param string $type_of_goods
     * @param bool $reduced_tbe_vat_group
     * @param bool $act_as_disclosed_agent
     * @param string $seller_registration_country
     * @param string $originating_country
     */
    public function __construct(
        public string $short_description,
        public string $long_description,
        public string $id_provided_by_em,
        public int $quantity,
        public float $cost_provided_by_em,
        public float $weight,
        public string $hs6p_received,
        public string $location_warehouse_country,
        public string $type_of_goods,
        public bool $reduced_tbe_vat_group,
        public bool $act_as_disclosed_agent,
        public string $seller_registration_country,
        public string $originating_country
    ) {}
}
