<?php
namespace Gw\EAS\Model;

use Exception;
use Gw\EAS\Model\SalesOrderEAS as Model;
use Gw\EAS\Model\SalesOrderEASFactory as Factory;
use Gw\EAS\Model\ResourceModel\SalesOrderEAS as Resource;
use Gw\EAS\Model\ResourceModel\SalesOrderEAS\CollectionFactory;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesOrderEASRepository
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param Resource $resource
     * @param Factory $factory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        Resource $resource,
        Factory $factory,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param Model $model
     * @return Model
     * @throws CouldNotSaveException
     */
    public function save(Model $model): Model
    {
        try {
            $this->resource->save($model);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the model: %1', $exception->getMessage()),
                $exception
            );
        }
        return $model;
    }

    /**
     * @param $modelId
     * @return Model
     * @throws NoSuchEntityException
     */
    public function getById($modelId): Model
    {
        $model = $this->factory->create();
        $this->resource->load($model, $modelId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The model with ID "%1" doesn\'t exist.', $modelId));
        }
        return $model;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResults
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $items = $collection->getItems();
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param Model $model
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Model $model): bool
    {
        try {
            $this->resource->delete($model);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the model: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * @param $modelId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($modelId): bool
    {
        return $this->delete($this->getById($modelId));
    }
}
