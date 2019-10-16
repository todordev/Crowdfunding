<?php
/**
 * @package     Crowdfunding\UnitTest
 * @subpackage  Projects
 * @author      Todor Iliev
 * @copyright   Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Project;

/**
 * Test class for Crowdfunding\UnitTest.
 *
 * @package     Crowdfunding\UnitTest
 * @subpackage  Projects
 */
class ProjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Project
     */
    protected $object;

    /**
     * Test the getGoal method.
     *
     * @return  void
     * @covers  Project::getGoal
     */
    public function testGetGoal()
    {
        $this->assertEquals(
            1000.000,
            $this->object->getGoal()
        );
    }

    /**
     * Test the getFunded method.
     *
     * @return  void
     * @covers  Project::getFunded
     */
    public function testGetFunded()
    {
        $this->assertEquals(
            500.000,
            $this->object->getFunded()
        );
    }

    /**
     * Test the addFunds method.
     *
     * @return  void
     * @covers  Project::addFunds
     */
    public function testAddFunds()
    {
        $this->object->addFunds(100);

        $this->assertEquals(
            600.000,
            $this->object->getFunded()
        );
    }

    /**
     * Test the removeFunds method.
     *
     * @return  void
     * @covers  Project::removeFunds
     */
    public function testRemoveFunds()
    {
        $this->object->removeFunds(100);

        $this->assertEquals(
            400.000,
            $this->object->getFunded()
        );
    }

    /**
     * Test the getFundedPercent method.
     *
     * @return  void
     * @covers  Project::getFundedPercent
     */
    public function testGetFundedPercent()
    {
        $this->object->addFunds(100);

        $this->assertEquals(
            60,
            $this->object->getFundedPercent()
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

        $jsonData = file_get_contents(PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER.'project.json');
        $data     = json_decode($jsonData, true);

        $this->object = new Project();
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
