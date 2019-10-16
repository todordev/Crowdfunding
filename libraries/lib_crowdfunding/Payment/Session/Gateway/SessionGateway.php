<?php
/**
 * @package         Crowdfunding
 * @subpackage      Payments\Gateways
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Payment\Session\Gateway;

use Prism\Domain\Fetcher;
use Crowdfunding\Payment\Session\Session;
use Crowdfunding\Payment\Session\ServiceData;

/**
 * Contract between database drivers and gateway objects.
 *
 * @package         Crowdfunding
 * @subpackage      Payments\Gateways
 */
interface SessionGateway extends Fetcher
{
    public function insertObject(Session $object);
    public function updateObject(Session $object);
    public function deleteObject(Session $object);
    public function storeServiceData(ServiceData $service);
}
