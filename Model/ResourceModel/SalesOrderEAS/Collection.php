<?php
namespace Gw\EAS\Model\ResourceModel\SalesOrderEAS;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Gw\EAS\Model\SalesOrderEAS;
use Gw\EAS\Model\ResourceModel\SalesOrderEAS as SalesOrderEASResource;

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
            SalesOrderEAS::class,
            SalesOrderEASResource::class
        );
    }
}
