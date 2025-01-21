<?php
namespace Gw\EAS\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class DebugLog extends AbstractDb
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $this->_isPkAutoIncrement = true;
        $this->_useIsObjectNew = true;
    }

    protected function _construct()
    {
        $this->_init('eas_debug', 'entity_id');
    }

    public function clearDebugLog()
    {
        $connection = $this->getConnection();
        $tableName = $this->getMainTable();
        $connection->truncateTable($tableName);
    }
}
