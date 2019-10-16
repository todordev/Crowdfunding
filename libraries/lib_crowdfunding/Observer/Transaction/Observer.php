<?php
/**
 * @package         Crowdfunding
 * @subpackage      Observers
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Observer\Transaction;

use Crowdfunding\Transaction\Transaction;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides methods used in transaction observers.
 *
 * @package     Crowdfunding
 * @subpackage  Observers
 */
abstract class Observer implements \JObserverInterface
{
    /**
     * The observed object.
     *
     * @var    \JObservableInterface
     * @since  3.1.2
     */
    protected $transaction;

    /**
     * Constructor: Associates to $transaction $this observer
     *
     * @param   \JObservableInterface $transaction Object to be observed.
     *
     * @since   3.1.2
     */
    public function __construct(\JObservableInterface $transaction)
    {
        $transaction->attachObserver($this);
        $this->transaction = $transaction;
    }

    /**
     * Pre-processor for $transactionManager->process($context, $points, $options).
     *
     * @param   string   $context
     * @param   Transaction   $transaction
     * @param   array    $options
     *
     * @return  void
     */
    public function onBeforeProcessTransaction($context, Transaction $transaction, array $options = array())
    {
    }

    /**
     * Post-processor for $transactionManager->process($context, $transaction, $options).
     *
     * @param   string   $context
     * @param   Transaction   $transaction
     * @param   array    $options
     *
     * @return  void
     */
    public function onAfterProcessTransaction($context, Transaction $transaction, array $options = array())
    {
    }
}
