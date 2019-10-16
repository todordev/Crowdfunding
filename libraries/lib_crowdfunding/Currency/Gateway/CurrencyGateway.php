<?php
/**
 * @package         Crowdfunding
 * @subpackage      Currencies\Gateways
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Currency\Gateway;

use Prism\Domain\RichFetcher;

/**
 * Contract between database drivers and gateway objects.
 *
 * @package         Crowdfunding
 * @subpackage      Currencies\Gateways
 */
interface CurrencyGateway extends RichFetcher
{
}
