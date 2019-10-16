<?php
/**
 * @package      Crowdfunding\Statistics
 * @subpackage   Transactions
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Statistics\Transactions;

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

\JLoader::register('Crowdfunding\\Statistics\\Transactions\\Base', JPATH_LIBRARIES . '/crowdfunding/statistics/transactions/base.php');

/**
 * This class loads statistics about transactions.
 *
 * @package      Crowdfunding\Statistics
 * @subpackage   Transactions
 */
class Latest extends Base
{
    /**
     * Load latest transaction ordering by record date.
     *
     * <code>
     * $limit = 10;
     *
     * $latest = new Crowdfunding\Statistics\Transactions\Latest(JFactory::getDbo());
     * $latest->load(['limit' => $limit]);
     *
     * foreach ($latest as $project) {
     *      echo $project["txn_amount"];
     *      echo $project["txn_currency"];
     *      echo $project["txn_date"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        $limit = ArrayHelper::getValue($options, 'limit', 5, 'int');

        $query = $this->getQuery();

        $query->order('a.txn_date DESC');

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }
}
