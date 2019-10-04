<?php
namespace SkiEssentials\ContentIntegration\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use SkiEssentials\ContentIntegration\Model\IntegratedSiteFactory;
use SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite;
use Magento\Framework\App\RequestInterface;


class Index extends Action
{
    /**
     * @var PageFactory
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
        RequestInterface $request,
        IntegratedSiteFactory $integratedSiteFactory,
        IntegratedSite $integratedSiteResource
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_request = $request;
        $this->_integratedSiteFactory = $integratedSiteFactory;
        $this->_integratedSiteResource = $integratedSiteResource;

    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create(false, ['isIsolated' => true]);


        $id = $this->_request->getParam('id');
        if ($id) {

            // load site model by passed id
            $site = $this->_integratedSiteFactory->create();
            $this->_integratedSiteResource->load($site, $id);

            // set page layout to the layout selected for this site
            $resultPage->getConfig()->setPageLayout($site->getPageLayout());
            $resultPage->addPageLayoutHandles(['id' => str_replace('/', '_', $site->getIdentifier())]);

            // Update site information for document head
            $resultPage->getConfig()->getTitle()->set($site->getTitle());
            $resultPage->getConfig()->setMetaTitle($site->getMetaTitle());
            $resultPage->getConfig()->setMetadata('description', $site->getMetaDescription());


            /** @TODO Check for existing canonical link and remove it before adding the new one */
            $resultPage->getConfig()->addRemotePageAsset(
                $site->getCanonicalLink(),
                'canonical',
                ['attributes' => ['rel' => 'canonical' ]]
            );


         }

        return $resultPage;
    }


}



