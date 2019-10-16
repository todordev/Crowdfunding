<?php
/**
 * @package     Crowdfunding\UnitTest
 * @subpackage  Payments\Session
 * @author      Todor Iliev
 * @copyright   Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Payment\Session;

/**
 * Test class for Crowdfunding\UnitTest.
 *
 * @package     Crowdfunding\UnitTest
 * @subpackage  Payments\Session
 */
class ServiceDataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Session\ServiceData
     */
    protected $object;

    /**
     * @var Session\Mapper
     */
    protected $mapper;

    /**
     * Test the getToken method.
     *
     * @return  void
     * @covers  ServiceData::getToken
     */
    public function testGetToken()
    {
        $this->assertEquals(
            'T123456789',
            $this->object->getToken()
        );
    }

    /**
     * Test the setToken method.
     *
     * @return  void
     * @covers  ServiceData::setToken
     */
    public function testSetToken()
    {
        $token = 'T987654321';
        
        $this->object->setToken($token);

        $this->assertEquals(
            $token,
            $this->object->getToken()
        );
    }

    /**
     * Test the getOrderId method.
     *
     * @return  void
     * @covers  ServiceData::getOrderId
     */
    public function testGetOrderId()
    {
        $this->assertEquals(
            'OID_1234',
            $this->object->getOrderId()
        );
    }

    /**
     * Test the setOrderId method.
     *
     * @return  void
     * @covers  ServiceData::setOrderId
     */
    public function testSetOrderId()
    {
        $this->object->setOrderId(15);

        $this->assertEquals(
            15,
            $this->object->getOrderId()
        );
    }

    /**
     * Test the getData method.
     *
     * @return  void
     * @covers  ServiceData::getData
     */
    public function testGetData()
    {
        $this->assertArrayHasKey(
            'preapproval_key',
            $this->object->getData()
        );
    }

    /**
     * Test the setData method.
     *
     * @return  void
     * @covers  ServiceData::setData
     */
    public function testSetData()
    {
        $serviceData = [
            'preapproval_key' => 'PA_KEY_654321'
        ];

        $this->object->setData($serviceData);

        $this->assertEquals(
            $serviceData,
            $this->object->getData()
        );
    }

    /**
     * Test setting of data by method data($key, $value).
     *
     * @return  void
     * @covers  ServiceData::data
     */
    public function testData()
    {
        $paKey = 'PA_KEY_654321';

        $this->object->data('preapproval_key', $paKey);

        $this->assertEquals(
            $paKey,
            $this->object->data('preapproval_key')
        );
    }

    /**
     * Test getting data by method data($key).
     *
     * @return  void
     */
    public function testDataGetValue()
    {
        $this->assertEquals(
            'PA_KEY_123456',
            $this->object->data('preapproval_key')
        );
    }

    /**
     * Test getting data by method data($key).
     * It gets a data by a key that does not exists.
     * It should return NULL.
     *
     * @return  void
     */
    public function testDataGetNotExistingKey()
    {
        $this->assertNull($this->object->data('language'));
    }

    /**
     * Test the parameter 'data' passed by method setData.
     *
     * @return  void
     */
    public function testExceptionParameterDataMethodSetServiceData()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->setData(array());
    }

    /**
     * Test the parameter 'orderId' passed by method setOrderId.
     *
     * @return  void
     */
    public function testExceptionParameterOrderIdMethodSetOrderId()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->setOrderId('');
    }

    /**
     * Test the parameter 'token' passed by method setUniqueKey.
     *
     * @return  void
     */
    public function testExceptionParameterTokenMethodSetUniqueKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->setToken('');
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     */
    protected function setUp()
    {
        parent::setUp();

        $jsonData = file_get_contents(PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER.'paymentgateway.json');
        $data     = json_decode($jsonData, true);

        $this->object = new Session\ServiceData;
        $this->object->bind($data);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     *
     * @see     PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->object);
        parent::tearDown();
    }
}
