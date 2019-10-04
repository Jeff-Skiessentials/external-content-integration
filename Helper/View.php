<?php
namespace SkiEssentials\ContentIntegration\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
class View extends AbstractHelper {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }
}