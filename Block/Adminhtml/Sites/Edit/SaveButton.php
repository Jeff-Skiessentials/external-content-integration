<?php
namespace Skiessentials\ContentIntegration\Block\Adminhtml\Sites\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveButton
 */
class SaveButton extends Button implements ButtonProviderInterface {
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Site'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', []) ;
    }
}