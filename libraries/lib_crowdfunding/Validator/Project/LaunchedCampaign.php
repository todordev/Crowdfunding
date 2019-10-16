<?php
/**
 * @package      Crowdfunding\Projects
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Validator\Project;

use Prism\Validator\ValidatorInterface;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality to check if campaign has been launched.
 *
 * @package      Crowdfunding\Projects
 * @subpackage   Validators
 */
class LaunchedCampaign implements ValidatorInterface
{
    protected $startDate;
    protected $endDate;

    /**
     * Initialize the object.
     *
     * <code>
     * $startDate = "2015-01-01";
     * $endDate = "2015-01-30";
     *
     * $period = new Crowdfunding\Validator\Project\LaunchedCampaign($startDate, $endDate);
     * </code>
     *
     * @param string $startDate
     * @param string $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * Check if project has been launched.
     *
     * <code>
     * $startDate = "2015-01-01";
     * $endDate = "2015-01-30";
     *
     * $campaign = new Crowdfunding\Validator\Project\LaunchedCampaign($startDate, $endDate);
     * if(!$campaign->isValid()) {
     * ......
     * }
     * </code>
     *
     * @return bool
     */
    public function isValid()
    {
        // Get interval between starting and ending date.
        $fundingStartDate = new \JDate($this->startDate);
        $fundingEndDate   = new \JDate($this->endDate);
        $interval         = $fundingStartDate->diff($fundingEndDate);

        $days = (int)$interval->format('%r%a');

        return (bool)($days > 0);
    }
}
