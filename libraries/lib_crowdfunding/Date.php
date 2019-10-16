<?php
/**
 * @package      Crowdfunding
 * @subpackage   Dates
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

use Prism\Validator\Date as DateValidator;
use Prism\Date as PrismDate;

/**
 * This is a class that provides functionality for managing dates.
 *
 * @package      Crowdfunding
 * @subpackage   Dates
 */
class Date extends PrismDate
{
    /**
     * Calculate days left.
     *
     * <code>
     * $fundingDays  = 30;
     * $fundingStart = "01-06-2014";
     * $fundingEnd   = "30-06-2014";
     *
     * $today    = new Crowdfunding\Date();
     * $daysLeft = $today->calculateDaysLeft($fundingDays, $fundingStart, $fundingEnd);
     * </code>
     *
     * @param int    $fundingDays
     * @param string $fundingStart
     * @param string $fundingEnd
     *
     * @return int
     */
    public function calculateDaysLeft($fundingDays, $fundingStart, $fundingEnd)
    {
        // Calculate days left
        $fundingDays = (int)abs($fundingDays);

        if ($fundingDays > 0) {
            $validatorDate = new DateValidator($fundingStart);

            // Validate starting date.
            // If there is not starting date, return number of day.
            if (!$validatorDate->isValid()) {
                return (int)$fundingDays;
            }

            $endingDate = new \DateTime($fundingStart);
            $endingDate->modify('+' . (int)$fundingDays . ' days');
        } else {
            $validatorDate = new DateValidator($fundingEnd);

            // Validate end date.
            // If there is not valid end date, create one.
            if (!$validatorDate->isValid()) {
                $today      = clone $this;
                $today->modify('+1 month');
                $fundingEnd =  $today->format('Y-m-d');
            }

            $endingDate = new \DateTime($fundingEnd);
        }

        $today    = clone $this;
        $interval = $today->diff($endingDate);
        $daysLeft = $interval->format('%r%a');

        if ($daysLeft < 0) {
            $daysLeft = 0;
        }

        return (int)$daysLeft;
    }
}
