<?php
/**
 * @package      Crowdfunding
 * @subpackage   Category
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Category;

use Prism\Domain;
use Prism\Domain\Entity;
use Crowdfunding\Category\Gateway\CategoryGateway;

/**
 * This class provides functionality for managing a category.
 *
 * @package      Crowdfunding
 * @subpackage   Category
 */
class Mapper extends Domain\Mapper
{
    /**
     * @var CategoryGateway
     */
    protected $gateway;

    /**
     * Initialize the object.
     *
     * @param CategoryGateway $gateway
     */
    public function __construct(CategoryGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Return a gateway object.
     *
     * @return CategoryGateway
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    protected function createObject()
    {
        return new Category;
    }

    public function populate(Entity $object, array $data)
    {
        $object->bind($data);

        return $object;
    }

    protected function insertObject(Entity $object)
    {
    }

    protected function updateObject(Entity $object)
    {
    }

    protected function deleteObject(Entity $object)
    {
    }
}
