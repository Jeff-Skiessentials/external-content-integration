<?php
namespace SkiEssentials\ContentIntegration\Model\ResourceModel;
class IntegratedSite extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('skiessentials_contentintegration', 'id');
    }
}