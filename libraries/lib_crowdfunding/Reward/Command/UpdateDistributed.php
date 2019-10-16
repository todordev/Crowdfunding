<?php
/**
 * @package      Crowdfunding\Reward
 * @subpackage   Command
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Reward\Command;

use Prism\Command\Command;
use Crowdfunding\Reward\Reward;
use Crowdfunding\Reward\Command\Gateway\UpdateDistributedGateway;

/**
 * Update reward funds command.
 *
 * @package      Crowdfunding\Reward
 * @subpackage   Command
 */
class UpdateDistributed implements Command
{
    /**
     * @var Reward
     */
    protected $reward;

    /**
     * @var UpdateDistributedGateway
     */
    protected $gateway;

    /**
     * Update reward funds command constructor.
     *
     * @param Reward $reward
     */
    public function __construct(Reward $reward)
    {
        $this->reward  = $reward;
    }

    /**
     * @param UpdateDistributedGateway $gateway
     *
     * @return self
     */
    public function setGateway(UpdateDistributedGateway $gateway)
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function handle()
    {
        $this->gateway->update($this->reward);
    }
}
