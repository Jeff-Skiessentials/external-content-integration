<?php
namespace SkiEssentials\ContentIntegration\Controller;

/**
 * SkiEssentials ContentIntegration Router
 *
 * @author      Jeffrey Siegel jeffrey@skiessentials.com
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * sites
     * @var \SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite\CollectionFactory
     */

    protected $_sites;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite\CollectionFactory $sites
    ) {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->_response = $response;
        $this->_sites = $sites;
    }

    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        // divide the path by '/' and get the first level
        if (substr_count($request->getPathInfo(), '/') >= 1) {
            list($dummy, $identifier) = explode('/', $request->getPathInfo());
        } else {
            return null;
        }

        // create a collection of sites that match the identifier. Should be unique.
        $sites = $this->_sites->create()
            ->addFieldToFilter('identifier', $identifier)
            ->addFieldToFilter('is_active', 1);

        if (!empty($sites->getData())) {
            $siteId = $sites->getFirstItem()->getId();
            if ($siteId) {

                $request->setModuleName('contentintegration')
                    ->setControllerName('index')
                    ->setActionName('index')
                    ->setParam('id', $siteId);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

                return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);

            }
        }

        return null;
    }
}
