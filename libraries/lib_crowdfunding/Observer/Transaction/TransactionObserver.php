<?php
/**
 * @package      Crowdfunding\Observer
 * @subpackage   Transaction
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Observer\Transaction;

use Crowdfunding\Project\Command\UpdateFunds;
use Crowdfunding\Project\Command\Gateway\Joomla\UpdateFunds as UpdateFundsGateway;
use Crowdfunding\Reward\Command\UpdateDistributed;
use Prism\Constants;
use Prism\Container;
use Crowdfunding\Reward\Reward;
use Joomla\Utilities\ArrayHelper;
use Crowdfunding\Transaction\Transaction;
use Crowdfunding\Container\Helper\Reward as RewardHelper;
use Crowdfunding\Container\Helper\Project as ProjectHelper;
use Crowdfunding\Reward\Gateway\JoomlaGateway as RewardGateway;
use Crowdfunding\Project\Gateway\JoomlaGateway as ProjectGateway;
use Crowdfunding\Reward\Command\Gateway\Joomla\UpdateDistributed as UpdateDistributedGateway;

/**
 * Transaction observer.
 *
 * @package      Crowdfunding\Observer
 * @subpackage   Transaction
 */
class TransactionObserver extends Observer
{
    /**
     * Context that are allowed to be processed.
     *
     * @var array
     */
    protected $allowedContext = array('com_crowdfunding.transaction', 'com_crowdfunding.payment');

    /**
     * The pattern for this table's TypeAlias
     *
     * @var    string
     * @since  3.1.2
     */
    protected $typeAliasPattern;

    /**
     * Creates the associated observer instance and attaches it to the $observableObject
     * $typeAlias can be of the form '{variableName}.type', automatically replacing {variableName} with table-instance variables variableName
     *
     * @param   \JObservableInterface $observableObject The subject object to be observed
     * @param   array                $params           ( 'typeAlias' => $typeAlias )
     *
     * @throws  \InvalidArgumentException
     * @return  self
     *
     * @since   3.1.2
     */
    public static function createObserver(\JObservableInterface $observableObject, $params = array())
    {
        $observer = new self($observableObject);
        $observer->typeAliasPattern = ArrayHelper::getValue($params, 'typeAlias');

        return $observer;
    }

