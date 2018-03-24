<?php
/**
 * Copyright © 2017 Moosend. All rights reserved.
 */
namespace Moosend\WebsiteTracking\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Helper\Image;
use \Magento\Framework\View\LayoutInterface;
use \Moosend\WebsiteTracking\Helper\Data;
use \Moosend\TrackerFactory;

class CheckoutOnepageControllerSuccessObserver implements ObserverInterface
{
    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $configInterface;

    /**
     *
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     *
     * @var \Moosend\WebsiteTracking\Helper\Data
     */
    private $helper;

    /**
     *
     * @var \Moosend\TrackerFactory
     */
    private $trackerFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Moosend\TrackerFactory $trackerFactory
     * @param \Moosend\WebsiteTracking\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        OrderFactory $orderFactory,
        ScopeConfigInterface $configInterface,
        Image $imageHelper,
        LayoutInterface $layout,
        TrackerFactory $trackerFactory,
        Data $helper
    ) {
        $this->orderFactory = $orderFactory;
        $this->configInterface = $configInterface;
        $this->imageHelper = $imageHelper;
        $this->layout = $layout;
        $this->trackerFactory = $trackerFactory;
        $this->helper = $helper;
    }

    /**
     *
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

        $orderIds = $observer->getEvent()->getOrderIds();

        $tracker = $this->trackerFactory->create($siteId);

        if (count($orderIds)) {
            $orderId = $orderIds[0];
            $order = $this->orderFactory->create()->load($orderId);
            $email = $order->getCustomerEmail();
            $name = $order->getCustomerName();

            $orderTotal = $order->getGrandTotal();
            $trackerOrder = $tracker->createOrder($orderTotal);
            $this->fillTrackerOrderWithProducts($trackerOrder, $order);

            $block = $this->layout->getBlock('moosend_websitetracking');
            $block->setOrderInfo(
                array(
                    'email' =>  $email,
                    'name'  =>  $name,
                    'order_data'    =>  $trackerOrder->toArray(),
                    'order_total_price' =>  $orderTotal
                )
            );
        }
    }

    private function fillTrackerOrderWithProducts($trackerOrder, $order)
    {
        $orderProducts = $order->getAllVisibleItems();

        foreach ($orderProducts as $orderProduct) {
            $this->addProductToOrder($orderProduct, $trackerOrder);
        }

        return $trackerOrder;
    }

    /**
     *
     * @param @orderProduct
     * @param $trackerOrder
     * @return void
     */
    private function addProductToOrder($orderProduct, $trackerOrder)
    {
        $product = $orderProduct->getProduct();
        $price = $product->getPriceInfo()->getPrice('final_price')->getValue();
        $id = $product->getId();
        $name = $product->getName();
        $url = $product->getProductUrl();
        $quantity = intval($orderProduct->getQtyToShip());
        $total = $price * $quantity;
        $image = $this->imageHelper->init($product, 'product_page_image_large')->getUrl();

        $productOptions = $orderProduct->getProductOptions();
        $props = $this->helper->formatProductOptions($productOptions);
        $props['itemCategory'] = $this->helper->getProductCategoryNames($product->getCategoryIds());

        $trackerOrder->addProduct($id, $price, $url, $quantity, $total, $name, $image, $props, true);
    }
}
