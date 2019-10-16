<?php
/**
 * @package      Crowdfunding
 * @subpackage   Payments
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Payment\Session;

use Prism\Domain\Reset;
use Prism\Domain\Entity;
use Prism\Domain\EntityId;
use Prism\Domain\Resetting;
use Prism\Domain\PropertiesMethods;

/**
 * This class provides functionality that manage payment session.
 * The session is used for storing data in the process of requests between application and payment services.
 *
 * @package      Crowdfunding
 * @subpackage   Payments
 */
class Session implements Entity, Resetting
{
    use EntityId, Reset, PropertiesMethods;

    protected $user_id;
    protected $project_id;
    protected $reward_id;
    protected $record_date;
    protected $auser_id;
    protected $session_id;

    protected $services = array();

    protected $intention_id;

    /**
     * Set user ID to the object.
     *
     * <code>
     * $paymentSessionId = 1;
     * $userId = 2;
     *
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * Set intention ID.
     *
     * <code>
     * $paymentSessionId = 1;
     * $intentionId = 2;
     *
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
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

    /**
     * Set notification data to object parameters.
     *
     * <code>
     * $data = array(
     *     //...
     * );
     *
     * $paymentSession   = new Crowdfunding\Payment\Session\Session();
     * $paymentSession->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored
     */
    public function bind(array $data, array $ignored = array())
    {
        $properties = get_object_vars($this);

        // Parse parameters of the object if they exists.
        if (array_key_exists('services', $data) and array_key_exists('services', $properties) and !in_array('services', $ignored, true)) {
            $this->services = $data['services'];
            unset($data['services']);
        }

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $properties) and !in_array($key, $ignored, true)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Return the services as Registry object.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
     *
     * $services       = $paymentSession->getServices();
     * </code>
     *
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Return an object that keeps the service data.
     *
     * <code>
     * $paymentSessionId = 1;
     *
     * $gateway        = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(JFactory::getDbo());
     * $repository     = new Crowdfunding\Payment\Session\Repository($gateway);
     *
     * $paymentSession = $repository->fetchById($paymentSessionId);
     *
     * $serviceData = $paymentSession->service('paypal');
     * </code>
     *
     * @param string $gateway
     *
     * @return ServiceData
     * @throws \InvalidArgumentException
     */
    public function service($gateway)
    {
        if (!$gateway) {
            throw new \InvalidArgumentException('Invalid gateway name (alias).');
        }

        if (!array_key_exists($gateway, $this->services)) {
            $this->services[$gateway] = new ServiceData;
            $this->services[$gateway]->setId($this->getId());
            $this->services[$gateway]->setAlias($gateway);
        }

        return $this->services[$gateway];
    }
}
