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

class CrowdfundingModelCategory extends JModelList
{
    protected $items;
    protected $numbers;
    protected $params;

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
            $config['filter_fields'] = array();
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
        parent::populateState('a.ordering', 'ASC');

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load parameters
        $params = $app->getParams();
        $this->setState('params', $params);

        // Filter by country
        $value = $app->input->get('filter_country', '', 'cmd');
        $this->setState($this->context . '.filter_country', $value);

        // Filter by location
        $value = $app->input->get('filter_location', 0, 'int');
        $this->setState($this->context . '.filter_location', $value);

        // Filter by region
        $value = $app->input->get('filter_region', '', 'cmd');
        $this->setState($this->context . '.filter_region', $value);

        // Filter by phrase
        $value = $app->input->get('filter_phrase');
        $this->setState($this->context . '.filter_phrase', $value);

        // Filter by filter type
        $value = $app->input->get('filter_fundingtype', '', 'cmd');
        $this->setState($this->context . '.filter_fundingtype', $value);

        // Filter by filter type
        $value = $app->input->get('filter_projecttype', 0, 'uint');
        $this->setState($this->context . '.filter_projecttype', $value);

        // Filter by filter date.
        $value = $app->input->get('filter_date', '', 'string');
        $this->setState($this->context . '.filter_date', $value);

        // Filter by funding state.
        $value = $app->input->get('filter_funding_state', '', 'string');
        $this->setState($this->context . '.filter_funding_state', $value);

        // Filter by featured state.
        $value = $app->input->get('filter_featured');
        $this->setState($this->context . '.filter_featured', $value);

        // Filter by user ID.
        $value = $app->input->get('filter_user');
        $this->setState($this->context . '.filter_user', $value);

        $catId = $app->input->get('id', 0, 'uint');

        // It is a discovery page and I can filter it by category.
        // If it is a subcategory page, there is a category ID
        if (!$catId) {
            // Get category ID from filter data.
            $catId = $app->input->get('filter_category');
        } else {
            $app->input->set('filter_category', (int)$catId);
        }

        // Set category ID to the session state.
        $this->setState($this->context . '.category_id', $catId);

        // Set limit
        $value = $app->input->getInt('limit');
        if (!$value) {
            $value = $params->get('items_limit', $app->get('list_limit', 20));
        }
        $this->setState('list.limit', $value);

