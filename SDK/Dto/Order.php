<?php
namespace Gw\EAS\SDK\Dto;

class Order
{
    /**
     * @param string $external_order_id
     * @param string $delivery_method
     * @param float $delivery_cost
     * @param string $payment_currency
     * @param bool $is_delivery_to_person
     * @param string $recipient_first_name
     * @param string $recipient_last_name
     * @param string $delivery_address_line_1
     * @param string $delivery_address_line_2
     * @param string $delivery_city
     * @param string $delivery_state_province
     * @param string $delivery_postal_code
     * @param string $delivery_country
     * @param string $delivery_phone
     * @param string $delivery_email
     * @param \Gw\EAS\SDK\Dto\OrderBreakdown[] $order_breakdown
     * @param float|null $recipient_title
     * @param string|null $recipient_company_name
     * @param string|null $recipient_company_vat
     */
    public function __construct(
        public string $external_order_id,
        public string $delivery_method,
        public float $delivery_cost,
        public string $payment_currency,
        public bool $is_delivery_to_person,
        public string $recipient_first_name,
        public string $recipient_last_name,
        public string $delivery_address_line_1,
        public string $delivery_address_line_2,
        public string $delivery_city,
        public string $delivery_state_province,
        public string $delivery_postal_code,
        public string $delivery_country,
        public string $delivery_phone,
        public string $delivery_email,
        public array $order_breakdown,
        public ?float $recipient_title = null,
        public ?string $recipient_company_name = null,
        public ?string $recipient_company_vat = null,

    ) {}
}
