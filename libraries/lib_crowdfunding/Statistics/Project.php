<?php
/**
 * @package      Crowdfunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Statistics;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This is a base class for project statistics.
 *
 * @package      Crowdfunding
 * @subpackage   Statistics
 */
class Project
{
    protected $id;

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
     * $projectId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\Project(\JFactory::getDbo(), $projectId);
     * </code>
     *
     * @param \JDatabaseDriver $db Database Driver
     * @param int             $id Project ID
     */
    public function __construct(\JDatabaseDriver $db, $id)
    {
        $this->db = $db;
        $this->id = (int)$id;
    }

    /**
     * Return the number of transactions.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\Project(\JFactory::getDbo(), $projectId);
     * $numberOfTransactions = $statistics->getTransactionsNumber();
     * </code>
     *
     * @return int
     */
    public function getTransactionsNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.project_id = ' . (int)$this->id);

        $this->db->setQuery($query);

        return (int)$this->db->loadResult();
    }

    /**
     * Calculate a project amount for full period of the campaign.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\Project(\JFactory::getDbo(), $projectId);
     * $amount = $statistics->getFullPeriodAmounts();
     * </code>
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function getFullPeriodAmounts()
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.funding_start, a.funding_end')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.id = ' . (int)$this->id);

        $this->db->setQuery($query);
        $result = $this->db->loadObject();

        // Validate dates
        $fundingStartDate = new Prism\Validator\Date($result->funding_start);
        $fundingEndDate   = new Prism\Validator\Date($result->funding_end);
        if (!$fundingStartDate->isValid() or !$fundingEndDate->isValid()) {
            return array();
        }

        $date    = new Prism\Date();

        $timezone = $date->getTimezone();

        $date1 = new \JDate($result->funding_start);
        $date2 = new \JDate($result->funding_end);

        $period = $date->getDaysPeriod($date1, $date2);

        $query = $this->db->getQuery(true);
        $query
            ->select('a.txn_date as date, SUM(a.txn_amount) as amount')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.project_id = ' . (int)$this->id)
            ->group('DATE(a.txn_date)');

        $this->db->setQuery($query);
        $results = (array)$this->db->loadAssocList();

        // Prepare data
        $data_ = array();
        foreach ($results as $result) {
            $date          = new \JDate($result['date']);
            $index         = $date->format('d.m');
            $data_[$index] = $result;
        }

        /** @var $day \JDate */
        $data = array();
        foreach ($period as $day) {
            $day->setTimezone($timezone);

            $amount   = 0;
            $dayMonth = $day->format('d.m');
            if (array_key_exists($dayMonth, $data_)) {
                $amount = $data_[$dayMonth]['amount'];
            }

            $data[] = array('date' => $dayMonth, 'amount' => $amount);
        }

        return $data;
    }

    /**
     * Calculate three types of project amount - goal, funded amount and remaining amount.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\Project(\JFactory::getDbo(), $projectId);
     * $data = $statistics->getFundedAmount();
     * </code>
     *
     * # Example result:
     * array(
     *    "goal" = 1000,
     *    "funded" = 100,
     *    "remaining" = 900
     * )
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function getFundedAmount()
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.goal, a.funded')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.id = ' . (int)$this->id);

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        if (count($result) === 0) {
            return $result;
        }

        // Remaining
        $remaining = (float)($result['goal'] - $result['funded']);
        if ($remaining < 0) {
            $remaining = 0;
        }

        $result['remaining'] = $remaining;
        return $result;
    }

    /**
     * Return the number of comments.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\Project(\JFactory::getDbo(), $projectId);
     * $numberOfComments = $statistics->getCommentsNumber();
     * </code>
     *
     * @return int
     */
    public function getCommentsNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_comments', 'a'))
            ->where('a.project_id = ' . (int)$this->id);

        $this->db->setQuery($query);

        return (int)$this->db->loadResult();
    }

    /**
     * Return the number of updates.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\Project(\JFactory::getDbo(), $projectId);
     * $numberOfUpdates = $statistics->getUpdatesNumber();
     * </code>
     *
     * @return int
     */
    public function getUpdatesNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__crowdf_updates', 'a'))
            ->where('a.project_id = ' . (int)$this->id);

        $this->db->setQuery($query);

        return (int)$this->db->loadResult();
    }

    /**
     * Return information about amounts by transaction statuses.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\Project(\JFactory::getDbo(), $projectId);
     * $payoutInformation = $statistics->getPayoutInformation();
     * </code>
     *
     * @return array
     */
    public function getTransactionsStatusStatistics()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select('a.project_id, a.txn_status, COUNT(id) AS transactions, SUM(txn_amount) AS amount, SUM(fee) AS fee_amount')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->group('a.txn_status')
            ->where('a.project_id = ' . (int)$this->id);

        $this->db->setQuery($query);

        return (array)$this->db->loadAssocList('txn_status');
    }

    /**
     * Return information about amounts by transaction statuses.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\Project(\JFactory::getDbo(), $projectId);
     * $payoutInformation = $statistics->getPayoutInformation();
     * </code>
     *
     * @return array
     */
    public function getPayoutStatistics()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select('a.txn_status, SUM(txn_amount) AS amount')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->group('a.txn_status')
            ->where('a.project_id = ' . (int)$this->id);

        $this->db->setQuery($query);

        return (array)$this->db->loadAssocList('txn_status');
    }
}
