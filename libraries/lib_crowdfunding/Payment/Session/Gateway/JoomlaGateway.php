<?php
/**
 * @package      Crowdfunding
 * @subpackage   Payments\Gateways
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Payment\Session\Gateway;

use Crowdfunding\Payment\Session\ServiceData;
use Prism\Database\Request\Request;
use Prism\Database\Joomla\FetchMethods;
use Crowdfunding\Payment\Session\Session;
use Prism\Database\JoomlaDatabaseGateway;

/**
 * Joomla database gateway.
 *
 * @package      Crowdfunding
 * @subpackage   Payments\Gateways
 */
class JoomlaGateway extends JoomlaDatabaseGateway implements SessionGateway
{
    use FetchMethods;

    /**
     * Fetch a data from database by item ID.
     *
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    public function fetch(Request $request)
    {
        if (!$request) {
            throw new \UnexpectedValueException('There are no request that the system should use to fetch data.');
        }

        $query = $this->getQuery($request);
        $this->filter($query, $request);

        $this->db->setQuery($query);

        $result = (array)$this->db->loadAssoc();

        // If it is not empty, get the gateways data.
        if (array_key_exists('id', $result)) {
            $result['services'] = $this->fetchServices($result['id']);
        }

        return $result;
    }

    /**
     * Fetch a data from database by item ID.
     *
     * @param int $id
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return array
     */
    public function fetchById($id, Request $request = null)
    {
        if (!$id) {
            throw new \InvalidArgumentException('There is no ID.');
        }

        $query = $this->getQuery($request);

        // Filter by ID.
        $query->where('a.id = ' . (int)$id);

        $this->db->setQuery($query);

        $result = (array)$this->db->loadAssoc();

        // If it is not empty, get the gateways data.
        if (array_key_exists('id', $result)) {
            $result['services'] = $this->fetchServices($result['id']);
        }

        return $result;
    }

    /**
     * Prepare the query by query builder.
     *
     * @param Request $request
     *
     * @return \JDatabaseQuery
     * @throws \RuntimeException
     */
    protected function getQuery(Request $request = null)
    {
        $query = $this->db->getQuery(true);

        $defaultFields  = [
            'a.id', 'a.user_id', 'a.project_id', 'a.reward_id',
            'a.record_date', 'a.auser_id', 'a.session_id', 'a.intention_id'
        ];

        $fields = $this->prepareFields($request, $defaultFields);

        // If there are no fields, use default ones.
        if (count($fields) === 0) {
            $fields = $defaultFields;
            unset($defaultFields);
        }

        $query
            ->select($fields)
            ->from($this->db->quoteName('#__crowdf_paymentsessions', 'a'));

        return $query;
    }

    /**
     * Insert a new payment session record.
     *
     * @param Session $object
     */
    public function insertObject(Session $object)
    {
        $recordDate   = (!$object->getRecordDate()) ? 'NULL' : $this->db->quote($object->getRecordDate());

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName('#__crowdf_paymentsessions'))
            ->set($this->db->quoteName('user_id') . '=' . $this->db->quote($object->getUserId()))
            ->set($this->db->quoteName('project_id') . '=' . $this->db->quote($object->getProjectId()))
            ->set($this->db->quoteName('reward_id') . '=' . $this->db->quote($object->getRewardId()))
            ->set($this->db->quoteName('record_date') . '=' . $recordDate)
            ->set($this->db->quoteName('auser_id') . '=' . $this->db->quote($object->getAnonymousUserId()))
            ->set($this->db->quoteName('session_id') . '=' . $this->db->quote($object->getSessionId()))
            ->set($this->db->quoteName('intention_id') . '=' . $this->db->quote($object->getIntentionId()));

        $this->db->setQuery($query);
        $this->db->execute();

