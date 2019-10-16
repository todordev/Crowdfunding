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
 * This is a base class for user statistics.
 *
 * @package      Crowdfunding
 * @subpackage   Statistics
 */
class User
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
     * $userId    = 1;
     *
     * $statistics   = new Crowdfunding\Statistics\User(\JFactory::getDbo(), $userId);
     * </code>
     *
     * @param \JDatabaseDriver $db Database Driver
     * @param int             $id
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(\JDatabaseDriver $db, $id)
    {
        $this->db = $db;
        $this->id = (int)$id;

        if (!$this->id) {
            throw new \InvalidArgumentException(\JText::_('LIB_CROWDFUNDING_INVALID_USER'));
        }
    }

    /**
     * Count and return projects number of users.
     *
     * <code>
     * $usersId = 1;
     *
     * $statistics     = new Crowdfunding\Statistics\User(\JFactory::getDbo(), $usersId);
     * $projectsNumber = $statistics->getProjectsNumber();
     * </code>
     *
     * @return int
     */
    public function getProjectsNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select('COUNT(*) as number')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.user_id = ' . (int)$this->id);

        $this->db->setQuery($query, 0, 1);

        return (int)$this->db->loadResult();
    }

    /**
     * Count and return transactions number.
     *
     * <code>
     * $usersId = 1;
     *
     * $statistics         = new Crowdfunding\Statistics\Users(\JFactory::getDbo(), $usersId);
     * $transactionsNumber = $statistics->getAmounts();
     * </code>
     *
     * @return array
     */
    public function getAmounts()
    {
        $statistics = array(
            'invested' => array(),
            'received' => array()
        );

        // Count invested amount and transactions.
        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*) AS number, SUM(a.txn_amount) AS amount')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.investor_id = ' . (int)$this->id);

        $this->db->setQuery($query);

        $statistics['invested'] = (array)$this->db->loadObject();

        // Count received amount and transactions.
        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*) AS number, SUM(a.txn_amount) AS amount')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.receiver_id = '. (int)$this->id);

        $this->db->setQuery($query);

        $statistics['received'] = (array)$this->db->loadObject();

        return $statistics;
    }

    /**
     * Count the number of payments.
     *
     * <code>
     * $usersId = 1;
     * $projectId = 2;
     *
     * $statistics         = new Crowdfunding\Statistics\Users(\JFactory::getDbo(), $usersId);
     * $paymentsPerProject = $statistics->getNumberOfPayments($projectId);
     * </code>
     *
     * @param int $projectId
     *
     * @return int
     */
    public function getNumberOfPayments($projectId = 0)
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*) AS number')
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.investor_id = '. (int)$this->id);

        if (!empty($projectId)) {
            $query->where('a.project_id = ' . (int)$projectId);
        }

        $this->db->setQuery($query, 0, 1);

        return (int)$this->db->loadResult();
    }

    /**
     * Count the number of active campaigns.
     *
     * <code>
     * $usersId = 1;
     *
     * $statistics      = new Crowdfunding\Statistics\Users(\JFactory::getDbo(), $usersId);
     * $activeCampaigns = $statistics->getNumberOfActiveCampaigns();
     * </code>
     *
     * @return int
     */
    public function getNumberOfActiveCampaigns()
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*) AS number')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.user_id = '. (int)$this->id)
            ->where('a.published = 1')
            ->where('a.approved = 1');

        $this->db->setQuery($query, 0, 1);

        return (int)$this->db->loadResult();
    }

    /**
     * Count the number of campaigns in a period.
     * If star date and end date are not provides, the system will get default values.
     * The default values will be the beginning of the year and the end of the year.
     *
     * <code>
     * $usersId = 1;
     *
     * $statistics        = new Crowdfunding\Statistics\Users(\JFactory::getDbo(), $usersId);
     * $numberOfCampaigns = $statistics->getNumberOfActiveCampaigns();
     * </code>
     *
     * @param string $startDate
     * @param string $endDate
     *
     * @return int
     */
    public function getNumberOfCampaignsInPeriod($startDate = null, $endDate = null)
    {
        // Set default start date.
        if (!(int)$startDate) {
            $date = new Prism\Date();
            $date = $date->getBeginOfYear();

            $startDate = $date->toSql();
        }

        // Set default end date.
        if (!(int)$endDate) {
            $date = new Prism\Date();
            $date = $date->getEndOfYear();

            $endDate = $date->toSql();
        }

        $startDate = new \JDate($startDate);
        $endDate   = new \JDate($endDate);

        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*) AS number')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.user_id = '. (int)$this->id)
            ->where('a.funding_start >= ' . $this->db->quote($startDate->toSql()))
            ->where('a.funding_start <= ' . $this->db->quote($endDate->toSql()));

        $this->db->setQuery($query, 0, 1);

        return (int)$this->db->loadResult();
    }
}
