<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
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
     * @param   array  $config An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'date', 'a.txn_date',
                'amount', 'a.txn_amount',
                'project', 'b.title',
                'reward', 'd.title',
                'investor', 'e.name',
                'receiver', 'f.name',
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $value = JFactory::getUser()->get('id');
        $this->setState('filter.receiver_id', $value);

        $value = $app->input->getString('filter_search');
        $this->setState('filter.search', $value);

        // Load the component parameters.
        $params = $app->getParams($this->option);
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.txn_date', 'desc');
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
        $id .= ':' . $this->getState('filter.receiver_id');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     * @throws \RuntimeException
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver **/

        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.txn_amount, a.txn_date, a.txn_currency, a.txn_id, a.txn_status, ' .
                'a.project_id, a.reward_id, a.investor_id, a.receiver_id, a.service_provider, a.reward_state, a.reward_id, ' .
                'b.title AS project, ' .
                $query->concatenate(array('b.id', 'b.alias'), ':') . ' AS slug, ' .
                $query->concatenate(array('c.id', 'c.alias'), ':') . ' AS catslug, ' .
                'd.title AS reward, ' .
                'e.name AS investor, ' .
                'f.name AS receiver'
            )
        );
        $query->from($db->quoteName('#__crowdf_transactions', 'a'));
        $query->innerJoin($db->quoteName('#__crowdf_projects', 'b') . ' ON a.project_id = b.id');
        $query->innerJoin($db->quoteName('#__categories', 'c') . ' ON b.catid = c.id');

        $query->leftJoin($db->quoteName('#__crowdf_rewards') . ' AS d ON a.reward_id = d.id');
        $query->leftJoin($db->quoteName('#__users') . ' AS e ON a.investor_id = e.id');
        $query->innerJoin($db->quoteName('#__users') . ' AS f ON a.receiver_id = f.id');

        // Filter by search phrase or ID.
        $search = (string)$this->getState('filter.search');
        if ($search !== '') {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } else {
                $escaped = $db->escape($search, true);
                $quoted  = $db->quote('%' . $escaped . '%', false);
                $query->where('b.title LIKE ' . $quoted);
            }
        }

        // Filter by receiver
        $userId = $this->getState('filter.receiver_id');
        $query->where('(a.investor_id = ' . (int)$userId . ' OR a.receiver_id = ' . (int)$userId .')');

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

    public function changeRewardsState($id, $state)
    {
        $state = (!$state) ? Prism\Constants::NOT_SENT : Prism\Constants::SENT;

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->update($db->quoteName('#__crowdf_transactions'))
            ->set($db->quoteName('reward_state') .'='. (int)$state)
            ->where($db->quoteName('id') .'='. (int)$id);

        $db->setQuery($query);
        $db->execute();
    }
}
