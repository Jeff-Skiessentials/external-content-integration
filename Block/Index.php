<?php
/**
 * @author Jeffrey Siegel jeffrey@skiessentials.com
 *
 */
namespace SkiEssentials\ContentIntegration\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use SkiEssentials\ContentIntegration\Model\IntegratedSite;
use SkiEssentials\ContentIntegration\Model\ResourceModel;
use SkiEssentials\ContentIntegration\Model\IntegratedSiteFactory;
use Magento\Framework\View\Page\Config;

/**
 * Class Index
 * @package SkiEssentials\ContentIntegration\Block
 *
 *
 */
class Index extends AbstractBlock implements
    IdentityInterface
{
    /**
     * @var \SkiEssentials\ContentIntegration\Model\IntegratedSite
     */
    protected $_site;
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $_pageConfig;
    /**
     * @var \SkiEssentials\ContentIntegration\Model\IntegratedSiteFactory
     */
    protected $_integratedSiteFactory;
    /**
     * @var \SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite
     */
    protected $_integratedSiteResource;

    /**
     * Index constructor.
     * @param IntegratedSiteFactory $integratedSiteFactory
     * @param ResourceModel\IntegratedSite $integratedSiteResource
     * @param Context $context
     * @param Config $pageConfig
     * @param array $data
     */
    public function __construct(
        IntegratedSiteFactory $integratedSiteFactory,
        ResourceModel\IntegratedSite $integratedSiteResource,
        Context $context,
        Config $pageConfig,
        array $data = []
    ){
        $this->_integratedSiteFactory = $integratedSiteFactory;
        $this->_pageConfig = $pageConfig;
        $this->_integratedSiteResource = $integratedSiteResource;

        parent::__construct($context, $data);
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $site = $this->getSite();
        //$this->_addBreadcrumbs($page);
        $this->_pageConfig->addBodyClass('integrated-site-' . $site->getIdentifier());
        $metaTitle = $site->getMetaTitle();
        $this->_pageConfig->getTitle()->set($metaTitle ? $metaTitle : $site->getTitle());
        $this->_pageConfig->setKeywords($site->getMetaKeywords());
        $this->_pageConfig->setDescription($site->getMetaDescription());

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Site instance
     *
     * @return \SkiEssentials\ContentIntegration\Model\IntegratedSite
     */
    public function getSite()
    {
        if (!$this->hasData('site')) {
            if ($this->getId()) {
                $site = $this->_integratedSiteFactory->create();
                $this->_integratedSiteResource->load($site, $this->getId());
            } else {
                $site = $this->_site;
            }
            $this->setData('site', $site);
        }
        return $this->getData('site');
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [IntegratedSite::CACHE_TAG . '_' . $this->getSite()->getId()];
    }

    /**
     * Return Id of requested site
     *
     * @return int|bool
     */
    public function getId()
    {
        $id = $this->_request->getParam('id');
        if ($id) {
            return $id;
        }
    }
}