<?php
/**
 * @package      Crowdfunding
 * @subpackage   Currencies
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Currency;

use Prism\Database\Request\Request;
use Prism\Domain;
use Crowdfunding\Currency\Gateway\CurrencyGateway;

/**
 * This class provides a glue between persistence layer and currency object.
 *
 * @package      Crowdfunding
 * @subpackage   Currencies
 */
class Repository extends Domain\Repository implements Domain\CollectionFetcher
{
    /**
     * @var CurrencyGateway
     */
    protected $gateway;

    /**
     * Collection object.
     *
     * @var Domain\Collection
     */
    protected $collection;

    /**
     * Initialize the object.
     *
     * @param Mapper $mapper
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper  = $mapper;
        $this->gateway = $mapper->getGateway();
    }

    /**
     * Load the data from database and return an entity.
     *
     * <code>
     * $currencyId  = 1;
     *
     * $gateway     = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $repository  = new Crowdfunding\Currency\Repository($gateway);
     * $currency    = $repository->findById($currencyId);
     * </code>
     *
     * @param int $id
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return Domain\Entity
     */
    public function fetchById($id, Request $request = null)
    {
        if (!$id) {
            throw new \InvalidArgumentException('There is no ID.');
        }

        $data = $this->gateway->fetchById($id, $request);

        return $this->mapper->create($data);
    }

    /**
     * Load the data from database by conditions and return an entity.
     *
     * <code>
     * $request = new Prism\Database\Request\Request;
     * $request->addCondition(new Prism\Database\Request\Condition(['column' => 'code', 'value' => 'USD']));
     *
     * $gateway     = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $repository  = new Crowdfunding\Currency\Repository($gateway);
     * $currency    = $repository->fetch($request);
     * </code>
     *
     * @param Request $request
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return Domain\Entity
     */
    public function fetch(Request $request)
    {
        if (!$request) {
            throw new \UnexpectedValueException('There are no conditions that the system should use to fetch data.');
        }

        $data = $this->gateway->fetch($request);

        return $this->mapper->create($data);
    }

    /**
     * Load the data from database and return a collection.
     *
     * <code>
     * $condition = new Prism\Database\Request\Condition([
     *    'column' => 'id',
     *    'value' => array(1,2,3,4)
     * ]);
     *
     * $request = new Prism\Database\Request\Request;
     * $request->addSpecificCondition('ids', $condition);
     *
     * $gateway     = new Crowdfunding\Currency\Gateway\JoomlaGateway(\JFactory::getDbo());
     * $repository  = new Crowdfunding\Currency\Repository($gateway);
     *
     * $currencies  = $repository->fetchCollection($request);
     * </code>
     *
     * @param Request $request
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return Domain\Collection
     */
    public function fetchCollection(Request $request)
    {
        if (!$request) {
            throw new \UnexpectedValueException('There are no conditions that the system should use to fetch data.');
        }

        $data = $this->gateway->fetchCollection($request);

        if ($this->collection === null) {
            $this->collection = new Currencies;
        }

        $this->collection->clear();
        if ($data) {
            foreach ($data as $row) {
                $this->collection[] = $this->mapper->create($row);
            }
        }

        return $this->collection;
    }
}
