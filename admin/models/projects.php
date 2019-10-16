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

class CrowdfundingModelProjects extends JModelList
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
                'title', 'a.title',
                'goal', 'a.goal',
                'funded', 'a.funded',
                'funded_percents',
                'funding_start', 'a.funding_start',
                'funding_end', 'a.funding_end',
                'ordering', 'a.ordering',
                'published', 'a.published',
                'approved', 'a.approved',
                'created', 'a.created',
                'type_id', 't.type',
                'type', 't.title',
                'category', 'c.title',
                'owner', 'u.name',
                'access_level', 'ag.title'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.created', $direction = 'desc')
    {
        // Load the component parameters.
        $params = JComponentHelper::getParams($this->option);
        $this->setState('params', $params);

        // Load filter search.
        $value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $value);

        // Load filter state.
        $value = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $value);

        // Load filter approved state.
        $value = $this->getUserStateFromRequest($this->context . '.filter.approved', 'filter_approved', '', 'string');
        $this->setState('filter.approved', $value);

        // Load filter featured state.
        $value = $this->getUserStateFromRequest($this->context . '.filter.featured', 'filter_featured', '', 'string');
        $this->setState('filter.featured', $value);

        // Load filter category.
        $value = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '', 'int');
        $this->setState('filter.category', $value);

        // Load filter type.
        $value = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'int');
        $this->setState('filter.type', $value);

        // Get filter author
        $value = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->setState('filter.access', $value);

        // List state information.
        parent::populateState($ordering, $direction);
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
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.approved');
        $id .= ':' . $this->getState('filter.featured');
        $id .= ':' . $this->getState('filter.category');
        $id .= ':' . $this->getState('filter.type');
        $id .= ':' . $this->getState('filter.access');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @throws  \RuntimeException
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver*/

        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.goal, a.funded, a.funding_start, a.funding_end, a.user_id, ' .
                'a.funding_days, a.ordering, a.created, a.catid, ROUND( (a.funded/a.goal) * 100, 1 ) AS funded_percents, ' .
                'a.featured, a.published, a.approved, ' .
                'c.title AS category, ' .
                't.title AS type, ' .
                'u.name AS username,' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                $query->concatenate(array('c.id', 'c.alias'), ':') . ' AS catslug, ' .
                'ag.title AS access_level'
            )
        );
        $query->from($db->quoteName('#__crowdf_projects', 'a'));
        $query->leftJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id');
        $query->leftJoin($db->quoteName('#__crowdf_types', 't') . ' ON a.type_id = t.id');
        $query->leftJoin($db->quoteName('#__users', 'u') . ' ON a.user_id = u.id');

        // Join over the asset groups.
        $query->leftJoin($db->quoteName('#__viewlevels', 'ag') .' ON ag.id = a.access');

        // Filter by category
        $categoryId = (int)$this->getState('filter.category');
        if ($categoryId > 0) {
            $query->where('c.id = ' . (int)$categoryId);
        }

        // Filter by state
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.published = ' . (int)$state);
        } elseif ($state === '') {
            $query->where('(a.published IN (0, 1))');
        }

        // Filter by approved state
        $state = $this->getState('filter.approved');
        if (is_numeric($state)) {
            $query->where('a.approved = ' . (int)$state);
        } elseif ($state === '') {
            $query->where('(a.approved IN (0, 1))');
        }

        // Filter by approved state
        $state = $this->getState('filter.featured');
        if (is_numeric($state)) {
            $query->where('a.featured = ' . (int)$state);
        } elseif ($state === '') {
            $query->where('(a.featured IN (0, 1))');
        }

        // Filter by type
        $typeId = (int)$this->getState('filter.type');
        if ($typeId > 0) {
            $query->where('a.type_id = ' . (int)$typeId);
        }

        // Filter by access level.
        $access = (int)$this->getState('filter.access');
        if ($access > 0) {
            $query->where('a.access = ' . (int)$access);
        }

        // Implement View Level Access
        $user = JFactory::getUser();
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (strlen($search) > 0) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } elseif (stripos($search, 'uid:') === 0) {
                $query->where('a.user_id = ' . (int)substr($search, 4));
            } else {
                $escaped = $db->escape($search, true);
                $quoted  = $db->quote('%' . $escaped . '%', false);
                $query->where('a.title LIKE ' . $quoted);
            }
        }

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState('list.ordering', 'a.created');
        $orderDirn = $this->getState('list.direction', 'asc');
        if ($orderCol === 'a.ordering') {
            $orderCol = 'a.catid ' . $orderDirn . ', a.ordering';
        }

        return $orderCol . ' ' . $orderDirn;
    }
}
