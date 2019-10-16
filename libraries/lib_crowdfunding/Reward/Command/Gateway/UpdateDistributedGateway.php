<?php
/**
 * @package         Crowdfunding\Reward\Command
 * @subpackage      Gateway
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Reward\Command\Gateway;

use Crowdfunding\Reward\Reward;

/**
 * Contract between database drivers and gateway objects.
 *
 * @package         Crowdfunding\Reward\Command
 * @subpackage      Gateway
 */
interface UpdateDistributedGateway
{
    /**
     * Update reward funds.
     *
     * @param Reward $reward
     */
    public function update(Reward $reward);
}
