<?php namespace Moosend\WebsiteTracking\Test\Unit\Model\System\Message;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Moosend\WebsiteTracking\Model\System\Message\EmptyWebsiteId;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\UrlInterface;

class EmptyWebsiteIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmptyWebsiteId | \PHPUnit_Framework_MockObject_MockObject
     */
    private $emptyWebsiteIdMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dbValue = 'c8f580ff-4c69-4618-a6bc-81900f20d098';

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emptyWebsiteIdMock = $objectManager->getObject(
            EmptyWebsiteId::class,
            array(
                'scopeConfig'	=>	$this->scopeConfigMock,
                'urlBuilder'	=>	$this->urlBuilderMock
            )
        );
    }

    public function testGetIdentify()
    {
        $this->assertEquals('moosend_empty_website_id', $this->emptyWebsiteIdMock->getIdentity());
    }

    public function testItShouldDisplayWhenWebsiteIdIsEmpty()
    {
        $this->assertTrue($this->emptyWebsiteIdMock->isDisplayed());
    }

    public function testItShouldNotDisplayWhenWebsiteIdIsNotEmpty()
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn($this->dbValue);
        $this->assertFalse($this->emptyWebsiteIdMock->isDisplayed());
    }

    public function testGetText()
    {
        $messageStart = 'In order to make it work, Moosend Email Marketing requires a Website ID.';
        $this->assertStringStartsWith($messageStart, (string)$this->emptyWebsiteIdMock->getText());
    }

    public function testGetSeverity()
    {
        $this->assertEquals(1, $this->emptyWebsiteIdMock->getSeverity());
    }
}
