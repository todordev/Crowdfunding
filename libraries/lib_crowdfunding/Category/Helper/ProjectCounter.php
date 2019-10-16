<?php
/**
 * @package      Crowdfunding\Category
 * @subpackage   Helper
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Category\Helper;

use Prism\Constants;
use Crowdfunding\Category\Helper\Gateway\ProjectCounterGateway;

/**
 * Count projects in categories.
 *
 * @package      Crowdfunding\Category
 * @subpackage   Helper
 */
class ProjectCounter
{
    /**
     * @var ProjectCounterGateway
     */
    protected $gateway;

    /**
     * Initialize the object.
     *
     * @param ProjectCounterGateway $gateway
     */
    public function __construct(ProjectCounterGateway $gateway)
    {
        $this->gateway  = $gateway;
    }

    /**
     * @param array $ids
     * @param int   $state
     * @param int   $approved
     *
     * @return array
     */
    public function count(array $ids, $state = Constants::PUBLISHED, $approved = Constants::APPROVED)
    {
        return $this->gateway->count($ids, $state, $approved);
    }
}
