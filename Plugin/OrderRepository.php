<?php
namespace Gw\EAS\Plugin;

use Exception;
use Gw\EAS\Model\SalesOrderEASRepository;
use Gw\EAS\Model\ResourceModel\SalesOrderEAS;
use Gw\EAS\Model\SalesOrderEASFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepository
{
    /**
     * @var SalesOrderEAS
     */
    private $salesOrderEAS;

    /**
     * @var SalesOrderEASFactory
     */
    private $salesOrderEASFactory;

    /**
     * @var SalesOrderEASRepository
     */
    private $salesOrderEASRepository;

    public function __construct(
        SalesOrderEAS $salesOrderEAS,
        SalesOrderEASRepository $salesOrderEASRepository,
        SalesOrderEASFactory $salesOrderEASFactory
    ) {
        $this->salesOrderEAS = $salesOrderEAS;
        $this->salesOrderEASRepository = $salesOrderEASRepository;
        $this->salesOrderEASFactory = $salesOrderEASFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $entity
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $entity
    ) {
        try {
            $soe = $this->salesOrderEASRepository->getById($entity->getEntityId());
            if ($soe->getData('eas_token') !== null) {
                $extensionAttributes = $entity->getExtensionAttributes();
                $extensionAttributes->setEasToken($soe->getData('eas_token'));
                $entity->setExtensionAttributes($extensionAttributes);
            }
        } catch (Exception $e) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setEasToken(null);
            $entity->setExtensionAttributes($extensionAttributes);
        }
        return $entity;
    }

    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $entity
    ) {
        try {
            $easToken = $entity->getExtensionAttributes()->getEasToken();
            if ($easToken !== null) {
                $soe = $this->salesOrderEASFactory->create();
                $this->salesOrderEAS->load($soe, $entity->getEntityId(), 'entity_id');
                if ($soe->getEntityId() === null) {
                    $soe->isObjectNew(true);
                    $soe->setData('entity_id', $entity->getEntityId());
                }
                $soe->setData('eas_token', $easToken);
                $this->salesOrderEASRepository->save($soe);
            }
        } catch (Exception $e) {}
        return $entity;
    }
}
