<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Get a list of items
 */
class CrowdfundingModelTransactions extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'amount', 'a.txn_amount',
                'service_provider', 'a.service_provider',
                'txn_status', 'a.txn_status',
                'reward_state', 'a.reward_state',
                'txn_status', 'a.txn_status',
                'name', 'b.name',
                'title', 'c.title',
                'sender', 'e.name'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        // Prepare filter by search phrase.
        $value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $value);

        // Prepare filter by payment service.
        $value = $this->getUserStateFromRequest($this->context . '.filter.payment_service', 'filter_payment_service');
        $this->setState('filter.payment_service', $value);

        // Prepare filter by payment status.
        $value = $this->getUserStateFromRequest($this->context . '.filter.payment_status', 'filter_payment_status');
        $this->setState('filter.payment_status', $value);

        // Prepare filter by reward status.
        $value = $this->getUserStateFromRequest($this->context . '.filter.reward_state', 'filter_reward_state');
        $this->setState('filter.reward_state', $value);

        // Load the component parameters.
        $params = JComponentHelper::getParams($this->option);
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.id', 'desc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string $id A prefix for the store id.
     *
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.payment_service');
        $id .= ':' . $this->getState('filter.reward_state');
        $id .= ':' . $this->getState('filter.payment_status');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     *
     * @throws \RuntimeException
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.txn_amount, a.txn_date, a.txn_currency, a.txn_id, a.txn_status, a.investor_id, a.receiver_id, a.fee, a.params, ' .
                'a.status_reason, a.parent_txn_id, a.project_id, a.reward_id, a.receiver_id, a.service_provider, a.service_alias, a.reward_state, ' .
                'b.name AS beneficiary, ' .
                'c.title AS project, ' .
                'd.title AS reward, ' .
                'e.name AS sender '
            )
        );
        $query->from($db->quoteName('#__crowdf_transactions', 'a'));
        $query->innerJoin($db->quoteName('#__users', 'b') . ' ON a.receiver_id = b.id');
        $query->innerJoin($db->quoteName('#__crowdf_projects', 'c') . ' ON a.project_id = c.id');
        $query->leftJoin($db->quoteName('#__crowdf_rewards', 'd') . ' ON a.reward_id = d.id');

        $query->leftJoin($db->quoteName('#__users', 'e') . ' ON a.investor_id = e.id');

        // Filter by payment service.
        $paymentService = (string)$this->getState('filter.payment_service');
        if ($paymentService !== '') {
            $query->where('a.service_alias = ' . $db->quote($paymentService));
        }

        // Filter by payment status.
        $paymentStatus = (string)$this->getState('filter.payment_status');
        if ($paymentStatus !== '') {
            $query->where('a.txn_status = ' . $db->quote($paymentStatus));
        }

        // Filter by reward distributed state.
        $rewardState = $this->getState('filter.reward_state');
        if (is_numeric($rewardState)) {
            if ((int)$rewardState === Prism\Constants::NOT_SENT) {
                $query->where('a.reward_state = ' .Prism\Constants::NOT_SENT);
            } elseif ((int)$rewardState === Prism\Constants::SENT) {
                $query->where('a.reward_state = ' .Prism\Constants::SENT);
            }
        } elseif ($rewardState === 'none') {
            $query->where('a.reward_id = 0');
        }

        // Filter by search phrase.
        $search = (string)$this->getState('filter.search');
        if ($search !== '') {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } elseif (stripos($search, 'bid:') === 0) {
                $query->where('a.receiver_id = ' . (int)substr($search, 4));
            } elseif (stripos($search, 'sid:') === 0) {
                $query->where('a.investor_id = ' . (int)substr($search, 4));
            } elseif (stripos($search, 'pid:') === 0) {
                $query->where('a.project_id = ' . (int)substr($search, 4));
            } elseif (stripos($search, 'ptid:') === 0) {
                $search = substr($search, 5);
                $query->where('a.parent_txn_id = ' . $db->quote($search));
            } else {
                $escaped = $db->escape($search, true);
                $quoted  = $db->quote('%' . $escaped . '%', false);
                $query->where('a.txn_id LIKE ' . $quoted);
            }
        }

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState('list.ordering');
        $orderDirn = $this->getState('list.direction');

        return $orderCol . ' ' . $orderDirn;
    }
}
