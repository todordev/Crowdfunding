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
 * This class loads statistics about users.
 *
 * @package      Crowdfunding
 * @subpackage   Statistics
 */
class Users
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
     * $ids = array(1,2,3);
     *
     * $statistics   = new Crowdfunding\Statistics\Users(\JFactory::getDbo(), $ids);
     * </code>
     *
     * @param \JDatabaseDriver $db  Database Driver
     * @param array $ids Users IDs
     */
    public function __construct(\JDatabaseDriver $db, $ids)
    {
        $this->db  = $db;
        $this->ids = $ids;
    }

    /**
     * Count and return projects number of users.
     *
     * <code>
     * $usersIds = array(1,2,3);
     *
     * $statistics     = new Crowdfunding\Statistics\Users(\JFactory::getDbo(), $usersIds);
     * $projectsNumber = $statistics->getProjectsNumber();
     * </code>
     *
     * @return array
     */
    public function getProjectsNumber()
    {
        // If there are no IDs, return empty array.
        if (!$this->ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select('a.user_id, COUNT(*) as number')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.user_id IN (' . implode(',', $this->ids) . ')')
            ->group('a.user_id');

        $this->db->setQuery($query);

        return (array)$this->db->loadObjectList('user_id');
    }

    /**
     * Count and return transactions number.
     *
     * <code>
     * $usersIds = array(1,2,3);
     *
     * $statistics         = new Crowdfunding\Statistics\Users(\JFactory::getDbo(), $usersIds);
     * $transactionsNumber = $statistics->getTransactionsNumber();
     * </code>
     *
     * @return array
     */
    public function getTransactionsNumber()
    {
        // If there are no IDs, return empty array.
        if (!$this->ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select('a.investor_id, COUNT(*) as number')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.investor_id IN (' . implode(',', $this->ids) . ')')
            ->group('a.investor_id');

        $this->db->setQuery($query);

        return (array)$this->db->loadObjectList('investor_id');
    }

    /**
     * Count and return transactions number.
     *
     * <code>
     * $usersIds = array(1,2,3);
     *
     * $statistics         = new Crowdfunding\Statistics\Users(\JFactory::getDbo(), $usersIds);
     * $transactionsNumber = $statistics->getAmounts();
     * </code>
     *
     * @return array
     */
    public function getAmounts()
    {
        // If there are no IDs, return empty array.
        if (!$this->ids) {
            return array();
        }

        $statistics = array(
            'invested' => array(),
            'received' => array()
        );

        // Count invested amount and transactions.
        $query = $this->db->getQuery(true);
        $query
            ->select('a.investor_id, COUNT(*) AS number, SUM(a.txn_amount) AS amount')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.investor_id IN (' . implode(',', $this->ids) . ')')
            ->group('a.investor_id');

        $this->db->setQuery($query);

        $statistics['invested'] = (array)$this->db->loadObjectList('investor_id');

        // Count received amount and transactions.
        $query = $this->db->getQuery(true);
        $query
            ->select('a.receiver_id, COUNT(*) AS number, SUM(a.txn_amount) AS amount')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.receiver_id IN (' . implode(',', $this->ids) . ')')
            ->group('a.receiver_id');

        $this->db->setQuery($query);

        $statistics['received'] = (array)$this->db->loadObjectList('receiver_id');

        return $statistics;
    }
}
