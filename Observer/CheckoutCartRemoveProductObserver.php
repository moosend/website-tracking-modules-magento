<?php
/**
 * Copyright © 2017 Moosend. All rights reserved.
 */
namespace Moosend\WebsiteTracking\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Helper\Image;
use \Moosend\WebsiteTracking\Helper\Data;
use \Moosend\TrackerFactory;

class CheckoutCartRemoveProductObserver implements ObserverInterface
{
	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $configInterface;
    /**
     * @var \Moosend\TrackerFactory
     */
    protected $trackerFactory;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @var \Moosend\WebsiteTracking\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
     * @param \Moosend\TrackerFactory $trackerFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Moosend\WebsiteTracking\Helper\Data $helper
     */
    public function __construct(ScopeConfigInterface $configInterface, Image $imageHelper, Data $helper, TrackerFactory $trackerFactory)
    {
        $this->configInterface = $configInterface;
        $this->imageHelper = $imageHelper;
        $this->trackerFactory = $trackerFactory;
        $this->helper = $helper;
    }

	/**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$siteId = $this->configInterface->getValue('mootracker_site_id_section/mootracker_group_site_id/mootracker_site_id');

        if (empty($siteId)) {
            return;
        }

    	$product = $observer->getEvent()->getQuoteItem()->getProduct();

    	$tracker = $this->trackerFactory->create($siteId);

        $price = floatval($product->getFinalPrice());
        $id = $product->getId();
        $name = $product->getName();
        $url = $product->getProductUrl();
        $quantity = intval($product->getQty()) ?: 1;
        $total = $price * $quantity;
        $image = $this->imageHelper->init($product, 'product_page_image_large')->getUrl();

        $productConfigurations = $product->getTypeInstance(true)->getOrderOptions($product);
        $props = $this->helper->formatProductOptions($productConfigurations);
        $props['itemCategory'] = $this->helper->getProductCategoryNames($product->getCategoryIds());

        $tracker->removeFromOrder($id, $price, $url, $total, $name, $image, $props, true);
    }
}
