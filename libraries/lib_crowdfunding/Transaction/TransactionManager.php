<?php
/**
 * @package         Crowdfunding
 * @subpackage      Transactions
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Transaction;

use Prism\Observer\Observable;

defined('JPATH_PLATFORM') or die;

/**
 * This is an object that represents user transaction.
 *
 * @package         Crowdfunding
 * @subpackage      Transactions
 */
class TransactionManager extends Observable implements TransactionManagerInterface
{
    /**
     * @var Transaction
     */
    protected $transaction;

    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }
    
    /**
     * Process a transaction.
     *
     * <code>
     * $context = "com_crowdfunding.transaction.process";
     *
     * $data = array(
     *    'txn_id' => 'TXN123456',
     *    'txn_amount' => '100',
     *    'txn_currency' => 'EUR',
     * //....
     * );
     *
     * // Create user transaction object based.
     * $transaction  = new Crowdfunding\Transaction\Transaction(\JFactory::getDbo());
     * $transaction->bind($data);
     *
     * $transactionManager = new Crowdfunding\Transaction\TransactionManager(\JFactory::getDbo());
     * $transactionManager->setTransaction($transaction);
     *
     * if ($transactionManager->process($context, $options)) {
     * // ...
     * }
     * </code>
     *
     * @param string $context
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function process($context, array $options = array())
    {
        if (!($this->transaction instanceof Transaction)) {
            throw new \UnexpectedValueException('It is missing transaction object.');
        }

        // Implement JObservableInterface: Pre-processing by observers
        $this->observers->update('onBeforeProcessTransaction', array($context, &$this->transaction, &$options));

        $this->transaction->store();

        // Implement JObservableInterface: Post-processing by observers
        $this->observers->update('onAfterProcessTransaction', array($context, &$this->transaction, &$options));
    }

    /**
     * Process a transaction.
     *
     * <code>
     * $context = "com_crowdfunding.transaction.process";
     *
     * $data = array(
     *    'txn_id' => 'TXN123456',
     *    'txn_amount' => '100',
     *    'txn_currency' => 'EUR',
     * //....
     * );
     *
     * // Create user transaction object based.
     * $transaction  = new Crowdfunding\Transaction\Transaction(\JFactory::getDbo());
     * $transaction->bind($data);
     *
     * $transactionManager = new Crowdfunding\Transaction\TransactionManager(\JFactory::getDbo());
     * $transactionManager->setTransaction($transaction);
     *
     * if ($transactionManager->process($context, $options)) {
     * // ...
     * }
     * </code>
     *
     * @param string $context
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function changeStatus($context, $options)
    {
        if (!($this->transaction instanceof Transaction)) {
            throw new \UnexpectedValueException('It is missing transaction object.');
        }

        if (!array_key_exists('old_status', $options)) {
            throw new \UnexpectedValueException('It is missing old status value.');
        }

        if (!array_key_exists('new_status', $options)) {
            throw new \UnexpectedValueException('It is missing new status value.');
        }

        // Implement JObservableInterface: Pre-processing by observers
        $this->observers->update('onBeforeTransactionStatusChange', array($context, &$this->transaction, $options));

        // Change the status.
        $this->transaction->setStatus($options['new_status']);
        $this->transaction->updateStatus();

        // Implement JObservableInterface: Post-processing by observers
        $this->observers->update('onAfterTransactionStatusChange', array($context, &$this->transaction, $options));
    }
}
