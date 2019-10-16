<?php
/**
 * @package      Crowdfunding
 * @subpackage   Reward
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Reward;

use Prism\Domain;
use Prism\Constants;
use Crowdfunding\Date;
use Prism\Domain\Entity;
use Prism\Utilities\MathHelper;
use Prism\Validator\Date as DateValidator;
use Crowdfunding\Reward\Gateway\RewardGateway;

/**
 * This class provides functionality for managing a challenge.
 *
 * @package      Crowdfunding
 * @subpackage   Reward
 */
class Mapper extends Domain\Mapper
{
    /**
     * @var RewardGateway
     */
    protected $gateway;

    /**
     * Initialize the object.
     *
     * @param RewardGateway $gateway
     */
    public function __construct(RewardGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Return a gateway object.
     *
     * @return RewardGateway
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    protected function createObject()
    {
        return new Reward;
    }

    /**
     * @param Entity $object
     * @param array  $data
     *
     * @return Reward
     */
    public function populate(Entity $object, array $data)
    {
        /** @var Reward $object */
        $object->bind($data);

        return $object;
    }

    protected function insertObject(Entity $object)
    {
        $this->gateway->insertObject($object);
    }

    protected function updateObject(Entity $object)
    {
        $this->gateway->updateObject($object);
    }

    protected function deleteObject(Entity $object)
    {
    }
}
