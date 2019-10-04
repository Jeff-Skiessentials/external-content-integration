<?php
namespace SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SkiEssentials\ContentIntegration\Model\IntegratedSite','SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite');
    }
}