<?php
namespace SkiEssentials\ContentIntegration\Controller\Adminhtml\Sites;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use SkiEssentials\ContentIntegration\Model\IntegratedSiteFactory;
use SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action
{
    /**
     * Edit An Integrated Site
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */

    protected $_resultPageFactory = false;
    /**
     * @var IntegratedSiteFactory
     */
    protected $_integratedSiteFactory;
    /**
     * @var IntegratedSite
     */
    protected $_integratedSiteResource;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        IntegratedSiteFactory $integratedSiteFactory,
        IntegratedSite $integratedSiteResource
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_integratedSiteFactory = $integratedSiteFactory;
        $this->_integratedSiteResource = $integratedSiteResource;
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Sites')));

        $id = $this->_request->getParam('id');
        if ($id) {

            // load site model by passed id
            $site = $this->_integratedSiteFactory->create();
            $this->_integratedSiteResource->load($site, $id);
            $resultPage->getConfig()->getTitle()->prepend(__('Edit -') . __($site['title']));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Create a New Site Integration'));
        }

        return $resultPage;
    }


    /**
     * Access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SkiEssentials_ContentIntegration::edit_integrated_content');
    }

}