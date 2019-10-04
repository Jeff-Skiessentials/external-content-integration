<?php

namespace SkiEssentials\ContentIntegration\Controller\Adminhtml\Sites;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use SkiEssentials\ContentIntegration\Model\IntegratedSiteFactory;

/**
 * Class Index
 * @package SkiEssentials\ContentIntegration\Controller\Adminhtml\Sites
 */
class Index extends Action
{
    /**
     * @var bool|PageFactory
     */
    protected $_resultPageFactory = false;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Sites')));

        return $resultPage;
    }


    /**
     * Access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SkiEssentials_ContentIntegration::integrated_content');
    }
}
