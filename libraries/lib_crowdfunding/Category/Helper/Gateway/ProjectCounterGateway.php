<?php
/**
 * @package         Crowdfunding\Category\Helper
 * @subpackage      Gateway
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Category\Helper\Gateway;

use Prism\Constants;

/**
 * Count the projects in the categories.
 *
 * @package         Crowdfunding\Category\Helper
 * @subpackage      Gateway
 */
interface ProjectCounterGateway
{
    /**
     * Returns the number of projects.
     *
     * @param array $ids
     * @param int $state
     * @param int $approved
     *
     * @return array
     */
    public function count(array $ids, $state = Constants::PUBLISHED, $approved = Constants::APPROVED);
}
