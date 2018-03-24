<?php namespace Moosend\WebsiteTracking\Test\Unit\Helper;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Moosend\WebsiteTracking\Helper\Data;
use \Magento\Catalog\Model\CategoryFactory;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Data | \PHPUnit_Framework_MockObject_MockObject
     */
    private $dataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryFactoryMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->categoryFactoryMock = $this->getMockBuilder(CategoryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'create'))
            ->getMock();

        $this->dataMock = $objectManager->getObject(
            Data::class,
            array(
                'categoryFactory'	=>	$this->categoryFactoryMock
            )
        );
    }

    public function testFormatProductOptionsWhenEmptyArray()
    {
        $this->assertEquals(array(), $this->dataMock->formatProductOptions(array()));
    }

    public function testFormatProductOptionsWhenNonEmptyArray()
    {
        $formatProductOptionsProperty = array(
            'options'	=>	array(
                array(
                    'label'	=>	'Product Label',
                    'value'	=>	'Product Value'
                )
            ),
            'attributes_info'	=>	array(
                array(
                    'label'	=>	'Attribute Label',
                    'value'	=>	'Attribute Value'
                )
            )
        );
        $expectedFormattedProperties = array(
            'Product Label'	=>	'Product Value',
            'Attribute Label'	=>	'Attribute Value'
        );
        $this->assertEquals($expectedFormattedProperties, $this->dataMock->formatProductOptions($formatProductOptionsProperty));

        $formatProductOptionsProperty['options'] = array();
        $formatProductOptionsProperty['attributes_info'] = array();


        $this->assertEquals(array(), $this->dataMock->formatProductOptions($formatProductOptionsProperty));
    }

    public function testGetProductCategoryNames()
    {
        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getName'))
            ->getMock();
        $categoryMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryFactoryMock->expects($this->any())->method('create')->will($this->returnValue($categoryMock));

        $categoryMock->expects($this->any())->method('getName')->will($this->returnValue('Category 1'));

        $expectedCategoryNames = 'Category 1, Category 1';

        $this->assertEquals($expectedCategoryNames, $this->dataMock->getProductCategoryNames(array(1, 2)));
    }

    public function testGetProductCategoryNamesIfThereAreNoCategories()
    {
        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getName'))
            ->getMock();
        $categoryMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryFactoryMock->expects($this->any())->method('create')->will($this->returnValue($categoryMock));

        $categoryMock->expects($this->any())->method('getName')->will($this->returnValue('Category 1'));

        $expectedCategoryNames = 'Category 1, Category 1';

        $this->assertEquals(null, $this->dataMock->getProductCategoryNames(array()));
    }
}
