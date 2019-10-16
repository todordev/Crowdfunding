<?php
/**
 * @package      Crowdfunding
 * @subpackage   Currencies
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Currency;

use Prism\Domain;
use Prism\Domain\Entity;
use Crowdfunding\Currency\Gateway\CurrencyGateway;

/**
 * This class provides functionality that manage the persistence of the currency objects.
 *
 * @package      Crowdfunding
 * @subpackage   Currencies
 */
class Mapper extends Domain\Mapper
{
    /**
     * @var CurrencyGateway
     */
    protected $gateway;

    /**
     * Initialize the object.
     *
     * <code>
     * $gateway = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $mapper  = new Crowdfunding\Currency\Mapper($gateway);
     * </code>
     *
     * @param CurrencyGateway $gateway
     */
    public function __construct(CurrencyGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @return CurrencyGateway
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Populate an object.
     *
     * <code>
     * $currencyId = 1;
     *
     * $gateway  = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $data     = $gateway->fetchById($currencyId);
     *
     * $mapper   = new Crowdfunding\Currency\Mapper($gateway);
     * $currency = $mapper->populate(new Crowdfunding\Currency\Currency, $data);
     * </code>
     *
     * @param Entity $object
     * @param array  $data
     *
     * @return Entity
     */
    public function populate(Entity $object, array $data)
    {
        $object->bind($data);

        return $object;
    }

    protected function createObject()
    {
        return new Currency;
    }

    protected function insertObject(Entity $object)
    {
        // @todo Do insertObject method in the currency mapper.
    }

    protected function updateObject(Entity $object)
    {
        // @todo Do updateObject method in the currency mapper.
    }

    protected function deleteObject(Entity $object)
    {
        // @todo Do deleteObject method in the currency mapper.
    }
}
