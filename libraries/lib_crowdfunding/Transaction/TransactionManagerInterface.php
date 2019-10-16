<?php
/**
 * @package         Crowdfunding
 * @subpackage      Transactions
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Transaction;

defined('JPATH_PLATFORM') or die;

/**
 * Transaction manager interface.
 *
 * @package         Crowdfunding
 * @subpackage      Transactions
 */
interface TransactionManagerInterface
{
    public function setTransaction(Transaction $transaction);
    public function getTransaction();
    public function process($context, array $options = array());
}
