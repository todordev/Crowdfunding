<?php
/**
 * @package     Crowdfunding\UnitTest
 * @subpackage  Currencies
 * @author      Todor Iliev
 * @copyright   Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Currency\Mapper;
use Crowdfunding\Currency\Currency;
use Crowdfunding\Currency\Currencies;
use Crowdfunding\Currency\Repository;
use CrowdfundingMock\Currency\Gateway\JoomlaGateway;

/**
 * Test class for Crowdfunding\UnitTest.
 *
 * @package     Crowdfunding\UnitTest
 * @subpackage  Projects
 *
 * @todo complete the tests.
 */
class CurrenciesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Currencies
     */
    protected $collection;

    /**
     * Raw data of the currencies.
     *
     * @var array
     */
    protected $data;

    /**
     * Test method Find.
     *
     * @return  void
     * @covers  Currencies::find()
     */
    /*public function testFind()
    {
        $result   = $this->collection->find(1, 'id');

        $this->assertInstanceOf(Currency::class, $result);
        $this->assertEquals(1, $result->getId());
    }*/

    /**
     * Test method toOptions.
     *
     * @return  void
     * @covers  Currencies::toOptions()
     */
    /*public function testToOptions()
    {
        $expected = array(
            array(
                'value' => 1,
                'text'  => 'EURO'
            ),
            array(
                'value' => 2,
                'text'  => 'BGN'
            ),
        );

        $result   = $this->collection->toOptions('id', 'title');

        $this->assertEquals($expected, $result);
    }*/

    /**
     * Test the instance of the object.
     *
     * @return  void
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Currencies::class, $this->collection);
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

        $jsonData   = file_get_contents(PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER.'currencies.json');
        $this->data = json_decode($jsonData, true);

        $gateway    = new JoomlaGateway(JFactory::getDbo());
        $repository = new Repository($gateway);
        $repository->setMapper(new Mapper($gateway));

        $databaseRequest = new \Prism\Database\Request\Request;
        $databaseRequest->addSpecificCondition('ids', new \Prism\Database\Request\Condition(['column' => 'id', 'value' => [1, 2]]));

        $this->collection = $repository->fetchCollection($databaseRequest);
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
        unset($this->collection, $this->data);
        parent::tearDown();
    }
}
