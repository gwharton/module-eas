<?php
namespace Gw\EAS\Model\ResourceModel\DebugLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Gw\EAS\Model\DebugLog;
use Gw\EAS\Model\ResourceModel\DebugLog as ResourceDebugLog;

class Collection extends AbstractCollection
{
    /**
     * Define the resource model & the model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            DebugLog::class,
            ResourceDebugLog::class
        );
    }
}
