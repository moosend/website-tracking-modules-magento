<?php namespace Moosend\WebsiteTracking\Test\Unit\Block;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\View\Element\Template\Context;
use \Moosend\WebsiteTracking\Block\TrackPageView;
use \Magento\Framework\Registry;
use \Magento\Framework\App\Request\Http;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Customer\Model\SessionFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Moosend\trackerFactory;
use \Moosend\WebsiteTracking\Helper\Data;

class TrackPageViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TrackPageView | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackPageViewBlock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $trackerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteTrackingDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dbValue = 'c8f580ff-4c69-4618-a6bc-81900f20d098';


    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'create'))
            ->getMock();
        $this->sessionFactoryMock = $this->getMockBuilder(SessionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $this->trackerFactoryMock = $this->getMockBuilder(TrackerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteTrackingDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->trackPageViewBlock = $objectManager->getObject(
            TrackPageView::class,
            array(
                '_scopeConfig'	=>	$this->scopeConfigMock,
                'context'	=>	$contextMock,
                'registry'	=>	$this->registryMock,
                'request'	=>	$this->requestMock,
                'productFactory'	=>	$this->productFactoryMock,
                'sessionFactory'	=>	$this->sessionFactoryMock,
                'storeManager'	=>	$this->storeManagerMock,
                'trackerFactory'	=>	$this->trackerFactoryMock,
                'helper'	=>	$this->websiteTrackingDataMock
            )
        );
    }

    public function testIsProductPage()
    {
        $catalog_product_view = 'catalog_product_view';
        $this->requestMock->expects($this->once())->method('getFullActionName')->will($this->returnValue($catalog_product_view));
        $this->assertTrue($this->trackPageViewBlock->isProductPage());
    }

    public function testGetWebsiteId()
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn($this->dbValue);
        $this->assertEquals($this->dbValue, $this->trackPageViewBlock->getWebsiteId());
    }

    public function testGetCookieNames()
    {
        $expectedResult = array(
            'website_id'    =>  'MOOSEND_SITE_ID',
            'user_id'   =>  'MOOSEND_USER_ID',
            'email' =>  'USER_EMAIL'
        );
        $this->assertEquals($expectedResult, $this->trackPageViewBlock->getCookieNames());
    }

    public function testgetCurrentProduct()
    {
        $productId = 1;
        $categoryIds = array(1, 2);
        $productImage = '/my_image.png';
        $productUrl = 'https://localhost/some-product';
        $productPrice = 20.00;
        $productQuantity = 2;
        $productName = 'Some Product';

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getId', 'getImage', 'getPriceInfo', 'getName', 'getProductUrl', 'getCategoryIds', 'isInStock', 'getQty'))
            ->getMock();
        $productMock->expects($this->any())->method('load')->will($this->returnSelf());
        $productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $productMock->expects($this->once())->method('getImage')->will($this->returnValue($productImage));
        $productMock->expects($this->any())->method('getName')->will($this->returnValue($productName));
        $productMock->expects($this->once())->method('getProductUrl')->will($this->returnValue($productUrl));
        $productMock->expects($this->any())->method('getQty')->will($this->returnValue($productQuantity));
        $productMock->expects($this->once())->method('getCategoryIds')->will($this->returnValue($categoryIds));
        $productMock->expects($this->once())->method('isInStock')->will($this->returnValue(true));
        $priceInfo = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getPrice'))
            ->getMock();
        $price = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $price->expects($this->any())->method('getValue')->will($this->returnValue($productPrice));
        $priceInfo->expects($this->any())->method('getPrice')->with('final_price')->willReturn($price);
        $productMock->expects($this->any())->method('getPriceInfo')->willReturn($priceInfo);
        $this->registryMock->method('registry')->with('current_product')->willReturn($productMock);
        $this->productFactoryMock->expects($this->any())->method('create')->willReturn($productMock);

        $expectedValue = array(
            array(
                'product'	=>	array(
                    'itemCode'	=>	$productId,
                    'itemPrice' => $productPrice,
                    'itemUrl' => $productUrl,
                    'itemQuantity' => $productQuantity,
                    'itemTotalPrice' => $productQuantity * $productPrice,
                    'itemImage' => 'catalog/product/my_image.png',
                    'itemName' => $productName,
                    'itemDescription' => $productName,
                    'itemCategory' => null,
                    'itemStockStatus' => true
                )
            )
        );

        $this->assertEquals($expectedValue, $this->trackPageViewBlock->getCurrentProduct());
    }

    public function testItShouldReturnNullIfThereIsNoSession()
    {
        $sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->sessionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sessionMock));

        $this->assertNull($this->trackPageViewBlock->getUserData());
    }

    public function testItShouldReturnUserInfoIfSession()
    {
        $customerName = 'John Smith';
        $customerEmail = 'john@example.com';

        $sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerModelMock = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getName', 'getEmail'))
            ->getMock();
        $customerModelMock->expects($this->once())->method('getName')->will($this->returnValue($customerName));
        $customerModelMock->expects($this->once())->method('getEmail')->will($this->returnValue($customerEmail));
        $sessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $sessionMock->expects($this->any())->method('getCustomer')->will($this->returnValue($customerModelMock));

        $this->sessionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sessionMock));

        $expectedValue = array(
            'name'	=>	$customerName,
            'email'	=>	$customerEmail
        );

        $this->assertEquals($expectedValue, $this->trackPageViewBlock->getUserData());
    }

    public function testTrackerShouldNotBeInitializedIfThereIsNoWebsiteId()
    {
        $trackerMock = $this->getMockBuilder(\Moosend\Tracker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->trackerFactoryMock->expects($this->never())->method('create')->will($this->returnValue($trackerMock));
        $this->trackPageViewBlock->initializeTracker();
    }

    public function testTrackerShouldBeInitializedIfThereIsWebsiteId()
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn($this->dbValue);
        $trackerMock = $this->getMockBuilder(\Moosend\Tracker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->trackerFactoryMock->expects($this->once())->method('create')->will($this->returnValue($trackerMock));
        $this->trackPageViewBlock->initializeTracker();
    }

    public function testGetTrackingData()
    {
        $customerName = 'John Smith';

        $sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerModelMock = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerModelMock->expects($this->once())->method('getName')->will($this->returnValue($customerName));
        $sessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $sessionMock->expects($this->any())->method('getCustomer')->will($this->returnValue($customerModelMock));

        $this->scopeConfigMock->method('getValue')
            ->willReturn($this->dbValue);
        $this->sessionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($sessionMock));

        $expectedResult = array(
            'current_website_id'	=>	$this->dbValue,
            'is_product_page'	=>	false,
            'current_product'	=>	false,
            'cookie_names'	=>	array(
                'website_id' => 'MOOSEND_SITE_ID',
                'user_id' => 'MOOSEND_USER_ID',
                'email' => 'USER_EMAIL'
            ),
            'order_info'	=>	null,
            'user_data'	=>	array(
                'name' => 'John Smith',
                'email' => null
            )
        );

        $this->assertEquals($expectedResult, $this->trackPageViewBlock->getTrackingData());
    }
}
