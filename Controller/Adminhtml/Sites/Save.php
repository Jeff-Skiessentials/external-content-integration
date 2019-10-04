<?php
namespace SkiEssentials\ContentIntegration\Controller\Adminhtml\Sites;

use SkiEssentials\ContentIntegration\Model\IntegratedSite;
use SkiEssentials\ContentIntegration\Model\IntegratedSiteFactory;

class Save extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Index';

    protected $resultPageFactory;
    protected $sitesFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \SkiEssentials\ContentIntegration\Model\IntegratedSiteFactory $sitesFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->sitesFactory = $sitesFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParam('sites');

        if(is_array($data))
        {
            try{
                $sites = $this->_objectManager->create(IntegratedSite::class);
                $sites->setData($data)->save();

                $this->messageManager->addSuccess(__('Successfully saved ' . $sites['title'] . '.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                return $resultRedirect->setPath('*/*/');
            }
            catch(\Exception $e)
            {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $data['id']]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
