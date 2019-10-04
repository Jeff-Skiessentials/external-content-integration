<?php
namespace SkiEssentials\ContentIntegration\Model;
use SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite\CollectionFactory;
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $sitesCollectionFactory
     * @param array $meta
     * @param array $data
     */

    protected $collection;
    protected $_loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $sitesCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $sitesCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }

        $items = $this->collection->getItems();
        $this->_loadedData = array();
        foreach ($items as $site) {
            $this->_loadedData[$site->getId()] = array('sites' => $site->getData());
        }

        return $this->_loadedData;
    }
}