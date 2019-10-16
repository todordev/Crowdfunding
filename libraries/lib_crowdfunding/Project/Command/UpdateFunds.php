<?php
/**
 * @package      Crowdfunding\Project
 * @subpackage   Command
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project\Command;

use Prism\Command\Command;
use Crowdfunding\Project\Project;
use Crowdfunding\Project\Command\Gateway\UpdateFundsGateway;

/**
 * Update project funds command.
 *
 * @package      Crowdfunding\Project
 * @subpackage   Command
 */
class UpdateFunds implements Command
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * @var UpdateFundsGateway
     */
    protected $gateway;

    /**
     * Update project funds command constructor.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project  = $project;
    }

    /**
     * @param UpdateFundsGateway $gateway
     *
     * @return self
     */
    public function setGateway(UpdateFundsGateway $gateway)
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function handle()
    {
        $this->gateway->update($this->project);
    }
}
