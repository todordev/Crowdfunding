<?php
/**
 * @package      Crowdfunding
 * @subpackage   Payment
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General protected License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Payment;

use Prism\Domain\PropertiesMethods;

/**
 * Value object of the payment transaction.
 *
 * @package      Crowdfunding
 * @subpackage   Payment
 */
class Transaction
{
    use PropertiesMethods;

    protected $receiver_id;
    protected $investor_id;
    protected $project_id;
    protected $reward_id;
    protected $service_provider;
    protected $service_alias;
    protected $txn_id;
    protected $parent_txn_id;
    protected $txn_amount;
    protected $txn_currency;
    protected $txn_status;
    protected $txn_date;
    protected $status_reason;
    protected $extra_data;

    public function __construct(array $data)
    {
        $this->receiver_id      = array_key_exists('receiver_id', $data) ? $data['receiver_id'] : null;
        $this->investor_id      = array_key_exists('investor_id', $data) ? $data['investor_id'] : null;
        $this->project_id       = array_key_exists('project_id', $data) ? $data['project_id'] : null;
        $this->reward_id        = array_key_exists('reward_id', $data) ? $data['reward_id'] : null;
        $this->service_provider = array_key_exists('service_provider', $data) ? $data['service_provider'] : null;
        $this->service_alias    = array_key_exists('service_alias', $data) ? $data['service_alias'] : null;
        $this->txn_id           = array_key_exists('txn_id', $data) ? $data['txn_id'] : null;
        $this->parent_txn_id    = array_key_exists('parent_txn_id', $data) ? $data['parent_txn_id'] : null;
        $this->txn_amount       = array_key_exists('txn_amount', $data) ? $data['txn_amount'] : null;
        $this->txn_currency     = array_key_exists('txn_currency', $data) ? $data['txn_currency'] : null;
        $this->txn_status       = array_key_exists('txn_status', $data) ? $data['txn_status'] : null;
        $this->txn_date         = array_key_exists('txn_date', $data) ? $data['txn_date'] : null;
        $this->status_reason    = array_key_exists('status_reason', $data) ? $data['status_reason'] : null;
        $this->extra_data       = array_key_exists('extra_data', $data) ? $data['extra_data'] : null;
    }

    /**
     * @return int
     */
    public function getReceiverId()
    {
        return $this->receiver_id;
    }

    /**
     * @return int
     */
    public function getInvestorId()
    {
        return $this->investor_id;
    }

    /**
     * @return int
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * @return int
     */
    public function getRewardId()
    {
        return $this->reward_id;
    }

    /**
     * @return string
     */
    public function getServiceProvider()
    {
        return $this->service_provider;
    }

    /**
     * @return string
     */
    public function getServiceAlias()
    {
        return $this->service_alias;
    }

    /**
     * @return string
     */
    public function getTxnId()
    {
        return $this->txn_id;
    }

    /**
     * @return string
     */
    public function getParentTxnId()
    {
        return $this->parent_txn_id;
    }

    /**
     * @return string
     */
    public function getTxnAmount()
    {
        return $this->txn_amount;
    }

    /**
     * @return string
     */
    public function getTxnCurrency()
    {
        return $this->txn_currency;
    }

    /**
     * @return string
     */
    public function getTxnStatus()
    {
        return $this->txn_status;
    }

    /**
     * @return string
     */
    public function getTxnDate()
    {
        return $this->txn_date;
    }

    /**
     * @return $statusReason
     */
    public function getStatusReason()
    {
        return $this->status_reason;
    }

    /**
     * @return array
     */
    public function getExtraData()
    {
        return $this->extra_data;
    }
}
