<?php
/**
 * @package      Crowdfunding
 * @subpackage   Users
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\User;

use Prism;
use Prism\Database;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage user rewards.
 *
 * @package      Crowdfunding
 * @subpackage   Users
 */
class Rewards extends Database\Collection
{
    /**
     * Load data about user rewards by user ID.
     *
     * <code>
     * $options = array(
     *     "user_id" => 1
     * );
     *
     * $rewards   = new Crowdfunding\User\Rewards(\JFactory::getDbo());
     * $rewards->load($options);
     *
     * foreach($rewards as $reward) {
     *   echo $reward["reward_id"];
     *   echo $reward["reward_name"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        $query = $this->getQuery();

        // Filter by user ID.
        $userId = ArrayHelper::getValue($options, 'user_id', 0, 'int');
        if ($userId > 0) {
            $query->where('a.receiver_id = ' . (int)$userId);
        }

        // Filter by project ID.
        $projectId = ArrayHelper::getValue($options, 'project_id', 0, 'int');
        if ($projectId > 0) {
            $query->where('a.project_id = ' . (int)$projectId);
        }

        // Filter by reward ID.
        $rewardId = ArrayHelper::getValue($options, 'reward_id', 0, 'int');
        if ($rewardId > 0) {
            $query->where('a.reward_id = ' .(int)$rewardId);
        } else {
            $query->where('a.reward_id > 0');
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }

    protected function getQuery()
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select(
                'a.id AS transaction_id, a.receiver_id, a.reward_state, a.txn_id, a.reward_id, a.project_id, ' .
                'b.title AS reward_name, '.
                'c.name, c.email, ' .
                'd.title AS project'
            )
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->leftJoin($this->db->quoteName('#__crowdf_rewards', 'b'). ' ON a.reward_id = b.id')
            ->leftJoin($this->db->quoteName('#__users', 'c') . ' ON a.receiver_id = c.id')
            ->leftJoin($this->db->quoteName('#__crowdf_projects', 'd') . ' ON a.project_id = d.id');

        return $query;
    }
}
