<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Helper;

use Prism\Validator;
use Prism\Helper\HelperInterface;
use Prism\Utilities\MathHelper;
use Crowdfunding\Date;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare project funding data - funded_percents, days_left, funding_end.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
class PrepareItemFundingHelper implements HelperInterface
{
    /**
     * Prepare an item parameters.
     *
     * @param \stdClass $data
     * @param array $options
     */
    public function handle(&$data, array $options = array())
    {
        if (is_object($data)) {
            $data->funding_days = (int)$data->funding_days;

            // Calculate end date
            if ($data->funding_days > 0) {
                $startDateValidator = new Validator\Date($data->funding_start);
                if (!$startDateValidator->isValid()) {
                    $data->funding_end = '0000-00-00';
                } else {
                    $fundingStartDate    = new Date($data->funding_start);
                    $fundingEndDate      = $fundingStartDate->calculateEndDate($data->funding_days);
                    $data->funding_end   = $fundingEndDate->format('Y-m-d');
                }

            }

            // Calculate funded percentage.
            $data->funded_percents = MathHelper::calculatePercentage($data->funded, $data->goal, 0);

            // Calculate days left.
            $today           = new Date();
            $data->days_left = $today->calculateDaysLeft($data->funding_days, $data->funding_start, $data->funding_end);
        }
    }
}
