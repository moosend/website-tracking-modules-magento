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

class CheckoutCartAddProductCompleteObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $configInterface;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @var \Moosend\WebsiteTracking\Helper\Data
     */
    protected $helper;
    /**
     * @var \Moosend\TrackerFactory
     */
    protected $trackerFactory;

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
        $this->helper = $helper;
        $this->trackerFactory = $trackerFactory;
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

        $eventData = $observer->getEvent()->getData();
        $product = $eventData['product'];

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

        try {
            $tracker->addToOrder($id, $price, $url, $quantity, $total, $name, $image, $props);
        } catch (\Exception $err) {
            trigger_error('Could not track events for MooTracker', E_USER_WARNING);
        }
    }
}
