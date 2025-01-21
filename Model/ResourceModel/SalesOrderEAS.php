<?php
namespace Gw\EAS\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class SalesOrderEAS extends AbstractDb
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $this->_useIsObjectNew = true;
        $this->_isPkAutoIncrement = false;
    }

    protected function _construct()
    {
        $this->_init('sales_order_eas', 'entity_id');
    }
}
