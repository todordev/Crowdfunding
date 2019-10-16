<?php
/**
 * @package     Crowdfunding\UnitTest
 * @subpackage  Currencies
 * @author      Todor Iliev
 * @copyright   Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace CrowdfundingMock\Currency\Gateway;

use Prism\Database\JoomlaDatabase;
use Prism\Database\Request\Request;
use Crowdfunding\Currency\Gateway\CurrencyGateway;

class JoomlaGateway extends JoomlaDatabase implements CurrencyGateway
{
    public function fetch(Request $request)
    {
        $jsonData = file_get_contents(PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER.'currency.json');
        return json_decode($jsonData, true);
    }

    public function fetchById($id, Request $request = null)
    {
        $jsonData = file_get_contents(PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER.'currency.json');
        return json_decode($jsonData, true);
    }

    public function fetchCollection(Request $request)
    {
        $jsonData = file_get_contents(PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER.'currencies.json');
        return json_decode($jsonData, true);
    }
}