        $value = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $value);
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
        $id .= ':' . $this->getState($this->context . '.category_id');
        $id .= ':' . $this->getState($this->context . '.filter_country');
        $id .= ':' . $this->getState($this->context . '.filter_location');
        $id .= ':' . $this->getState($this->context . '.filter_fundingtype');
        $id .= ':' . $this->getState($this->context . '.filter_projecttype');
        $id .= ':' . $this->getState($this->context . '.filter_phrase');
        $id .= ':' . $this->getState($this->context . '.filter_date');
        $id .= ':' . $this->getState($this->context . '.filter_funding_state');
        $id .= ':' . $this->getState($this->context . '.filter_featured');
        $id .= ':' . $this->getState($this->context . '.filter_user');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     *
     * @throws \Exception
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
                'a.id, a.title, a.short_desc, a.image, a.user_id, a.catid, a.featured, a.params, ' .
                'a.goal, a.funded, a.funding_start, a.funding_end, a.funding_days, a.funding_type, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                'u.name AS user_name, ' .
                $query->concatenate(array('c.id', 'c.alias'), ':') . ' AS catslug'
            )
        );

        $query
            ->from($db->quoteName('#__crowdf_projects', 'a'))
            ->innerJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id')
            ->innerJoin($db->quoteName('#__users', 'u') . ' ON a.user_id = u.id');

        // Filter by state
        $query
            ->where('a.published = ' . Prism\Constants::PUBLISHED)
            ->where('a.approved = ' . Prism\Constants::APPROVED);

        // Filter by access level.
        $user   = JFactory::getUser();
        $groups = array_unique((array)$user->getAuthorisedViewLevels());
        $groups = implode(',', $groups);

        $query
            ->where('a.access IN (' . $groups . ')')
            ->where('c.access IN (' . $groups . ')');

        $this->prepareFilters($query);
        $this->prepareFilterDate($query);
        $this->prepareFilterFundingState($query);

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $params    = $this->getState('params');

        $order     = $this->getState('list.ordering');
        $orderDirn = $this->getState('list.direction');

        if (!is_numeric($order)) {
            $order     = $params->get('items_order', Crowdfunding\Constants::ORDER_BY_START_DATE);
            $orderDirn = $params->get('items_order_direction', 'desc');
        }

        // Convert direction to uppercase.
        $orderDirn = strtoupper($orderDirn);

        // Validate directions.
        $allowedDirns = array('ASC', 'DESC');
        if (!in_array($orderDirn, $allowedDirns, true)) {
            $orderDirn = 'ASC';
        }

        $fundingEndSort = ', a.funding_end ASC';

        switch ($order) {
            case Crowdfunding\Constants::ORDER_BY_NAME:
                $orderCol = 'a.title';
                break;

            case Crowdfunding\Constants::ORDER_BY_CREATED_DATE:
                $orderCol = 'a.created';
                break;

            case Crowdfunding\Constants::ORDER_BY_START_DATE:
                $orderCol = 'a.funding_start';
                break;

            case Crowdfunding\Constants::ORDER_BY_END_DATE:
                $orderCol = 'a.funding_end';
                $fundingEndSort = '';
                break;

            case Crowdfunding\Constants::ORDER_BY_POPULARITY:
                $orderCol = 'a.hits';
                break;

            case Crowdfunding\Constants::ORDER_BY_FUNDING:
                $orderCol = 'a.funded';
                break;

            default: // Ordering
                $orderCol = 'a.ordering';
                break;
        }

        $orderString = 'a.featured DESC, ' . $orderCol . ' ' . $orderDirn . $fundingEndSort;

        return $orderString;
    }

    /**
     * Prepare some main filters.
     *
     * @param JDatabaseQuery $query
     *
     * @throws \RuntimeException
     */
    protected function prepareFilters(&$query)
    {
        $db     = $this->getDbo();

        // Filter by featured state.
        $value  = $this->getState($this->context . '.filter_featured');
        if ($value !== null) {
            $value = $value ? Prism\Constants::FEATURED : Prism\Constants::NOT_FEATURED;
            $query->where('a.featured = '. $value);
        }

        // Filter by category ID
        $value = (int)$this->getState($this->context . '.category_id', 0);
        if ($value > 0) {
            $query->where('a.catid = ' . $value);
        }

        // Filter by project type
        $value = (int)$this->getState($this->context . '.filter_projecttype', 0);
        if ($value > 0) {
            $query->where('a.type_id = ' . $value);
        }

        // Filter by user.
        $value = (int)$this->getState($this->context . '.filter_user', 0);
        if ($value > 0) {
            $query->where('a.user_id = ' . $value);
        }

        // Filter by country
        $value = (string)$this->getState($this->context . '.filter_country');
        if ($value !== '') {
            $query->innerJoin($db->quoteName('#__crowdf_locations', 'l') . ' ON a.location_id = l.id');
            $query->where('l.country_code = ' . $db->quote($value));
        }

        // Filter by region
        $value = (string)$this->getState($this->context . '.filter_region');
        if ($value !== '') {
            $query->innerJoin($db->quoteName('#__crowdf_locations', 'l') . ' ON a.location_id = l.id');
            $query->where('l.admin1code_id = '. $db->quote($value));
        }

        // Filter by location
        $value = (int)$this->getState($this->context . '.filter_location');
        if ($value > 0) {
            $query->where('a.location_id = ' . $value);
        }

        // Filter by funding type
        $value = strtoupper(trim($this->getState($this->context . '.filter_fundingtype')));
        if ($value !== '') {
            $allowedFundingTypes = array('FIXED', 'FLEXIBLE');
            if (in_array($value, $allowedFundingTypes, true)) {
                $query->where('a.funding_type = ' . $db->quote($value));
            }
        }

        // Filter by phrase
        $value = (string)$this->getState($this->context . '.filter_phrase');
        if ($value !== '') {
            $escaped = $db->escape($value, true);
            $quoted  = $db->quote('%' . $escaped . '%', false);
            $query->where('a.title LIKE ' . $quoted);
        }
    }

    /**
     * Prepare filter by date.
     *
     * @param JDatabaseQuery $query
     *
     * @throws \Exception
     */
    protected function prepareFilterDate(&$query)
    {
        $db     = $this->getDbo();

        // Filter by period.
        $filter = (string)$this->getState($this->context . '.filter_date');
        switch ($filter) {
            case 'ssoon': // Starting soon
                $date  = new JDate();
                $today = $date->toSql();

                $date->sub(new DateInterval('P7D'));
                $query->where('a.funding_start >= ' . $db->quote($date->toSql()) . ' AND a.funding_start <= '. $db->quote($today));
                break;

            case 'esoon': // Ending soon
                $date  = new JDate();
                $today = $date->toSql();

                $date->add(new DateInterval('P7D'));
                $query->where('a.funding_end >= ' . $db->quote($today) . ' AND a.funding_start <= '. $db->quote($date->toSql()));
                break;
        }
    }

    /**
     * Prepare filter by funding state.
     *
     * @param JDatabaseQuery $query
     */
    protected function prepareFilterFundingState(&$query)
    {
        $db     = JFactory::getDbo();

        // Filter by funding state.
        $filter = (string)$this->getState($this->context . '.filter_funding_state');
        switch ($filter) {
            case 'sfunded': // Successfully funded.
                $date  = new JDate();
                $today = $date->toSql();

                $query->where('a.funding_end < ' . $db->quote($today) . ' AND a.funded >= a.goal');
                break;
        }
    }
}
