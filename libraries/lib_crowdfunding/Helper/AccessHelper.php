<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Helper;

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides methods used in the process of checking users access.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
trait AccessHelper
{
    /**
     * Check the access view.
     *
     * <code>
     * $currencyId = 1;
     * $currency   = $this->getCurrency($currencyId);
     * </code>
     *
     * @param \JApplicationCms $app
     * @param \JUser $user
     *
     * @return bool
     */
    protected function canView(\JApplicationCms $app, \JUser $user)
    {
        $activeMenu = $app->getMenu()->getActive();
        $groups     = $user->getAuthorisedViewLevels();

        $canView    = false;
        if ($activeMenu !== null) {
            $canView = in_array((int)$activeMenu->access, $groups, true);
        }

        return $canView;
    }
}
