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

class CrowdfundingModelFunders extends JModelList
{
    protected $items;
    protected $numbers;
    protected $params;

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
                'name', 'a.name'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering
     * @param string $direction
     *
     * @return  void
     * @since   1.6
     * @throws \Exception
     */
    protected function populateState($ordering = 'ordering', $direction = 'ASC')
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load parameters
        $params = $app->getParams();
        $this->setState('params', $params);

        // Get project id
        $value = $app->input->get('id', 0, 'uint');
        $this->setState($this->context . '.project_id', $value);

        parent::populateState('a.txn_date', 'DESC');
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
        $id .= ':' . $this->getState($this->context . '.project_id');
        $id .= ':' . $this->getState('list.ordering');
        $id .= ':' . $this->getState('list.direction');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     * @throws \Exception
     */
    protected function getListQuery()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load parameters
        $params = $app->getParams();

        $displayAnonymous = $params->get('funders_display_anonymous', 0);

        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.txn_date, a.txn_amount, a.txn_currency, ' .
                'b.id, b.name'
            )
        );

        $query->from($db->quoteName('#__crowdf_transactions', 'a'));
        $query->leftJoin($db->quoteName('#__users', 'b') . ' ON a.investor_id = b.id');

        // Filter by project id
        $projectId = $this->getState($this->context . '.project_id');
        $query->where('a.project_id =' . (int)$projectId);

        if (!$displayAnonymous) {
            $query->where('a.investor_id != 0');
        }

        $query->where('(a.txn_status = ' . $db->quote('completed') . ' OR a.txn_status = '. $db->quote('pending') . ')');

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
