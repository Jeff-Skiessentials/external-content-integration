<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SkiEssentials\ContentIntegration\Model;

use Magento\Framework\Data\OptionSourceInterface;


/**
 * Class PageLayout
 */
class SiteType implements OptionSourceInterface{



    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => __('Wordpress - Full Path URLs'), 'value' => __('wp-full-path')],
            ['label' => __('Wordpress - Parameter URLs'), 'value' => __('wp-params')]
        ];
        return $options;
    }
}