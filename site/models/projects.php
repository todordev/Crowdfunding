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
class CrowdfundingModelProjects extends JModelList
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
                'title', 'a.title',
                'goal', 'a.goal',
                'funded', 'a.funded',
                'funding_start', 'a.funding_start',
                'funding_end', 'a.funding_end',
                'ordering', 'a.ordering'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $state);

        $value = JFactory::getUser()->get('id');
        $this->setState('filter.user_id', $value);

        // Load the component parameters.
        $params = $app->getParams('com_crowdfunding');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.created', 'desc');
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
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.user_id');

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
        /** @var $db JDatabaseDriver */

        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.image_square, a.goal, a.funded, ' .
                'a.funding_end, a.funding_days, a.funding_start, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                'a.published, a.approved, ' .
                'b.published AS catstate, ' .
                $query->concatenate(array('b.id', 'b.alias'), ':') . ' AS catslug'
            )
        );
        $query->from($db->quoteName('#__crowdf_projects', 'a'));
        $query->leftJoin($db->quoteName('#__categories', 'b') . ' ON a.catid = b.id');

        // Filter by state
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.published = ' . (int)$state);
        } elseif ($state === '') {
            $query->where('(a.published IN (0, 1))');
        }

        $userId = $this->getState('filter.user_id');
        $query->where('a.user_id = ' . (int)$userId);

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
