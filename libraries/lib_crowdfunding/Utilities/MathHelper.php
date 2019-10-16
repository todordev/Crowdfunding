<?php
/**
 * @package      Crowdfunding
 * @subpackage   Utilities
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Utilities;

/**
 * Mathematical helper.
 *
 * @package     Crowdfunding
 * @subpackage  Utilities
 */
abstract class MathHelper
{
    /**
     * Calculate percentage.
     *
     * @param float $goal
     * @param float $funded
     *
     * @return float|int
     */
    public static function calculatePercent($goal, $funded)
    {
        $percents = 0;
        if ($goal > 0) {
            $percents = round(($funded / $goal) * 100, 2);
        }

        return $percents;
    }
}
