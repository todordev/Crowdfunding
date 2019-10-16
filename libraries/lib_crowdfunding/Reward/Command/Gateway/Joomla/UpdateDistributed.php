<?php
/**
 * @package      Crowdfunding\Reward\Command\Gateway
 * @subpackage   Joomla
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Reward\Command\Gateway\Joomla;

use Crowdfunding\Reward\Reward;
use Prism\Database\JoomlaDatabase;
use Crowdfunding\Reward\Command\Gateway\UpdateDistributedGateway;

/**
 * Joomla database gateway.
 *
 * @package      Crowdfunding\Reward\Command\Gateway
 * @subpackage   Joomla
 */
class UpdateDistributed extends JoomlaDatabase implements UpdateDistributedGateway
{
    /**
     * Update reward funds.
     *
     * @param Reward  $reward
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     */
    public function update(Reward $reward)
    {
        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName('#__crowdf_rewards'))
            ->set($this->db->quoteName('distributed') . '=' . (int)$reward->getDistributed())
            ->where($this->db->quoteName('id') .'='. (int)$reward->getId());

        $this->db->setQuery($query);
        $this->db->execute();
    }
}
