<?php
/**
 * @package         Crowdfunding\Project\Command
 * @subpackage      Gateway
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project\Command\Gateway;

use Crowdfunding\Project\Project;

/**
 * Contract between database drivers and gateway objects.
 *
 * @package         Crowdfunding\Project\Command
 * @subpackage      Gateway
 */
interface UpdateFundsGateway
{
    /**
     * Update project funds.
     *
     * @param Project $project
     */
    public function update(Project $project);
}
