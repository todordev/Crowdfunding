<?php
/**
 * @package      Crowdfunding
 * @subpackage   Payments
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Payment;

use Prism;
use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage payment session.
 * The session is used for storing data in the process of requests between application and payment services.
 *
 * @package      Crowdfunding
 * @subpackage   Payments
 *
 * @deprecated v2.6.6
 */
class Session extends Prism\Database\Table
{
    protected $id;
    protected $user_id;
    protected $project_id;
    protected $reward_id;
    protected $record_date;
    protected $order_id;
    protected $gateway;
    protected $auser_id;
    protected $session_id;

    /**
     * @var Registry
     */
    protected $gateway_data;

    protected $intention_id;

    /**
     * This is a unique string where is stored a unique key from a payment gateway.
     * That can be transaction ID, token,...
     *
     * @var mixed
     */
    protected $unique_key;

    /**
     * Initialize the object.
     *
     * @param \JDatabaseDriver $db
     */
    public function __construct(\JDatabaseDriver $db = null)
    {
        parent::__construct($db);

        $this->gateway_data = new Registry;
    }

    /**
     * Load country data from database.
     *
     * <code>
     * $keys = array(
     *  "project_id" = 1,
     *  "intention_id" = 2
     * );
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($keys);
     * </code>
     *
     * @param array $keys
     * @param array $options
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     */
    public function load($keys, array $options = array())
    {
        if (!$keys) {
            throw new \UnexpectedValueException(\JText::_('LIB_CROWDFUNDING_INVALID_PAYMENTSESSION_KEYS'));
        }

        $query = $this->db->getQuery(true);
        $query
            ->select(
                'a.id, a.user_id, a.project_id, a.reward_id, a.record_date, a.order_id, ' .
                'a.unique_key, a.gateway, a.gateway_data, a.auser_id, a.session_id, a.intention_id'
            )
            ->from($this->db->quoteName('#__crowdf_paymentsessions', 'a'));

        if (!is_array($keys)) {
            $query->where('a.id = ' . (int)$keys);
        } else {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.' . $key) . '=' . $this->db->quote($value));
            }
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        // Prepare gateway data.
        if (!empty($result['gateway_data'])) {
            $this->gateway_data = new Registry($result['gateway_data']);
        } else {
            $this->gateway_data = new Registry();
        }

        $this->bind($result, array('gateway_data'));
    }

    /**
     * Store the data in database.
     *
     * <code>
     * $data = (
     *  "user_id"  => 2,
     *  "intention_id" => 3
     * );
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->bind($data);
     * $paymentSession->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function insertObject()
    {
        $recordDate   = (!$this->record_date) ? 'NULL' : $this->db->quote($this->record_date);

        // Convert the gateway data to JSON format.
        $gatewayData = 'NULL';
        if ($this->gateway_data instanceof Registry) {
            $gatewayData  = $this->gateway_data->toString();
        }

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName('#__crowdf_paymentsessions'))
            ->set($this->db->quoteName('user_id') . '=' . $this->db->quote($this->user_id))
            ->set($this->db->quoteName('project_id') . '=' . $this->db->quote($this->project_id))
            ->set($this->db->quoteName('reward_id') . '=' . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName('record_date') . '=' . $recordDate)
            ->set($this->db->quoteName('unique_key') . '=' . $this->db->quote($this->unique_key))
            ->set($this->db->quoteName('order_id') . '=' . $this->db->quote($this->order_id))
            ->set($this->db->quoteName('gateway') . '=' . $this->db->quote($this->gateway))
            ->set($this->db->quoteName('gateway_data') . '=' . $this->db->quote($gatewayData))
            ->set($this->db->quoteName('auser_id') . '=' . $this->db->quote($this->auser_id))
            ->set($this->db->quoteName('session_id') . '=' . $this->db->quote($this->session_id))
            ->set($this->db->quoteName('intention_id') . '=' . $this->db->quote($this->intention_id));

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    protected function updateObject()
    {
        // Convert the gateway data to JSON format.
        $gatewayData = 'NULL';
        if ($this->gateway_data instanceof Registry) {
            $gatewayData  = $this->gateway_data->toString();
        }

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__crowdf_paymentsessions'))
            ->set($this->db->quoteName('user_id') . '=' . $this->db->quote($this->user_id))
            ->set($this->db->quoteName('project_id') . '=' . $this->db->quote($this->project_id))
            ->set($this->db->quoteName('reward_id') . '=' . $this->db->quote($this->reward_id))
            ->set($this->db->quoteName('record_date') . '=' . $this->db->quote($this->record_date))
            ->set($this->db->quoteName('unique_key') . '=' . $this->db->quote($this->unique_key))
            ->set($this->db->quoteName('order_id') . '=' . $this->db->quote($this->order_id))
            ->set($this->db->quoteName('gateway') . '=' . $this->db->quote($this->gateway))
            ->set($this->db->quoteName('gateway_data') . '=' . $this->db->quote($gatewayData))
            ->set($this->db->quoteName('auser_id') . '=' . $this->db->quote($this->auser_id))
            ->set($this->db->quoteName('session_id') . '=' . $this->db->quote($this->session_id))
            ->set($this->db->quoteName('intention_id') . '=' . $this->db->quote($this->intention_id))
            ->where($this->db->quoteName('id') . '=' . $this->db->quote($this->id));

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * @return mixed|string
     *
     * @deprecated v2.7
     */
    protected function encodeDataToJson()
    {
        if ($this->gateway_data === null or !is_array($this->gateway_data)) {
            $this->gateway_data = array();
        }
        return json_encode($this->gateway_data);
    }

