<?php
/**
 * @package     Crowdfunding\UnitTest
 * @subpackage  Currencies
 * @author      Todor Iliev
 * @copyright   Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Currency\Currency;

/**
 * Test class for Crowdfunding\UnitTest.
 *
 * @package     Crowdfunding\UnitTest
 * @subpackage  Projects
 */
class CurrencyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Currency
     */
    protected $object;

    /**
     * Test the getId method.
     *
     * @return  void
     * @covers  Currency::getId
     */
    public function testGetId()
    {
        $this->assertEquals(
            1,
            $this->object->getId()
        );
    }

    /**
    * Test the getTitle method.
    *
    * @return  void
    * @covers  Currency::getTitle
    */
    public function testGetTitle()
    {
        $this->assertEquals(
            'EURO',
            $this->object->getTitle()
        );
    }

    /**
     * Test the getCode method.
     *
     * @return  void
     * @covers  Currency::getCode
     */
    public function testGetCode()
    {
        $this->assertEquals(
            'EUR',
            $this->object->getCode()
        );
    }

    /**
     * Test the getSymbol method.
     *
     * @return  void
     * @covers  Currency::getSymbol
     */
    public function testGetSymbol()
    {
        $this->assertEquals(
            '€',
            $this->object->getSymbol()
        );
    }

    /**
     * Test the getPosition method.
     *
     * @return  void
     * @covers  Currency::getPosition
     */
    public function testGetPosition()
    {
        $this->assertEquals(
            Currency::SYMBOL_BEFORE,
            $this->object->getPosition()
        );
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

        $jsonData = file_get_contents(PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER.'currency.json');
        $data     = json_decode($jsonData, true);

        $this->object = new Currency();
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
