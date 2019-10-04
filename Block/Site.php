<?php
namespace SkiEssentials\ContentIntegration\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use SkiEssentials\ContentIntegration\Model\IntegratedSite;
use SkiEssentials\ContentIntegration\Model\ResourceModel;
use SkiEssentials\ContentIntegration\Model\IntegratedSiteFactory;

class Site implements ArgumentInterface{

    /**
     * @var IntegratedSiteFactory
     */
    protected $_integratedSiteFactory;
    /**
     * @var ResourceModel\IntegratedSite
     */
    protected $_integratedSiteResource;
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var IntegratedSite
     */
    protected $_site;


    /**
     * Site constructor.
     * @param IntegratedSiteFactory $integratedSiteFactory
     * @param ResourceModel\IntegratedSite $integratedSiteResource
     * @param RequestInterface $request
     */

    public function __construct(
        IntegratedSiteFactory $integratedSiteFactory,
        ResourceModel\IntegratedSite $integratedSiteResource,
        RequestInterface $request
    )
    {
        $this->_integratedSiteFactory = $integratedSiteFactory;
        $this->_integratedSiteResource = $integratedSiteResource;
        $this->_request = $request;

    }

    /**
     * Get site based on request parameter id
     *
     * Used in index.phtml
     *
     * @return IntegratedSite|boolean
     */
    public function getSite(){
        $id = $this->_request->getParam('id');
        if ($id) {
            if(!$this->_site) {
                $this->_site = $this->_integratedSiteFactory->create();
                $this->_integratedSiteResource->load($this->_site, $id);
            }
            return $this->_site;
        }
        return false;
    }


}