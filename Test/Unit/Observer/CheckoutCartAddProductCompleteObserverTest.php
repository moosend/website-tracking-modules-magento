<?php namespace Moosend\WebsiteTracking\Test\Unit\Observer;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Moosend\WebsiteTracking\Observer\CheckoutCartAddProductCompleteObserver;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Helper\Image;
use \Moosend\WebsiteTracking\Helper\Data;
use \Moosend\TrackerFactory;

class CheckoutCartAddProductCompleteObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Moosend\WebsiteTracking\Observer\CheckoutCartAddProductCompleteObserver
     */
    protected $observer;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelperMock;

    /**
     * @var \Moosend\WebsiteTracking\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Moosend\TrackerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackerFactoryMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbValue = 'c8f580ff-4c69-4618-a6bc-81900f20d098';

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageHelperMock = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->trackerFactoryMock = $this->getMockBuilder(TrackerFactory::class)
               ->disableOriginalConstructor()
               ->getMock();
        $this->productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $objectManager->getObject(
            CheckoutCartAddProductCompleteObserver::class,
            array(
                'configInterface'   =>  $this->scopeConfigMock,
                'imageHelper'   =>  $this->imageHelperMock,
                'helper'    =>  $this->helperMock,
                'trackerFactory'    =>  $this->trackerFactoryMock
            )
        );
    }

    public function testItShouldNotTrackAddProductToCart()
    {
        $this->assertNull($this->observer->execute($this->getObserverMock()));
    }

    public function testItShouldTrackAddProductToCart()
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn($this->dbValue);
        $productId = 1;
        $price = 20.00;
        $productUrl = 'https://localhost/some-product';
        $productName = 'Some Product';
        $productImage = 'https://localhost/some-product.png';
        $quantiy = 2;
        $total = $price * $quantiy;

        $this->productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getId', 'getImage', 'getFinalPrice', 'getName', 'getProductUrl', 'getTypeInstance', 'getCategoryIds', 'isInStock', 'getQty'))
            ->getMock();
        $productImageMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $trackerMock = $this->getMockBuilder(\Moosend\Tracker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstanceMock = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->trackerFactoryMock->expects($this->once())->method('create')->with($this->dbValue)->will($this->returnValue($trackerMock));

        $this->imageHelperMock->expects($this->once())->method('init')->with($this->productMock, 'product_page_image_large')->will($this->returnValue($productImageMock));
        $productImageMock->expects($this->once())->method('getUrl')->will($this->returnValue($productImage));
        $this->productMock->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $this->productMock->expects($this->once())->method('getFinalPrice')->will($this->returnValue($price));
        $this->productMock->expects($this->once())->method('getProductUrl')->will($this->returnValue($productUrl));
        $this->productMock->expects($this->once())->method('getQty')->will($this->returnValue($quantiy));
        $this->productMock->expects($this->once())->method('getName')->will($this->returnValue($productName));
        $this->productMock->expects($this->once())->method('getTypeInstance')->with(true)->willReturn($typeInstanceMock);
        $this->productMock->expects($this->once())->method('getCategoryIds')->will($this->returnValue(array(1, 2)));
        $trackerMock->expects($this->once())->method('addToOrder')->with($productId, $price, $productUrl, $quantiy, $total, $productName, $productImage);
        $this->observer->execute($this->getObserverMock(true));
    }

    public function getObserverMock($hasSiteId = false)
    {
        $eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getData'))->getMock();

        if ($hasSiteId) {
            $eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
            $eventMock->expects($this->once())->method('getData')->will($this->returnValue(array('product'  =>  $this->productMock)));
        }

        return $eventObserverMock;
    }
}
