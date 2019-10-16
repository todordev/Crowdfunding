<?php
/**
 * @package      Crowdfunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Statistics\Transactions;

use Prism\Database;

defined('JPATH_PLATFORM') or die;

/**
 * This is a base class for transactions statistics.
 *
 * @package      Crowdfunding\Statistics
 * @subpackage   Transactions
 */
abstract class Base extends Database\Collection
{
    protected function getQuery()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                'a.id, a.txn_date, a.txn_amount, a.txn_currency, a.txn_id, a.project_id, a.fee, ' .
                'b.title'
            )
            ->from($this->db->quoteName('#__crowdf_transactions', 'a'))
            ->leftJoin($this->db->quoteName('#__crowdf_projects', 'b') . ' ON a.project_id = b.id');

        return $query;
    }
}
