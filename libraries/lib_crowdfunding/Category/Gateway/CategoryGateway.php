<?php
/**
 * @package         Crowdfunding\Category
 * @subpackage      Gateway
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Category\Gateway;

use Prism\Domain\RichFetcher;

/**
 * Contract between database drivers and gateway objects.
 *
 * @package         Crowdfunding\Category
 * @subpackage      Gateway
 */
interface CategoryGateway extends RichFetcher
{
}
