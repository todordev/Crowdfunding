<?php
/**
 * @package         Crowdfunding\Reward
 * @subpackage      Gateway
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Reward\Gateway;

use Prism\Domain\RichFetcher;
use Crowdfunding\Reward\Reward;

/**
 * Contract between database drivers and gateway objects.
 *
 * @package         Crowdfunding\Project
 * @subpackage      Gateway
 */
interface RewardGateway extends RichFetcher
{
    /**
     * Insert a record to database.
     *
     * @param Reward $object
     *
     * @return mixed
     */
    public function insertObject(Reward $object);

    /**
     * Update a record in database.
     *
     * @param Reward $object
     *
     * @return mixed
     */
    public function updateObject(Reward $object);
}
