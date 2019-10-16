<?php
/**
 * @package     Crowdfunding\UnitTest
 * @subpackage  Payments\Session
 * @author      Todor Iliev
 * @copyright   Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Payment\Session\Session;
use Crowdfunding\Payment\Session\Mapper;
use Crowdfunding\Payment\Session\ServiceData;
use Crowdfunding\Payment\Session\Gateway\JoomlaGateway;

/**
 * Test class for Crowdfunding\UnitTest.
 *
 * @package     Crowdfunding\UnitTest
 * @subpackage  Payments\Session
 */
class SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Session
     */
    protected $object;

    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * Test the getId method.
     *
     * @return  void
     * @covers  Session::getId
     */
    public function testGetId()
    {
        $this->assertEquals(
            2,
            $this->object->getId()
        );
    }

    /**
     * Test the getProjectId method.
     *
     * @return  void
     * @covers  Session::getProjectId
     */
    public function testGetProjectId()
    {
        $this->assertEquals(
            4,
            $this->object->getProjectId()
        );
    }

    /**
     * Test the setProjectId method.
     *
     * @return  void
     * @covers  Session::setProjectId
     */
    public function testSetProjectId()
    {
        $this->object->setProjectId(5);

        $this->assertEquals(
            5,
            $this->object->getProjectId()
        );
    }

    /**
     * Test the getRewardId method.
     *
     * @return  void
     * @covers  Session::getRewardId
     */
    public function testGetRewardId()
    {
        $this->assertEquals(
            9,
            $this->object->getRewardId()
        );
    }

    /**
     * Test the setRewardId method.
     *
     * @return  void
     * @covers  Session::setRewardId
     */
    public function testSetRewardId()
    {
        $this->object->setRewardId(10);

        $this->assertEquals(
            10,
            $this->object->getRewardId()
        );
    }

    /**
     * Test the getSessionId method.
     *
     * @return  void
     * @covers  Session::getSessionId
     */
    public function testGetSessionId()
    {
        $this->assertEquals(
            'ec81d124910a7a49130adf02585c0a2e',
            $this->object->getSessionId()
        );
    }

    /**
     * Test the setSessionId method.
     *
     * @return  void
     * @covers  Session::setSessionId
     */
    public function testSetSessionId()
    {
        $this->object->setSessionId('ec81d124910a7a49130adf02585c0a3e');

        $this->assertEquals(
            'ec81d124910a7a49130adf02585c0a3e',
            $this->object->getSessionId()
        );
    }

    /**
     * Test the getIntentionId method.
     *
     * @return  void
     * @covers  Session::getIntentionId
     */
    public function testGetIntentionId()
    {
        $this->assertEquals(
            34,
            $this->object->getIntentionId()
        );
    }

    /**
     * Test the setIntentionId method.
     *
     * @return  void
     * @covers  Session::setIntentionId
     */
    public function testSetIntentionId()
    {
        $this->object->setIntentionId(35);

        $this->assertEquals(
            35,
            $this->object->getIntentionId()
        );
    }

    /**
    * Test the getUserId method.
    *
    * @return  void
    * @covers  Session::getUserId
    */
    public function testGetUserId()
    {
        $this->assertEquals(
            300,
            $this->object->getUserId()
        );
    }

    /**
     * Test the setUserId method.
     *
     * @return  void
     * @covers  Session::setUserId
     */
    public function testSetUserId()
    {
        $this->object->setUserId(301);

        $this->assertEquals(
            301,
            $this->object->getUserId()
        );
    }

    /**
     * Test the getAnonymousUserId method.
     *
     * @return  void
     * @covers  Session::getAnonymousUserId
     */
    public function testGetAnonymousUserId()
    {
        $this->assertEquals(
            '',
            $this->object->getAnonymousUserId()
        );
    }

    /**
     * Test the setAnonymousUserId method.
     *
     * @return  void
     * @covers  Session::setAnonymousUserId
     */
    public function testSetAnonymousUserId()
    {
        $this->object->setAnonymousUserId('anon12345');

        $this->assertEquals(
            'anon12345',
            $this->object->getAnonymousUserId()
        );
    }

    /**
     * Test the getRecordDate method.
     *
     * @return  void
     * @covers  Session::getRecordDate
     */
    public function testGetRecordDate()
    {
        $this->assertEquals(
            '2017-06-15 06:12:37',
            $this->object->getRecordDate()
        );
    }

    /**
     * Test the getServices method.
     *
     * @return  void
     * @covers  Session::getServices
     */
    public function testGetServices()
    {
        $this->assertArrayHasKey(
            'paypal',
            $this->object->getServices()
        );
    }

    /**
     * Test the service method.
     *
     * @return  void
     * @covers  Session::service
     */
    public function testService()
    {
        $this->assertInstanceOf(
            ServiceData::class,
            $this->object->service('paypal')
        );
    }

    /**
     * Test Gateway with new payment gateway data.
     *
     * @return  void
     */
    public function testNewGateway()
    {
        $gatewayAlias = 'generic';

        $this->assertInstanceOf(
            ServiceData::class,
            $this->object->service($gatewayAlias)
        );

        $this->assertEquals(
            $gatewayAlias,
            $this->object->service($gatewayAlias)->getAlias()
        );
    }

    /**
     * Test the setRecordDate method.
     *
     * @return  void
     * @covers  Session::setRecordDate
     */
    public function testSetRecordDate()
    {
        $this->object->setRecordDate('2017-06-15 00:00:00');

        $this->assertEquals(
            '2017-06-15 00:00:00',
            $this->object->getRecordDate()
        );
    }

    /**
     * Test the parameter 'gateway' passed by method gateway().
     *
     * @return  void
     */
    public function testExceptionParameterGatewayMethodGateway()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->object->service('');
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

        $jsonData = file_get_contents(PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER.'paymentsession.json');
        $data     = json_decode($jsonData, true);

        $sessionGateway = new JoomlaGateway(JFactory::getDbo());
        $this->mapper   = new Mapper($sessionGateway);

        $this->object   = $this->mapper->create($data);
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