        $object->setId($this->db->insertid());
    }

    /**
     * Update payment session record.
     *
     * @param Session $object
     * @todo update service data
     */
    public function updateObject(Session $object)
    {
        $recordDate   = (!$object->getRecordDate()) ? 'NULL' : $this->db->quote($object->getRecordDate());

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName('#__crowdf_paymentsessions'))
            ->set($this->db->quoteName('user_id') . '=' . $this->db->quote($object->getUserId()))
            ->set($this->db->quoteName('project_id') . '=' . $this->db->quote($object->getProjectId()))
            ->set($this->db->quoteName('reward_id') . '=' . $this->db->quote($object->getRewardId()))
            ->set($this->db->quoteName('record_date') . '=' . $recordDate)
            ->set($this->db->quoteName('auser_id') . '=' . $this->db->quote($object->getAnonymousUserId()))
            ->set($this->db->quoteName('session_id') . '=' . $this->db->quote($object->getSessionId()))
            ->set($this->db->quoteName('intention_id') . '=' . $this->db->quote($object->getIntentionId()))
            ->where($this->db->quoteName('id') . '=' . $this->db->quote($object->getId()));

        $this->db->setQuery($query);
        $this->db->execute();
    }

    public function deleteObject(Session $object)
    {
        // Delete service records.
        $query = $this->db->getQuery(true);
        $query
            ->delete($this->db->quoteName('#__crowdf_paymentsessiongateways'))
            ->where($this->db->quoteName('id') .'='. (int)$object->getId());

        $this->db->setQuery($query);
        $this->db->execute();

        // Delete the session record.
        $query = $this->db->getQuery(true);
        $query
            ->delete($this->db->quoteName('#__crowdf_paymentsessions'))
            ->where($this->db->quoteName('id') .'='. (int)$object->getId());

        $this->db->setQuery($query);
        $this->db->execute();

        $object->reset();
    }

    public function storeServiceData(ServiceData $service)
    {
        $primaryKey     = (int)$service->getId();
        if (!$primaryKey) {
            throw new \UnexpectedValueException('Invalid session primary key.');
        }

        // Store data to gateways repository.
        $token          = $service->getToken();
        $orderId        = $service->getOrderId();
        $gatewayData    = $service->getData();

        // Convert the gateway data to JSON format.
        if ($gatewayData) {
            $gatewayData = json_encode($gatewayData);
        } else {
            $gatewayData = 'NULL';
        }

        // Check for existing record.
        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_paymentsessiongateways'))
            ->where($this->db->quoteName('id') .'='. (int)$primaryKey)
            ->where($this->db->quoteName('alias') .'='. $this->db->quote($service->getAlias()));

        $this->db->setQuery($query, 0, 1);
        $recordExists = (bool)$this->db->loadResult();

        // Prepare the query.
        $query = $this->db->getQuery(true);
        $query
            ->set($this->db->quoteName('token') . '=' . $this->db->quote($token))
            ->set($this->db->quoteName('order_id') . '=' . $this->db->quote($orderId))
            ->set($this->db->quoteName('data') . '=' . $this->db->quote($gatewayData));

        if ($recordExists) { // Update
            $query
                ->update($this->db->quoteName('#__crowdf_paymentsessiongateways'))
                ->where($this->db->quoteName('id') .'='. (int)$primaryKey)
                ->where($this->db->quoteName('alias') .'='. $this->db->quote($service->getAlias()));
        } else {
            $query
                ->insert($this->db->quoteName('#__crowdf_paymentsessiongateways'))
                ->set($this->db->quoteName('alias') . '=' . $this->db->quote($service->getAlias()))
                ->set($this->db->quoteName('id') . '=' . (int)$primaryKey);
        }

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function fetchServices($id)
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('b.order_id, b.token, b.alias, b.data')
            ->from($this->db->quoteName('#__crowdf_paymentsessiongateways', 'b'))
            ->where('b.id = '. (int)$id);

        $this->db->setQuery($query);

        return (array)$this->db->loadAssocList();
    }

    /**
     * Prepare some query filters.
     *
     * @param \JDatabaseQuery $query
     * @param Request         $request
     *
     * @throws \InvalidArgumentException
     */
    protected function filter(\JDatabaseQuery $query, Request $request)
    {
        $conditions = $request->getConditions();

        // Filter by service token.
        if ($conditions->getSpecificCondition('token')) {
            $condition = $conditions->getSpecificCondition('token');
            if ($condition !== null) {
                $query
                    ->leftJoin($this->db->quoteName('#__crowdf_paymentsessiongateways', 'b') . ' ON a.id = b.id')
                    ->where($this->db->quoteName('b.token') .'='. $this->db->quote($condition->getValue()));
            }
        }

        // Filter by standard conditions.
        parent::filter($query, $request);
    }
}
