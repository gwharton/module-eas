<?php
namespace Gw\EAS\Model;

use Magento\Framework\Model\AbstractModel;
use Gw\EAS\Model\ResourceModel\SalesOrderEAS as SalesOrderEASResource;

class SalesOrderEAS extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(SalesOrderEASResource::class);
    }
}
