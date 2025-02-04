<?php
namespace Gw\EAS\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Environment implements ArrayInterface
{
    const ENVIRONMENT_SANDBOX = 'sandbox';
    const ENVIRONMENT_PRODUCTION = 'production';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ENVIRONMENT_SANDBOX, 'label' => __('Sandbox')],
            ['value' => self::ENVIRONMENT_PRODUCTION, 'label' => __('Production')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::ENVIRONMENT_SANDBOX => __('Sandbox'),
            self::ENVIRONMENT_PRODUCTION => __('Production')
        ];
    }
}
