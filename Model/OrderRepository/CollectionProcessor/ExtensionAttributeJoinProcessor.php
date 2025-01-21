<?php

namespace Gw\EAS\Model\OrderRepository\CollectionProcessor;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class ExtensionAttributeJoinProcessor implements CollectionProcessorInterface
{
    /**
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

    /**
     * ExtensionAttributeJoinProcessor constructor.
     * @param JoinProcessorInterface $joinProcessor
     */
    public function __construct(
        JoinProcessorInterface $joinProcessor
    ) {
        $this->joinProcessor = $joinProcessor;
    }

    /**
     * @inheritDoc
     */
    public function process(SearchCriteriaInterface $searchCriteria, AbstractDb $collection)
    {
        $this->joinProcessor->process($collection);
    }
}
