<?php
/**
 * Copyright © 2017 Moosend. All rights reserved.
 */
namespace Moosend\WebsiteTracking\Helper;

use \Magento\Catalog\Model\CategoryFactory;

/**
 * Moosend_WebsiteTracking Page Block
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    public function __construct(
        CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }
    /**
     * Formats product options
     *
     * @param array $productConfigurations
     * @return array
     */
    public function formatProductOptions($productConfigurations)
    {
        if (count($productConfigurations) === 0) {
            return array();
        }

        $productOptions = $this->_getProductOptions($productConfigurations);
        $productAttributes = $this->_getProductAttributes($productConfigurations);

        return array_merge($productAttributes, $productOptions);
    }

    /**
     * @param $productConfigurations
     * @return array
     */
    private function _getProductOptions($productConfigurations)
    {
        if (!isset($productConfigurations['options']) || count($productConfigurations['options']) === 0) {
            return array();
        }

        $productOptions = $productConfigurations['options'];

        return $this->mapProductConfigurations($productOptions);
    }

    /**
     * @param $productConfigurations
     * @return array
     */
    private function _getProductAttributes($productConfigurations)
    {
        if (!isset($productConfigurations['attributes_info']) || count($productConfigurations['attributes_info']) === 0) {
            return array();
        }

        $productAttributes = $productConfigurations['attributes_info'];

        return $this->mapProductConfigurations($productAttributes);
    }

    /**
     * @param $productConfigurations
     * @return array
     */
    private function mapProductConfigurations($productConfigurations)
    {
        $formattedConfiguration = array();

        foreach ($productConfigurations as $productConfiguration) {
            $configurationLabel = $productConfiguration['label'];
            $configurationValue = $productConfiguration['value'];

            $formattedConfiguration[$configurationLabel] = $configurationValue;
        }

        return $formattedConfiguration;
    }

    /**
     * @param array $category_ids
     * @return mixed
     */
    public function getProductCategoryNames(array $category_ids)
    {
        $product_cats_names = array_map(function ($category) {
            $cat = $this->categoryFactory->create()->load($category);
            return $cat->getName();
        }, $category_ids);
        return implode(', ', $product_cats_names) ?: null;
    }
}