    /**
     * Pre-processor for $transactionManager->process($context, $options)
     *
     * @param   string        $context
     * @param   Transaction   $transaction
     * @param   array         $options
     *
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     * @throws  \OutOfBoundsException
     *
     * @return  void
     */
    public function onAfterProcessTransaction($context, Transaction $transaction, array $options = array())
    {
        // Check for allowed context.
        if (!in_array($context, $this->allowedContext, true)) {
            return;
        }

        $completedOrPending            = Constants::PAYMENT_STATUS_COMPLETED_BIT | Constants::PAYMENT_STATUS_PENDING_BIT;
        $canceledOrRefundedOrFailed    = Constants::PAYMENT_STATUS_CANCELED_BIT | Constants::PAYMENT_STATUS_REFUNDED_BIT | Constants::PAYMENT_STATUS_FAILED_BIT;

        $statuses = array(
            'completed' => Constants::PAYMENT_STATUS_COMPLETED_BIT,
            'pending'   => Constants::PAYMENT_STATUS_PENDING_BIT,
            'canceled'  => Constants::PAYMENT_STATUS_CANCELED_BIT,
            'refunded'  => Constants::PAYMENT_STATUS_REFUNDED_BIT,
            'failed'    => Constants::PAYMENT_STATUS_FAILED_BIT
        );

        $oldStatus     = ArrayHelper::getValue($options, 'old_status');
        $newStatus     = ArrayHelper::getValue($options, 'new_status');

        $oldStatusBit  = ($oldStatus && array_key_exists($oldStatus, $statuses)) ? $statuses[$oldStatus] : null;
        $newStatusBit  = ($newStatus && array_key_exists($newStatus, $statuses)) ? $statuses[$newStatus] : null;

        // Check if it is new record.
        $isNew = false;
        if ($oldStatusBit === null && $newStatusBit !== null) {
            $isNew = true;
        }

        $container        = Container::getContainer();

        $rewardHelper     = new RewardHelper($container);
        $rewardGateway    = new RewardGateway(\JFactory::getDbo());

        $projectHelper    = new ProjectHelper($container);
        $projectGateway   = new ProjectGateway(\JFactory::getDbo());

        // Add funds when create new transaction record, and it is completed and pending.
        if ($isNew && $transaction->getProjectId() > 0 && ($transaction->isCompleted() || $transaction->isPending())) {
            $project = $projectHelper->getProject($transaction->getProjectId(), $projectGateway);

            $project->addFunds($transaction->getAmount());

            // Update the amount in database.
            $updateFundsCommand = new UpdateFunds($project);
            $updateFundsCommand->setGateway(new UpdateFundsGateway(\JFactory::getDbo()));
            $updateFundsCommand->handle();

            if ($transaction->getRewardId()) {
                $reward = $rewardHelper->getReward($transaction->getRewardId(), $rewardGateway, $transaction->getProjectId());
                $this->increaseDistributedReward($transaction, $reward);
            }
        } else {
            // If someone change the status from completed/pending to another one, remove funds.
            if (($completedOrPending & $oldStatusBit) && ($canceledOrRefundedOrFailed & $newStatusBit)) {
                $project = $projectHelper->getProject($transaction->getProjectId(), $projectGateway);

                $project->removeFunds($transaction->getAmount());

                // Update the amount in database.
                $updateFundsCommand = new UpdateFunds($project);
                $updateFundsCommand->setGateway(new UpdateFundsGateway(\JFactory::getDbo()));
                $updateFundsCommand->handle();

                if ($transaction->getRewardId()) {
                    $reward = $rewardHelper->getReward($transaction->getRewardId(), $rewardGateway, $transaction->getProjectId());
                    $this->decreaseDistributedReward($transaction, $reward);
                }
            } // If someone change the status to completed/pending from canceled, refunded or failed, add funds.
            elseif (($canceledOrRefundedOrFailed & $oldStatusBit) && ($completedOrPending & $newStatusBit)) {
                $project = $projectHelper->getProject($transaction->getProjectId(), $projectGateway);

                $project->addFunds($transaction->getAmount());

                // Update the amount in database.
                $updateFundsCommand = new UpdateFunds($project);
                $updateFundsCommand->setGateway(new UpdateFundsGateway(\JFactory::getDbo()));
                $updateFundsCommand->handle();

                if ($transaction->getRewardId()) {
                    $reward = $rewardHelper->getReward($transaction->getRewardId(), $rewardGateway, $transaction->getProjectId());
                    $this->increaseDistributedReward($transaction, $reward);
                }
            }
        }
    }

