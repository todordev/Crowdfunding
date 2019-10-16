<?php
/**
 * @package      Crowdfunding
 * @subpackage   Payments
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Payment\Session;

use Prism\Domain;
use Prism\Database\Request\Request;
use Crowdfunding\Payment\Session\Gateway\SessionGateway;

/**
 * This class provides a glue between persistence layer and payment session object.
 *
 * @package      Crowdfunding
 * @subpackage   Payments
 */
class Repository extends Domain\Repository
{
    /**
     * @var SessionGateway
     */
    protected $gateway;

    /**
     * Repository constructor.
     *
     * @param Mapper $mapper
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper  = $mapper;
        $this->gateway = $mapper->getGateway();
    }

    /**
     * Save session entity to database records.
     *
     * @param Session $entity
     *
     * @throws \InvalidArgumentException
     */
    public function store(Session $entity)
    {
        $this->mapper->save($entity);
    }

    /**
     * Store service data.
     *
     * @param string $serviceName
     * @param Session $entity
     *
     * @throws \InvalidArgumentException
     */
    public function storeServiceData($serviceName, Session $entity)
    {
        $service = $entity->service($serviceName);

        $this->gateway->storeServiceData($service);
    }

    /**
     * Remove session entity from database records.
     *
     * @param Session $entity
     *
     * @throws \InvalidArgumentException
     */
    public function delete(Session $entity)
    {
        $this->mapper->delete($entity);
    }

    /**
     * Load the data from database and return an entity.
     *
     * @param int $id
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return Session
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
     * @param Request $request
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return Session
     */
    public function fetch(Request $request)
    {
        if (!$request) {
            throw new \UnexpectedValueException('There are no conditions that the system should use to fetch data.');
        }

        $data = $this->gateway->fetch($request);

        return $this->mapper->create($data);
    }
}
