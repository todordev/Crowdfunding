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

class CrowdfundingModelManager extends CrowdfundingModelProject
{
    /**
     * Return information about project rewards and their receivers.
     *
     * @param int $projectId
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getRewardsData($projectId)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select(
                'a.id AS transaction_id, a.receiver_id, a.reward_state, a.txn_id, a.reward_id, ' .
                'b.title AS reward, b.distributed, '.
                'c.name, c.email'
            )
            ->from($db->quoteName('#__crowdf_transactions', 'a'))
            ->leftJoin($db->quoteName('#__crowdf_rewards', 'b') . ' ON a.reward_id = b.id')
            ->leftJoin($db->quoteName('#__users', 'c') . ' ON a.receiver_id = c.id')
            ->where('a.project_id = '. (int)$projectId)
            ->where('a.reward_id > 0 ');

        $db->setQuery($query);

        return (array)$db->loadObjectList();
    }
}