    /**
     * Pre-processor for $transactionManager->changeStatus($context, $options)
     *
     * @param   string        $context
     * @param   Transaction   $transaction
     * @param   array         $options
     *
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     * @throws  \OutOfBoundsException
     *
     * @return  void
     */
    public function onAfterTransactionStatusChange($context, Transaction $transaction, array $options = array())
    {
        // Check for allowed context.
        if (!in_array($context, $this->allowedContext, true)) {
            return;
        }

        $completedOrPending            = Constants::PAYMENT_STATUS_COMPLETED_BIT | Constants::PAYMENT_STATUS_PENDING_BIT;
        $canceledOrRefundedOrFailed    = Constants::PAYMENT_STATUS_CANCELED_BIT | Constants::PAYMENT_STATUS_REFUNDED_BIT | Constants::PAYMENT_STATUS_FAILED_BIT;

        $statuses = array(
            'completed' => Constants::PAYMENT_STATUS_COMPLETED_BIT,
            'pending'   => Constants::PAYMENT_STATUS_PENDING_BIT,
            'canceled'  => Constants::PAYMENT_STATUS_CANCELED_BIT,
            'refunded'  => Constants::PAYMENT_STATUS_REFUNDED_BIT,
            'failed'    => Constants::PAYMENT_STATUS_FAILED_BIT
        );

        $oldStatus     = ArrayHelper::getValue($options, 'old_status');
        $newStatus     = ArrayHelper::getValue($options, 'new_status');

        $oldStatusBit  = ($oldStatus and array_key_exists($oldStatus, $statuses)) ? $statuses[$oldStatus] : null;
        $newStatusBit  = ($newStatus and array_key_exists($newStatus, $statuses)) ? $statuses[$newStatus] : null;

        // Check if it is new record.
        $container        = Container::getContainer();

        $rewardHelper     = new RewardHelper($container);
        $rewardGateway    = new RewardGateway(\JFactory::getDbo());

        $projectHelper    = new ProjectHelper($container);
        $projectGateway   = new ProjectGateway(\JFactory::getDbo());

        // If someone change the status from completed/pending to another one, remove funds.
        if (($completedOrPending & $oldStatusBit) && ($canceledOrRefundedOrFailed & $newStatusBit)) {
            $project = $projectHelper->getProject($transaction->getProjectId(), $projectGateway);

            $project->removeFunds($transaction->getAmount());

            // Update the amount in database.
            $updateFundsCommand = new UpdateFunds($project);
            $updateFundsCommand->setGateway(new UpdateFundsGateway(\JFactory::getDbo()));
            $updateFundsCommand->handle();

            if ($transaction->getRewardId()) {
                $reward = $rewardHelper->getReward($transaction->getRewardId(), $rewardGateway, $transaction->getProjectId());
                $this->decreaseDistributedReward($transaction, $reward);
            }
        } // If someone change the status to completed/pending from canceled, refunded or failed, add funds.
        elseif (($canceledOrRefundedOrFailed & $oldStatusBit) && ($completedOrPending & $newStatusBit)) {
            $project = $projectHelper->getProject($transaction->getProjectId(), $projectGateway);

            $project->addFunds($transaction->getAmount());

            // Update the amount in database.
            $updateFundsCommand = new UpdateFunds($project);
            $updateFundsCommand->setGateway(new UpdateFundsGateway(\JFactory::getDbo()));
            $updateFundsCommand->handle();

            if ($transaction->getRewardId()) {
                $reward = $rewardHelper->getReward($transaction->getRewardId(), $rewardGateway, $transaction->getProjectId());
                $this->increaseDistributedReward($transaction, $reward);
            }
        }
    }

    /**
     * Increase the number of distributed to a user rewards.
     *
     * @param Transaction $transaction
     * @param Reward|null $reward
     *
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     *
     * @return void
     */
    protected function increaseDistributedReward(Transaction $transaction, Reward $reward = null)
    {
        // Check for valid reward.
        if ($reward === null || !$reward->getId()) {
            return;
        }

        // Check for valida amount between reward value and payed by user
        $txnAmount = $transaction->getAmount();
        if ($txnAmount < $reward->getAmount()) {
            return;
        }

        // Check for available rewards.
        if ($reward->isLimited() && !$reward->hasAvailable()) {
            return;
        }

        // Increase the number of distributed rewards.
        $reward->increaseDistributed();

        $updateDistributedCommand = new UpdateDistributed($reward);
        $updateDistributedCommand->setGateway(new UpdateDistributedGateway(\JFactory::getDbo()));
        $updateDistributedCommand->handle();
    }

    /**
     * Decrease the number of distributed to a user rewards.
     *
     * @param Transaction $transaction
     * @param Reward|null $reward
     *
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     *
     * @return void
     */
    protected function decreaseDistributedReward(Transaction $transaction, Reward $reward = null)
    {
        // Check for valid reward.
        if ($reward === null || !$reward->getId()) {
            return;
        }

        // Check for valida amount between reward value and payed by user
        $txnAmount = $transaction->getAmount();
        if ($txnAmount < $reward->getAmount()) {
            return;
        }

        // Decrease the number of distributed rewards.
        $reward->decreaseDistributed();

        $updateDistributedCommand = new UpdateDistributed($reward);
        $updateDistributedCommand->setGateway(new UpdateDistributedGateway(\JFactory::getDbo()));
        $updateDistributedCommand->handle();
    }
}
