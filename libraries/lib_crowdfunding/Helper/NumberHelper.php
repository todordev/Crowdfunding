<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Helper;

use Prism\Utilities\LocaleHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to return NumberFormatter object.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
trait NumberHelper
{
    /**
     * Return number formatter.
     *
     * <code>
     * $fractionDigits  = 2;
     * $numberFormatter = $this->getNumberFormatter($fractionDigits);
     * </code>
     *
     * @param int $fractionDigits
     *
     * @return \NumberFormatter
     */
    protected function getNumberFormatter($fractionDigits = 2)
    {
        // Prepare decimal pattern.
        $pattern         = '#0';
        if ($fractionDigits > 0) {
            $pattern .= '.' .str_repeat('0', $fractionDigits);
        }

        return LocaleHelper::getNumberFormatter($pattern);
    }
}
