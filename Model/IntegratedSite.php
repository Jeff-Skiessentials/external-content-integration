<?php
namespace SkiEssentials\ContentIntegration\Model;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class IntegratedSite
 * @package SkiEssentials\ContentIntegration\Model
 */
class IntegratedSite extends AbstractModel implements PageInterface, IdentityInterface
{

    /**#@+
     * Site's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Site page cache tag
     */
    const CACHE_TAG = 'se_site';

    /**
     * Site page's canonical link
     */
    const CANONICAL_LINK = 'canonical_link';

    /**#@+
     *  Additional Scripts and CSS to add to the <head> and the end of the <body>.
     */
    const ADDITIONAL_HEAD_HTML = 'additional_head_html';
    const ADDITIONAL_FOOTER_HTML = 'additional_footer_html';
    /**#@-*/

    /** Type of external site */
    const TYPE = 'type';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'se_site';

    /**
     * Has data been loaded form the external site
     *
     * @var boolean
     */
    protected $_externalSiteData = false;

    /**
     * Adapter to access external data
     *
     * @var \Magento\Framework\HTTP\Client\CurlFactory
     */
    protected $_curlFactory;

    /**
     * @var DomDocumentFactory
     */
    protected $_domDocumentFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * IntegratedSite constructor.
     * @param Context $context
     * @param RequestInterface $request
     * @param \Magento\Framework\Registry $registry
     * @param DomDocumentFactory $domDocumentFactory
     * @param CurlFactory $curlFactory
     * @param StoreManagerInterface $storeManager
     * @param ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        \Magento\Framework\Registry $registry,
        DomDocumentFactory $domDocumentFactory,
        CurlFactory $curlFactory,
        StoreManagerInterface $storeManager,
        ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_curlFactory = $curlFactory;
        $this->_domDocumentFactory = $domDocumentFactory;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SkiEssentials\ContentIntegration\Model\ResourceModel\IntegratedSite');
    }


    /**
     * Load External Data from External Site into Model
     *
     * @TODO Make this method work for all external sites. This needs some work.  It's only written for the 2020 Ski Test
     *
     */
    protected function _loadExternalData(){
        
        // Flag that external data has been loaded
        $this->_externalSiteData = true;
        
        // Create a CURL object
        $curl = $this->_curlFactory->create();

        $siteUrl = $this->_siteUrl();

        // CURL allow to follow redirects
        $curl->setOption(CURLOPT_FOLLOWLOCATION, true);


        // Get the external page
        $curl->get($siteUrl);

        if ($curl->getStatus() == 200 || $curl->getStatus() == 404 || $curl->getStatus() == 301 ) {



            $siteHtml = $curl->getBody();

            // Load DOM
            libxml_use_internal_errors(true);
            $siteDOM = $this->_domDocumentFactory->create();
            $siteDOM->loadHTML($siteHtml);
            libxml_use_internal_errors(false);

            $DOM_path = new \DOMXpath($siteDOM);  // create a DomXpath object

            // Get the title of this ski test page from the DOM as a string
            $title = $siteDOM->getElementsByTagName('title')->item(0)->nodeValue;
            $this->setTitle($title);
            $this->setMetaTitle($title);

            // Load and set the canonical link of this ski test page from the DOM path as a string
            $canonical_link_node = $DOM_path->query('/html/head/link[@rel="canonical"]');
            if ($canonical_link_node->length > 0)
                $this->setCanonicalLink($canonical_link_node->item(0)->attributes->getNamedItem('href')->nodeValue);

            // Load and set the meta description from the article body
            $meta_desc_node = $DOM_path->query('//article//div[@class="entry-content"]/p[last()] | //header//div[@class="taxonomy-description"]/p');
            if ($meta_desc_node->length > 0)
                $this->setMetaDescription($meta_desc_node->item(0)->nodeValue);


            $innerHTML = "";
            $element = $siteDOM->getElementById('page');
            $children = $element->childNodes;

            foreach ($children as $child) {
                $innerHTML .= $element->ownerDocument->saveHTML($child);
            }
            $this->setContent($innerHTML);

        } else {
            /**
             * Catch any HTTP errors returned by external site.
             *
             * @todo Redo this using Magento Best practices. Also, maybe send a notification of server error?
             */
            $this->setContent("We're sorry, but the page you're looking for could not be reached.");
        }

        
    }

    /**
     * determine the external url for the requested page
     *
     * @return string
     */
    protected function _siteUrl()
    {

        // Get the URL to the external site
        $params = '?redirect=no';
        $path = '';
        $externalSiteUrl = $this->getData()['external_site_url'] ;
        if (substr($externalSiteUrl,-1) !=='/')
            $externalSiteUrl .= '/';


        switch ($this->getType()) {
            case 'wp-full-path':
                $path = str_replace(
                    '/' . $this->getIdentifier(),
                    '',$this->_request->getPathInfo()
                );
                foreach ($this->_request->getQuery() as $key => $value)
                            $params .= '&' . $key . '=' . $value;
                break;
            case 'wp-params':
                foreach ($this->_request->getQuery() as $key => $value){
                    switch ($key) {
                        case 'skis':
                        case 'ski_testers':
                        case 'category':
                        case 'page':
                        case 'post_type':
                            $path = $key . '/';
                            if ($value)
                                $path .= $value . '/';
                            break;
                        default:
                    }
                }
                break;
        }
        $siteUrl =$externalSiteUrl . $path . $params;
        return $siteUrl;
    }

    /**
     * Public access to _loadExternalData() method
     */
     public function loadExternalData(){
        $this->_loadExternalData();
    }

    /**
     * Get Identities
     *
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        if (!$this->_externalSiteData)
            $this->_loadExternalData();
        return $this->getData(self::TITLE);
    }

    /**
     * Get page layout
     *
     * @return string
     */
    public function getPageLayout()
    {
        return $this->getData(self::PAGE_LAYOUT);
    }

    /**
     * Get meta title
     *
     * @return string|null
     * @since 101.0.0
     */
    public function getMetaTitle()
    {
        if (!$this->_externalSiteData)
            $this->_loadExternalData();
        return $this->getData(self::META_TITLE);
    }

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        if (!$this->_externalSiteData)
            $this->_loadExternalData();
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        if (!$this->_externalSiteData)
            $this->_loadExternalData();
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Check if page identifier exist for specific store
     * return id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Get content heading
     *
     * Unneeded.  Always return null
     *
     * @return null
     */
    public function getContentHeading()
    {
        return null;
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent()
    {
        if (!$this->_externalSiteData)
            $this->_loadExternalData();
        return $this->getData(self::CONTENT);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get sort order
     *
     * @return string|null
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get layout update xml
     *
     * @return string|null
     */
    public function getLayoutUpdateXml()
    {
        return $this->getData(self::LAYOUT_UPDATE_XML);
    }

    /**
     * Get custom theme
     *
     * @return string|null
     */
    public function getCustomTheme()
    {
        return $this->getData(self::CUSTOM_THEME);
    }

    /**
     * Get custom root template
     *
     * @return string|null
     */
    public function getCustomRootTemplate()
    {
        return $this->getData(self::CUSTOM_ROOT_TEMPLATE);
    }

    /**
     * Get custom layout update xml
     *
     * @return string|null
     */
    public function getCustomLayoutUpdateXml()
    {
        return $this->getData(self::CUSTOM_LAYOUT_UPDATE_XML);
    }

    /**
     * Get custom theme from
     *
     * @return string|null
     */
    public function getCustomThemeFrom()
    {
        return $this->getData(self::CUSTOM_THEME_TO);
    }

    /**
     * Get custom theme to
     *
     * @return string|null
     */
    public function getCustomThemeTo()
    {
        return $this->getData(self::CUSTOM_THEME_TO);
    }

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Get the canonical link
     *
     * @return string|null
     */
    public function getCanonicalLink()
    {
        return $this->getData(self::CANONICAL_LINK);
    }

    /**
     * Get the additional HTML for the <head>
     *
     * @return string|null
     */
    public function getAdditionalHeadHtml()
    {
        return $this->getData(self::ADDITIONAL_HEAD_HTML);
    }

    /**
     * Get the additional HTML for the end <body>
     *
     * @return string|null
     */
    public function getAdditionalFooterHtml()
    {
        return $this->getData(self::ADDITIONAL_FOOTER_HTML);
    }

    /**
     *  Get the type of integrated site
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set page layout
     *
     * @param string $pageLayout
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setPageLayout($pageLayout)
    {
        return $this->setData(self::PAGE_LAYOUT, $pageLayout);
    }

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 101.0.0
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Set content heading
     *
     * @param string $contentHeading
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setContentHeading($contentHeading)
    {
        return $this->setData(self::CONTENT_HEADING, $contentHeading);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setContent($content)
    {
        /** @todo remove these lines - temp for dev server */
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        if ($baseUrl !== 'https://www.skiessentials.com/')
            $content = str_replace('href="https://www.skiessentials.com/', 'href="' . $baseUrl, $content);

        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set layout update xml
     *
     * @param string $layoutUpdateXml
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setLayoutUpdateXml($layoutUpdateXml)
    {
        return $this->setData(self::LAYOUT_UPDATE_XML, $layoutUpdateXml);
    }

    /**
     * Set custom theme
     *
     * @param string $customTheme
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomTheme($customTheme)
    {
        return $this->setData(self::CUSTOM_THEME, $customTheme);
    }

    /**
     * Set custom root template
     *
     * @param string $customRootTemplate
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomRootTemplate($customRootTemplate)
    {
        return $this->setData(self::CUSTOM_ROOT_TEMPLATE, $customRootTemplate);
    }

    /**
     * Set custom layout update xml
     *
     * @param string $customLayoutUpdateXml
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomLayoutUpdateXml($customLayoutUpdateXml)
    {
        return $this->setData(self::CUSTOM_LAYOUT_UPDATE_XML, $customLayoutUpdateXml);
    }

    /**
     * Set custom theme from
     *
     * @param string $customThemeFrom
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomThemeFrom($customThemeFrom)
    {
        return $this->setData(self::CUSTOM_THEME_FROM, $customThemeFrom);
    }

    /**
     * Set custom theme to
     *
     * @param string $customThemeTo
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomThemeTo($customThemeTo)
    {
        return $this->setData(self::CUSTOM_THEME_TO, $customThemeTo);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set is Canonical Link
     *
     * @param string $canonicalLink
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCanonicalLink($canonicalLink)
    {
        return $this->setData(self::CANONICAL_LINK, $canonicalLink);
    }

    /**
     * Set the additional HTML for the <head>
     *
     * @param $additionalHeadHtml
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setAdditionalHeadHtml($additionalHeadHtml)
    {
        return $this->setData(self::ADDITIONAL_HEAD_HTML, $additionalHeadHtml);
    }

    /**
     * Get the additional HTML for the end <body>
     *
     * @param $additionalFootHtml
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setAdditionalFootHtml($additionalFootHtml)
    {
        return $this->setData(self::ADDITIONAL_FOOTER_HTML, $additionalFootHtml);
    }

    /**
     * Get the additional HTML for the end <body>
     *
     * @param $type
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }


}