    /**
     * Remove a payment session record from database.
     *
     * <code>
     * $keys = (
     *  "user_id"  => 2,
     *  "intention_id" => 3
     * );
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($keys);
     * $paymentSession->delete();
     * </code>
     *
     * @throws \RuntimeException
     */
    public function delete()
    {
        $query = $this->db->getQuery(true);
        $query
            ->delete($this->db->quoteName('#__crowdf_paymentsessions'))
            ->where($this->db->quoteName('id') . '=' . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->reset();
    }

    /**
     * Return payment session ID.
     *
     * <code>
     * $keys = (
     *  "user_id"  => 2,
     *  "intention_id" => 3
     * );
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($keys);
     *
     * if (!$paymentSession->getId()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Set user ID to the object.
     *
     * <code>
     * $paymentSessionId = 1;
     * $userId = 2;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setUserId($userId);
     * </code>
     *
     * @param int $userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->user_id = (int)$userId;

        return $this;
    }

    /**
     * Return user ID which is part of current payment session.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $userId = $paymentSession->getUserId();
     * </code>
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Set the ID of the anonymous user.
     *
     * <code>
     * $paymentSessionId = 1;
     * $anonymousUserId = 2;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setAnonymousUserId($anonymousUserId);
     * </code>
     *
     * @param string $auserId
     *
     * @return self
     */
    public function setAnonymousUserId($auserId)
    {
        $this->auser_id = (string)$auserId;

        return $this;
    }

    /**
     * Return the ID (hash) of anonymous user which is part of current payment session.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $anonymousUserId = $paymentSession->getAnonymousUserId();
     * </code>
     *
     * @return string
     */
    public function getAnonymousUserId()
    {
        return (string)$this->auser_id;
    }

    /**
     * Set a project ID.
     *
     * <code>
     * $paymentSessionId = 1;
     * $projectId = 2;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setProjectId($projectId);
     * </code>
     *
     * @param int $projectId
     *
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->project_id = (int)$projectId;

        return $this;
    }

    /**
     * Return project ID.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $projectId = $paymentSession->getProjectId();
     * </code>
     *
     * @return int
     */
    public function getProjectId()
    {
        return (int)$this->project_id;
    }

    /**
     * Set a reward ID.
     *
     * <code>
     * $paymentSessionId = 1;
     * $rewardId = 2;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setRewardId($rewardId);
     * </code>
     *
     * @param int $rewardId
     *
     * @return self
     */
    public function setRewardId($rewardId)
    {
        $this->reward_id = (int)$rewardId;

        return $this;
    }

    /**
     * Return reward ID.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $rewardId = $paymentSession->getRewardId();
     * </code>
     *
     * @return int
     */
    public function getRewardId()
    {
        return (int)$this->reward_id;
    }

    /**
     * Set the date of the database record.
     *
     * <code>
     * $paymentSessionId = 1;
     * $date = "01-01-2014";
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setRecordDateId($date);
     * </code>
     *
     * @param string $recordDate
     *
     * @return self
     */
    public function setRecordDate($recordDate)
    {
        $this->record_date = $recordDate;

        return $this;
    }

    /**
     * Return the date of current record.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $date = $paymentSession->getRecordDate();
     * </code>
     *
     * @return string
     */
    public function getRecordDate()
    {
        return $this->record_date;
    }

    /**
     * Set the name of the payment gateway.
     *
     * <code>
     * $paymentSessionId = 1;
     * $name = "PayPal";
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setGateway($name);
     * </code>
     *
     * @param string $gateway
     *
     * @return self
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;

        return $this;
    }

    /**
     * Return the name of payment service.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $name = $paymentSession->getGateway();
     * </code>
     *
     * @return string
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Return gateway data.
     *
     * <code>
     * $paymentSessionId  = 1;
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $gatewayData = $paymentSession->getGatewayData();
     * </code>
     *
     * @return Registry
     */
    public function getGatewayData()
    {
        return $this->gateway_data;
    }

    /**
     * Set a gateway data.
     *
     * <code>
     * $paymentSessionId  = 1;
     * $data        = array(
     *    "token" => "TOKEN1234"
     * );
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setGatewayData($data);
     * </code>
     *
     * @param array $data
     *
     * @return self
     */
    public function setGatewayData(array $data)
    {
        $this->gateway_data = new Registry($data);

        return $this;
    }

    /**
     * Return a value of a gateway data.
     *
     * <code>
     * $paymentSessionId  = 1;
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $gateway = $paymentSession->getData("token");
     * </code>
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getData($key, $default = null)
    {
        return ($this->gateway_data instanceof Registry) ? $this->gateway_data->get($key) : $default;
    }

    /**
     * Set a gateway data value.
     *
     * <code>
     * $paymentSessionId  = 1;
     * $token        = "TOKEN1234";
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setData("token", $token);
     * </code>
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function setData($key, $value)
    {
        $this->gateway_data->set($key, $value);

        return $this;
    }

    /**
     * Return a unique key that comes from a payment gateway.
     * That can be transaction ID, token,...
     *
     * <code>
     * $paymentSessionId  = 1;
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $uniqueKey = $intention->getUniqueKey();
     * </code>
     *
     * @return string
     */
    public function getUniqueKey()
    {
        return $this->unique_key;
    }

    /**
     * Set unique key that comes from a payment gateway.
     * That can be transaction ID, token,...
     *
     * <code>
     * $paymentSessionId  = 1;
     * $token        = "TOKEN1234";
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setUniqueKey($token);
     * </code>
     *
     * @param string $key
     * @return self
     */
    public function setUniqueKey($key)
    {
        $this->unique_key = $key;

        return $this;
    }

    /**
     * Set unique key that comes from a payment gateway.
     * That can be transaction ID, token,...
     *
     * <code>
     * $paymentSessionId  = 1;
     * $token        = "TOKEN1234";
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setUniqueKey($token);
     * $paymentSession->storeUniqueKey();
     * </code>
     *
     * @throws \RuntimeException
     *
     * @return self
     */
    public function storeUniqueKey()
    {
        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__crowdf_paymentsessions'))
            ->set($this->db->quoteName('unique_key') . '=' . $this->db->quote($this->unique_key))
            ->where($this->db->quoteName('id') . '=' . $this->db->quote($this->id));

        $this->db->setQuery($query);
        $this->db->execute();

        return $this;
    }

    /**
     * Get order ID.
     *
     * <code>
     * $paymentSessionId  = 1;
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $orderId = $intention->getOrderId();
     * </code>
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * Set the order ID.
     *
     * <code>
     * $paymentSessionId  = 1;
     * $orderId           = "ORDER123";
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setOrderId($orderId);
     * </code>
     *
     * @param string $orderId
     * @return self
     */
    public function setOrderId($orderId)
    {
        $this->order_id = $orderId;

        return $this;
    }

    /**
     * Store the order ID to database.
     *
     * <code>
     * $paymentSessionId  = 1;
     * $orderId           = "ORDER123";
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setOrderId($orderId);
     * $paymentSession->storeOrderId();
     * </code>
     *
     * @throws \RuntimeException
     *
     * @return self
     */
    public function storeOrderId()
    {
        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__crowdf_paymentsessions'))
            ->set($this->db->quoteName('order_id') . '=' . $this->db->quote($this->order_id))
            ->where($this->db->quoteName('id') . '=' . $this->db->quote($this->id));

        $this->db->setQuery($query);
        $this->db->execute();

        return $this;
    }

    /**
     * Store data used during process of payment.
     *
     * <code>
     * $paymentSessionId  = 1;
     * $orderId           = "ORDER2";
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setData('order_id', $orderId);
     * $paymentSession->storeData();
     * </code>
     *
     * @throws \RuntimeException
     *
     * @return self
     */
    public function storeData()
    {
        // Convert the gateway data to JSON format.
        $gatewayData = 'NULL';
        if ($this->gateway_data instanceof Registry) {
            $gatewayData  = $this->gateway_data->toString();
        }

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__crowdf_paymentsessions'))
            ->set($this->db->quoteName('gateway_data') . '=' . $this->db->quote($gatewayData))
            ->where($this->db->quoteName('id') . '=' . $this->db->quote($this->id));

        $this->db->setQuery($query);
        $this->db->execute();

        return $this;
    }

    /**
     * Set intention ID.
     *
     * <code>
     * $paymentSessionId = 1;
     * $intentionId = 2;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setIntentionId($intentionId);
     * </code>
     *
     * @param int $intentionId
     *
     * @return self
     */
    public function setIntentionId($intentionId)
    {
        $this->intention_id = $intentionId;

        return $this;
    }

    /**
     * Return the ID of intention.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $intentionId = $paymentSession->getIntentionId();
     * </code>
     *
     * @return int
     */
    public function getIntentionId()
    {
        return (int)$this->intention_id;
    }

    /**
     * Return session ID.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($intentionId);
     *
     * $sessionId = $paymentSession->getSessionId();
     * </code>
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * Set session ID.
     *
     * <code>
     * $paymentSessionId = 1;
     * $sessionId        = "SESSION_ID_1234";
     *
     * $paymentSession    = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * $paymentSession->setSessionId($sessionId);
     * </code>
     *
     * @param string $sessionId
     * @return self
     */
    public function setSessionId($sessionId)
    {
        $this->session_id = $sessionId;

        return $this;
    }

    /**
     * Check if payment session has been handled from anonymous user.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $paymentSession   = new Crowdfunding\Payment\Session(\JFactory::getDbo());
     * $paymentSession->load($paymentSessionId);
     *
     * if (!$paymentSession->isAnonymous()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isAnonymous()
    {
        return (bool)$this->auser_id;
    }
}
