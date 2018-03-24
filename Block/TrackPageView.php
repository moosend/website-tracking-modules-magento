<?php
/**
 * Copyright © 2017 Moosend. All rights reserved.
 */
namespace Moosend\WebsiteTracking\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\App\Request\Http;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Customer\Model\SessionFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Moosend\WebsiteTracking\Helper\Data;
use \Moosend\TrackerFactory;
use \Moosend\CookieNames;

/**
 * Moosend Website Tracking Page Block
 */
class TrackPageView extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Moosend\TrackerFactory
     */
    protected $trackerFactory;

    /**
     * @var \Moosend\WebsiteTracking\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Moosend\TrackerFactory $trackerFactory
     * @param \Moosend\WebsiteTracking\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Http $request,
        ProductFactory $productFactory,
        SessionFactory $sessionFactory,
        StoreManagerInterface $storeManager,
        TrackerFactory $trackerFactory,
        Data $helper,
        array $data = array()
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->sessionFactory = $sessionFactory;
        $this->storeManager = $storeManager;
        $this->trackerFactory = $trackerFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return string
     */
    protected function getCurrentProductId()
    {
        return $this->registry->registry('current_product')->getId();
    }

    /**
     *
     * @return boolean
     */
    public function isProductPage()
    {
        return $this->request->getFullActionName() === 'catalog_product_view';
    }

    /**
     *
     * @return string
     */
    public function getWebsiteId()
    {
        $website_id = $this->_scopeConfig->getValue('mootracker_site_id_section/mootracker_group_site_id/mootracker_site_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $website_id;
    }

    /**
     *
     * @return array
     */
    public function getCurrentProduct()
    {
        $product_id = $this->getCurrentProductId();
        $product = $this->productFactory->create()->load($product_id);
        $store = $this->storeManager->getStore();
        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

        $properties = array(
            array(
                'product' => array(
                    'itemCode' => $product->getId(),
                    'itemPrice' => $product->getPriceInfo()->getPrice('final_price')->getValue(),
                    'itemUrl' => $product->getProductUrl(),
                    'itemQuantity' => $product->getQty() ?: 1,
                    'itemTotalPrice' => $product->getPriceInfo()->getPrice('final_price')->getValue() * ((float)$product->getQty() ?: 1),
                    'itemImage' => $productImageUrl,
                    'itemName' => $product->getName(),
                    'itemDescription' => $product->getName(),
                    'itemCategory' => $this->helper->getProductCategoryNames($product->getCategoryIds()),
                    'itemStockStatus' => $product->isInStock()
                )
            )
        );

        return $properties;
    }

    /**
     *
     * @return mixed
     */
    public function getCookieNames()
    {
        return array(
            'website_id'    =>  CookieNames::SITE_ID,
            'user_id'   =>  CookieNames::USER_ID,
            'email' =>  CookieNames::USER_EMAIL
        );
    }

    /**
     *
     * @return mixed
     */
    public function getUserData()
    {
        $customerSession = $this->sessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            return array(
                'name'  =>  $customerSession->getCustomer()->getName(),
                'email' =>  $customerSession->getCustomer()->getEmail()
            );
        }
        return null;
    }

    /**
     *
     * @return void
     */
    public function initializeTracker()
    {
        $website_id = $this->_scopeConfig->getValue('mootracker_site_id_section/mootracker_group_site_id/mootracker_site_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (empty($website_id)) {
            return;
        }

        $tracker = $this->trackerFactory->create($website_id);

        $tracker->init($website_id);
    }

    /**
     *
     * @return array
     */
    public function getTrackingdata()
    {
        return array(
            "current_website_id"    =>  $this->getWebsiteId(),
            "is_product_page"   =>  $this->isProductPage(),
            "current_product"   =>  $this->isProductPage() ? $this->getCurrentProduct() : false,
            "cookie_names"  =>  $this->getCookieNames(),
            "order_info" =>  $this->getOrderInfo(),
            "user_data" =>  $this->getUserData(),
        );
    }
}
