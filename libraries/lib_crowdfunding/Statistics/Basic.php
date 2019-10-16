<?php
/**
 * @package      Crowdfunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Statistics;

defined('JPATH_PLATFORM') or die;

/**
 * This class loads statistics about transactions.
 *
 * @package      Crowdfunding
 * @subpackage   Statistics
 */
class Basic
{
    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * <code>
     * $statistics   = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db
     */
    public function __construct(\JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Get the number of all projects.
     *
     * <code>
     * $options = array(
     *     "state"    => 1, // The state of the campaign - published or unpublished.
     *     "approved" => 1, // The approved state - approved or not approved.
     * );
     *
     * $statistics   = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     * $numberOfProjects = $statistics->getTotalProjects($options);
     * </code>
     *
     * @param array $options Options used to be aggregated data.
     *
     * @throws \RuntimeException
     * @return int
     */
    public function getTotalProjects(array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'));

        // Filter by state.
        if (array_key_exists('state', $options)) {
            $query->where('a.published =' .(int)$options['state']);
        }

        // Filter by approved state.
        if (array_key_exists('approved', $options)) {
            $query->where('a.approved =' .(int)$options['approved']);
        }

        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    /**
     * Get the number of featured projects.
     *
     * <code>
     * $options = array(
     *     "state"    => 1, // The state of the campaign - published or unpublished.
     *     "approved" => 1, // The approved state - approved or not approved.
     * );
     *
     * $statistics       = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     * $numberOfProjects = $statistics->getFeaturedProjects($options);
     * </code>
     *
     * @param array $options Options used to be aggregated data.
     *
     * @throws \RuntimeException
     * @return int
     */
    public function getFeaturedProjects(array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.featured = 1');

        // Prepare filters.
        $this->prepareFilters($query, $options);

        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }


    /**
     * Get the number of all transactions.
     *
     * <code>
     * $statistics   = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     * $numberOfProjects = $statistics->getTotalTransactions();
     * </code>
     *
     * @throws \RuntimeException
     */
    public function getTotalTransactions()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'));

        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    /**
     * Get total amount of all transactions.
     *
     * <code>
     * $statistics   = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     * $numberOfProjects = $statistics->getTotalAmount();
     * </code>
     *
     * @throws \RuntimeException
     */
    public function getTotalAmount()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('SUM(a.txn_amount)')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'));

        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    /**
     * Get the number of started soon projects.
     *
     * <code>
     * $options = array(
     *     "interval" => 7, // The number of last days when the campaigns have been started.
     *     "state"    => 1, // The state of the campaign - published or unpublished.
     *     "approved" => 1, // The approved state - approved or not approved.
     * );
     *
     * $statistics   = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     * $numberOfProjects = $statistics->getStartedSoonProjects();
     * </code>
     *
     * @param array $options Options used to be aggregated data.
     *
     * @throws \RuntimeException
     * @return int
     */
    public function getStartedSoonProjects($options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'));

        // Filter by date interval.
        if (array_key_exists('interval', $options)) {
            $days = (int)$options['interval'];

            if ($days > 0) {
                jimport('joomla.date.date');
                $date = new \JDate();
                $today = $date->toSql();

                $date->sub(new \DateInterval('P'.$days.'D'));
                $query->where('a.funding_start >= ' . $this->db->quote($date->toSql()) . ' AND a.funding_start <= '. $this->db->quote($today));
            }
        }

        // Prepare filters.
        $this->prepareFilters($query, $options);

        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    /**
     * Get the number of ending soon projects.
     *
     * <code>
     * $options = array(
     *     "interval" => 7, // The number of next days when the campaigns is going to finish.
     *     "state"    => 1, // The state of the campaign - published or unpublished.
     *     "approved" => 1, // The approved state - approved or not approved.
     * );
     * $statistics   = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     * $numberOfProjects = $statistics->getEndingSoonProjects();
     * </code>
     *
     * @param array $options Options used to be aggregated data.
     *
     * @throws \RuntimeException
     * @return int
     */
    public function getEndingSoonProjects(array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'));

        // Filter by date interval.
        if (array_key_exists('interval', $options)) {
            $days = (int)$options['interval'];

            if ($days > 0) {
                jimport('joomla.date.date');
                $date  = new \JDate();
                $today = $date->toSql();

                $date->add(new \DateInterval('P'.$days.'D'));
                $query->where('a.funding_end >= ' . $this->db->quote($today) . ' AND a.funding_start <= '. $this->db->quote($date->toSql()));
            }
        }

        // Prepare filters.
        $this->prepareFilters($query, $options);

        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    /**
     * Get the number of successfully completed projects.
     *
     * <code>
     * $options = array(
     *     "state"    => 1, // The state of the campaign - published or unpublished.
     *     "approved" => 1, // The approved state - approved or not approved.
     * );
     *
     * $statistics   = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     * $numberOfProjects = $statistics->getSuccessfullyCompletedProjects();
     * </code>
     *
     * @param array $options Options used to be aggregated data.
     *
     * @throws \RuntimeException
     * @return int
     */
    public function getSuccessfullyCompletedProjects(array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'));

        // Prepare filters.
        $this->prepareFilters($query, $options);

        // Filter by funding date.
        jimport('joomla.date.date');
        $date  = new \JDate();
        $today = $date->toSql();

        $query->where('a.funding_end < ' . $this->db->quote($today) . ' AND a.funded >= a.goal');

        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    /**
     * Prepare some main filters.
     *
     * @param \JDatabaseQuery $query
     * @param array $options
     */
    protected function prepareFilters(&$query, $options)
    {
        // Filter by state.
        if (array_key_exists('state', $options)) {
            $query->where('a.published =' .(int)$options['state']);
        }

        // Filter by approved state.
        if (array_key_exists('approved', $options)) {
            $query->where('a.approved =' .(int)$options['approved']);
        }
    }

    /**
     * Return the number of items in categories.
     *
     * <code>
     * $item   = new Crowdfunding\Statistics\Basic(\JFactory::getDbo());
     *
     * $categoryItems = $item->countCategoryItems();
     * </code>
     *
     * @param array $ids
     * @param array $options
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function countCategoryItems(array $ids, array $options = array())
    {
        $results = array();

        if (count($ids) > 0) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.catid, COUNT(*) as number')
                ->from($this->db->quoteName('#__crowdf_projects', 'a'))
                ->where('a.catid IN (' . implode(',', $ids) . ')')
                ->group($this->db->quoteName('catid'));

            $state = array_key_exists('state', $options) ? $options['state'] : null;

            // Filter by state
            if ($state !== null) {
                $query->where('a.published = ' .(int)$state);
            }

            $this->db->setQuery($query);
            $results = (array)$this->db->loadAssocList('catid', 'number');
        }

        return $results;
    }
}
