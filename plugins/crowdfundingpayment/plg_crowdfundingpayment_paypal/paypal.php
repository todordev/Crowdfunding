<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use \Crowdfunding\Payment;
use \Crowdfunding\Transaction\Transaction;
use \Crowdfunding\Transaction\TransactionManager;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\Registry\Registry;
use \PayPal\IPN\PPIPNMessage;
use \PayPal\Core\PPConstants;
use \Prism\Payment\Result as PaymentResult;
use \Crowdfunding\Currency\Gateway\JoomlaGateway as CurrencyGateway;

use \Crowdfunding\Project\Project;
use \Crowdfunding\Project\Gateway\JoomlaGateway as ProjectGateway;
use \Crowdfunding\Reward\Reward;
use \Crowdfunding\Reward\Gateway\JoomlaGateway as RewardGateway;
use \Crowdfunding\Payment\Transaction as PaymentTransaction;
use \Crowdfunding\Payment\Session\Session as PaymentSession;

// no direct access
defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');
jimport('Crowdfundingfinance.init');
jimport('Emailtemplates.init');

JObserverMapper::addObserverClassToClass(
    Crowdfunding\Observer\Transaction\TransactionObserver::class,
    Crowdfunding\Transaction\TransactionManager::class,
    array('typeAlias' => 'com_crowdfunding.payment')
);

/**
 * Crowdfunding PayPal payment plugin.
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgCrowdfundingPaymentPayPal extends Payment\Plugin
{
    protected $payout;

    public function __construct(&$subject, $config = array())
    {
        $this->serviceProvider = 'PayPal';
        $this->serviceAlias    = 'paypal';

        $this->extraDataKeys = array(
            'first_name', 'last_name', 'payer_id', 'payer_status',
            'mc_gross', 'mc_fee', 'mc_currency', 'payment_status', 'payment_type', 'payment_date',
            'txn_type', 'test_ipn', 'ipn_track_id', 'custom', 'protection_eligibility'
        );

        parent::__construct($subject, $config);
    }

    /**
     * This method prepares a payment gateway - buttons, forms,...
     * That gateway will be displayed on the summary page as a payment option.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param stdClass  $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function onProjectPayment($context, $item, $params)
    {
        if (strcmp('com_crowdfunding.payment', $context) !== 0) {
            return null;
        }

        if ($this->app->isClient('administrator')) {
            return null;
        }

        // Check document type
        $docType = JFactory::getDocument()->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        $notifyUrl = $this->getCallbackUrl();
        $returnUrl = $this->getReturnUrl($item->slug, $item->catslug);
        $cancelUrl = $this->getCancelUrl($item->slug, $item->catslug);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_NOTIFY_URL'), $this->debugType, $notifyUrl) : null;
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_RETURN_URL'), $this->debugType, $returnUrl) : null;
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_CANCEL_URL'), $this->debugType, $cancelUrl) : null;

        $html   = array();
        $html[] = '<div class="well">';
        $html[] = '<h4><img src="plugins/crowdfundingpayment/paypal/images/paypal_icon.png" width="36" height="32" alt="PayPal" />' . JText::_($this->textPrefix . '_TITLE') . '</h4>';

        // Prepare payment receiver.
        $paymentReceiverInput  = $this->preparePaymentReceiver($item->id);
        if (!$paymentReceiverInput) {
            $html[] = $this->generateSystemMessage(JText::_($this->textPrefix . '_ERROR_PAYMENT_RECEIVER_MISSING'));
            return implode("\n", $html);
        }

        // Display additional information.
        $html[] = '<p>' . JText::_($this->textPrefix . '_INFO') . '</p>';

        // Start the form.
        if ($this->params->get('paypal_sandbox', 1)) {
            $html[] = '<form action="' . trim(PPConstants::IPN_SANDBOX_ENDPOINT) . '" method="post">';
        } else {
            $html[] = '<form action="' . trim(PPConstants::IPN_LIVE_ENDPOINT) . '" method="post">';
        }

        $html[] = $paymentReceiverInput;

        $html[] = '<input type="hidden" name="cmd" value="_xclick" />';
        $html[] = '<input type="hidden" name="charset" value="utf-8" />';
        $html[] = '<input type="hidden" name="currency_code" value="' . $item->currencyCode . '" />';
        $html[] = '<input type="hidden" name="amount" value="' . $item->amount . '" />';
        $html[] = '<input type="hidden" name="quantity" value="1" />';
        $html[] = '<input type="hidden" name="no_shipping" value="1" />';
        $html[] = '<input type="hidden" name="no_note" value="1" />';
        $html[] = '<input type="hidden" name="tax" value="0" />';

        // Title
        $title  = JText::sprintf($this->textPrefix . '_INVESTING_IN_S', htmlentities($item->title, ENT_QUOTES, 'UTF-8'));
        $html[] = '<input type="hidden" name="item_name" value="' . $title . '" />';

        // Get payment session
        $wizardSessionContext   = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $item->id;
        $wizardSession          = $this->app->getUserState($wizardSessionContext);

        $paymentSession = $this->getPaymentSession(array(
            'session_id' => $wizardSession->session_id
        ), Prism\Constants::NOT_LEGACY);

        // Prepare custom data
        $custom = array(
            'payment_session_id' => $paymentSession->getId(),
            'gateway'            => $this->serviceAlias
        );

        $html[] = '<input type="hidden" name="custom" value="' . base64_encode(json_encode($custom)) . '" />';

        // Set a link to logo
        $imageUrl = trim($this->params->get('paypal_image_url'));
        if ($imageUrl) {
            $html[] = '<input type="hidden" name="image_url" value="' . $imageUrl . '" />';
        }

        // Set URLs
        $html[] = '<input type="hidden" name="cancel_return" value="' . $cancelUrl . '" />';
        $html[] = '<input type="hidden" name="return" value="' . $returnUrl . '" />';
        $html[] = '<input type="hidden" name="notify_url" value="' . $notifyUrl . '" />';

        $this->prepareLocale($html);

        // End the form.
        $html[] = '<img alt="" border="0" width="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" >';
        $html[] = '</form>';

        // Display a sticky note if extension works in sandbox mode.
        if ($this->params->get('paypal_sandbox', 1)) {
            $html[] = '<div class="alert alert-info p-10-5"><span class="fa fa-info-circle"></span> ' . JText::_($this->textPrefix . '_WORKS_SANDBOX') . '</div>';
        }

        $html[] = '</div>';

        return implode("\n", $html);
    }

    /**
     * This method processes transaction data that comes from PayPal instant notifier.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param Registry  $params  The parameters of the component
     *
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     *
     * @return null|PaymentResult
     */
    public function onPaymentNotify($context, $params)
    {
        if (strcmp('com_crowdfunding.notify.'.$this->serviceAlias, $context) !== 0) {
            return null;
        }

        if ($this->app->isClient('administrator')) {
            return null;
        }

        // Check document type
        $docType = JFactory::getDocument()->getType();
        if (strcmp('raw', $docType) !== 0) {
            return null;
        }

        // Validate request method
        $requestMethod = $this->app->input->getMethod();
        if (strcmp('POST', $requestMethod) !== 0) {
            $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_REQUEST_METHOD'), $this->debugType, JText::sprintf($this->textPrefix . '_ERROR_INVALID_TRANSACTION_REQUEST_METHOD', $requestMethod));
            return null;
        }

        $postData   = file_get_contents('php://input');
        $ipnMessage = new PPIPNMessage($postData, $this->getIpnConfig());
        $postData   = $ipnMessage->getRawData();

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_RESPONSE'), $this->debugType, $postData) : null;

        // Decode custom data
        $custom = ArrayHelper::getValue($postData, 'custom');
        $custom = json_decode(base64_decode($custom), true);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_CUSTOM'), $this->debugType, $custom) : null;

        // Verify gateway. Is it PayPal?
        $gateway = ArrayHelper::getValue($custom, 'gateway');
        if (!$this->isValidPaymentGateway($gateway)) {
            $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_PAYMENT_GATEWAY'), $this->debugType, array('custom' => $custom, '_POST' => $postData));
            return null;
        }

        // Prepare the array that have to be returned by this method.
        $paymentResult = new PaymentResult();

        try {
            if ($ipnMessage->validate()) {
                $containerHelper = new Crowdfunding\Container\Helper\Money($this->container);
                $currency        = $containerHelper->getCurrency($params->get('project_currency'), new CurrencyGateway(\JFactory::getDbo()));

                // Get payment session data
                $paymentSessionId = ArrayHelper::getValue($custom, 'payment_session_id', 0, 'int');
                $paymentSession   = $this->getPaymentSession(['id' => $paymentSessionId], \Prism\Constants::NOT_LEGACY);

                // Check for valid payment session.
                if (!$paymentSession->getId()) {
                    $this->log->add(JText::_($this->textPrefix . '_ERROR_PAYMENT_SESSION'), $this->errorType, $paymentSession->getProperties());
                    return null;
                }

                // DEBUG DATA
                JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_PAYMENT_SESSION'), $this->debugType, $paymentSession->getProperties()) : null;

                $containerHelper = new Crowdfunding\Container\Helper\Project($this->container);
                $project         = $containerHelper->getProject($paymentSession->getProjectId(), new ProjectGateway(JFactory::getDbo()));
                if ($project === null || !$project->getId()) {
                    $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_PROJECT'), $this->errorType, $project);
                    return null;
                }

                // Prepare transaction data.
                $transactionData = $this->prepareTransactionData($postData, $project, $paymentSession);


                $isValidData  = $this->validateData($transactionData, $postData, $currency->getCode());
                if (!$isValidData) {
                    $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_TRANSACTION_DATA'), $this->debugType, $transactionData->getProperties());
                    return null;
                }

                // DEBUG DATA
                JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_VALID_DATA'), $this->debugType, $transactionData) : null;

                // Get reward object.
                $reward = null;
                if ($transactionData->getRewardId()) {
                    $containerHelper = new Crowdfunding\Container\Helper\Reward($this->container);
                    $reward          = $containerHelper->getReward($transactionData->getRewardId(), new RewardGateway(JFactory::getDbo()), $transactionData->getProjectId());
                }

                // Save transaction data.
                $transaction = $this->storeTransaction($transactionData);
                if ($transaction === null) {
                    return null;
                }

                // Generate object of data, based on the transaction properties.
                $paymentResult->transaction = $transaction;

                // Generate object of data based on the project properties.
                $paymentResult->project     = $project;

                // Generate object of data based on the reward properties.
                if ($reward instanceof Reward) {
                    $paymentResult->reward  = $reward;
                }

                // Generate data object, based on the payment session properties.
                $paymentResult->paymentSession = $paymentSession;

                // Removing intention.
                $this->removeIntention($paymentSession, $transaction);
            }
        } catch (Exception $e) {
            $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_TRANSACTION_DATA'), $this->debugType, array('error message' => $e->getMessage(), '_POST' => $postData));
        }

        return $paymentResult;
    }

    /**
     * Prepare transaction data that comes from PayPal.
     *
     * @param array          $data
     * @param Project        $project
     * @param PaymentSession $paymentSession
     *
     * @throws \InvalidArgumentException
     * @return PaymentTransaction
     */
    protected function prepareTransactionData(array $data, Project $project, PaymentSession $paymentSession)
    {
        $txnDate        = ArrayHelper::getValue($data, 'payment_date');
        $dateValidator  = new Prism\Validator\Date($txnDate);
        $date           = $dateValidator->isValid() ? new JDate($txnDate) : new JDate();

        // Prepare transaction data
        $transactionData = new PaymentTransaction(
            [
                'investor_id'      => (int)$paymentSession->getUserId(),
                'receiver_id'      => (int)$project->getUserId(),
                'project_id'       => (int)$paymentSession->getProjectId(),
                'reward_id'        => (int)$paymentSession->getRewardId(),
                'service_provider' => $this->serviceProvider,
                'service_alias'    => $this->serviceAlias,
                'txn_id'           => ArrayHelper::getValue($data, 'txn_id', null, 'string'),
                'txn_amount'       => ArrayHelper::getValue($data, 'mc_gross', null, 'float'),
                'txn_currency'     => ArrayHelper::getValue($data, 'mc_currency', null, 'string'),
                'txn_status'       => strtolower(ArrayHelper::getValue($data, 'payment_status', '', 'string')),
                'txn_date'         => $date->toSql(),
                'extra_data'       => $this->prepareExtraData($data)
            ]
        );

        return $transactionData;
    }

    /**
     * Validate transaction data.
     *
     * @param PaymentTransaction  $transactionData
     * @param array $postData
     * @param string $currencyCode
     *
     * @return bool
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function validateData(PaymentTransaction $transactionData, array $postData, $currencyCode)
    {
        if (!$transactionData->getProjectId()) {
            $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_PROJECT_ID'), $this->debugType, $transactionData);
            return Prism\Constants::INVALID;
        }

        if (!$transactionData->getTxnId()) {
            $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_TRANSACTION_ID'), $this->debugType, $transactionData);
            return Prism\Constants::INVALID;
        }

        // Check if reward record exists in database.
        if ($transactionData->getRewardId() > 0) {
            $rewardRecord = new Crowdfunding\Validator\Reward\Record(JFactory::getDbo(), $transactionData->getRewardId(), ['state' => Prism\Constants::PUBLISHED]);
            if (!$rewardRecord->isValid()) {
                $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_REWARD'), $this->errorType, $transactionData);
                return Prism\Constants::INVALID;
            }
        }

        // Check currency
        if (strcmp($transactionData->getTxnCurrency(), $currencyCode) !== 0) {
            $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_TRANSACTION_CURRENCY'), $this->errorType, array('TRANSACTION DATA' => $transactionData, 'CURRENCY' => $currencyCode));
            return Prism\Constants::INVALID;
        }

        // Check payment receiver.
        $allowedReceivers = array(
            strtolower(ArrayHelper::getValue($postData, 'business')),
            strtolower(ArrayHelper::getValue($postData, 'receiver_email')),
            strtolower(ArrayHelper::getValue($postData, 'receiver_id'))
        );

        // Get payment receiver.
        $paymentReceiver  = $this->getPaymentReceiver($transactionData->getProjectId());
        if (!in_array($paymentReceiver, $allowedReceivers, true)) {
            $this->log->add(
                JText::_($this->textPrefix . '_ERROR_INVALID_RECEIVER'),
                $this->errorType,
                array(
                    'TRANSACTION DATA'  => $transactionData,
                    'RECEIVER'          => $paymentReceiver,
                    'ALLOWED RECEIVERS' => $allowedReceivers
                )
            );

            return Prism\Constants::INVALID;
        }

        return Prism\Constants::VALID;
    }

    /**
     * Save transaction data.
     *
     * @param PaymentTransaction $transactionData
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return null|Transaction
     */
    protected function storeTransaction(PaymentTransaction $transactionData)
    {
        // Get transaction object by transaction ID
        $keys  = array(
            'txn_id' => $transactionData->getTxnId()
        );
        $transaction = new Transaction(JFactory::getDbo());
        $transaction->load($keys);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_TRANSACTION_OBJECT'), $this->debugType, $transaction->getProperties()) : null;

        // Check for existed transaction
        // If the current status if completed, stop the payment process.
        if ($transaction->getId() && $transaction->isCompleted()) {
            return null;
        }

        // Add extra data.
        if ($transactionData->getExtraData()) {
            $transaction->addExtraData($transactionData->getExtraData());
        }

        // IMPORTANT: It must be placed before ->bind();
        $options = array(
            'old_status' => $transaction->getStatus(),
            'new_status' => $transactionData->getTxnStatus()
        );

        $transaction
            ->setReceiverId($transactionData->getReceiverId())
            ->setInvestorId($transactionData->getInvestorId())
            ->setProjectId($transactionData->getProjectId())
            ->setRewardId($transactionData->getRewardId())
            ->setServiceProvider($transactionData->getServiceProvider())
            ->setServiceAlias($transactionData->getServiceAlias())
            ->setTxnId($transactionData->getTxnId())
            ->setTxnAmount($transactionData->getTxnAmount())
            ->setTxnCurrency($transactionData->getTxnCurrency())
            ->setTxnStatus($transactionData->getTxnStatus())
            ->setTxnDate($transactionData->getTxnDate());

        // Start database transaction.
        $db = JFactory::getDbo();

        try {
            $db->transactionStart();

            $transactionManager = new TransactionManager($db);
            $transactionManager->setTransaction($transaction);
            $transactionManager->process('com_crowdfunding.payment', $options);

            $db->transactionCommit();
        } catch (Exception $e) {
            $db->transactionRollback();
            $this->log->add(JText::_($this->textPrefix . '_ERROR_TRANSACTION_PROCESS'), $this->errorType, $e->getMessage());
            return null;
        }

        return $transaction;
    }

    protected function prepareLocale(&$html)
    {
        // Get country
        $countryId = $this->params->get('paypal_country');
        $country   = new Crowdfunding\Country\Country(JFactory::getDbo());
        $country->load($countryId);

        $code  = $country->getCode();
        $code4 = $country->getLocale();

        $button    = $this->params->get('paypal_button_type', 'btn_buynow_LG');
        $buttonUrl = $this->params->get('paypal_button_url');

        // Generate a button
        if (!$this->params->get('paypal_button_default', 0)) {
            if (!$buttonUrl) {
                if (strcmp('US', $code) === 0) {
                    $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/' . $code4 . '/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . '_BUTTON_ALT') . '">';
                } else {
                    $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/' . $code4 . '/' . $code . '/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . '_BUTTON_ALT') . '">';
                }
            } else {
                $html[] = '<input type="image" name="submit" border="0" src="' . $buttonUrl . '" alt="' . JText::_($this->textPrefix . '_BUTTON_ALT') . '">';
            }
        } else { // Default button
            $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/en_US/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . '_BUTTON_ALT') . '">';
        }

        // Set locale
        $html[] = '<input type="hidden" name="lc" value="' . $code . '" />';
    }

    /**
     * Prepare a form element of payment receiver.
     *
     * @param $itemId
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function preparePaymentReceiver($itemId)
    {
        if ((bool)$this->params->get('paypal_sandbox', 1)) {
            $result = '<input type="hidden" name="business" value="' . trim($this->params->get('paypal_sandbox_business_name')) . '" />';
        } else {
            $result = '<input type="hidden" name="business" value="' . trim($this->params->get('paypal_business_name')) . '" />';
        }

        if (strcmp('project_owner', $this->params->get('paypal_payment_receiver', 'site_owner')) === 0) {
            if (JComponentHelper::isEnabled('com_crowdfundingfinance')) {
                if ($this->payout === null) {
                    $this->payout = new Crowdfundingfinance\Payout(JFactory::getDbo());
                    $this->payout->load(['project_id' => $itemId], ['secret_key' => $this->app->get('secret')]);
                }

                if (!$this->payout->getPaypalEmail()) {
                    $this->log->add(JText::_($this->textPrefix . '_ERROR_PAYOUT_PROJECT_OWNER'), $this->errorType);
                    return '';
                }

                $result = '<input type="hidden" name="business" value="' . trim($this->payout->getPaypalEmail()) . '" />';
            } else {
                $this->log->add(JText::_($this->textPrefix . '_ERROR_CROWDFUNDING_FINANCE'), $this->errorType);
                return '';
            }
        }

        return $result;
    }

    /**
     * Return payment receiver.
     *
     * @param $itemId
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function getPaymentReceiver($itemId)
    {
        if ($this->params->get('paypal_sandbox', 1)) {
            $result = strtolower(trim($this->params->get('paypal_sandbox_business_name')));
        } else {
            $result = strtolower(trim($this->params->get('paypal_business_name')));
        }

        if (strcmp('project_owner', $this->params->get('paypal_payment_receiver', 'site_owner')) === 0) {
            if (!JComponentHelper::isEnabled('com_crowdfundingfinance')) {
                return '';
            }

            if ($this->payout === null) {
                $this->payout = new Crowdfundingfinance\Payout(JFactory::getDbo());
                $this->payout->load(['project_id' => $itemId], ['secret_key' => $this->app->get('secret')]);
            }

            if (!$this->payout->getPaypalEmail()) {
                return '';
            }

            $result = trim($this->payout->getPaypalEmail());
        }

        return $result;
    }

    /**
     * Prepare the config for IPN message checker.
     *
     * @return array
     */
    protected function getIpnConfig()
    {
        $config = [];

        if ($this->params->get('paypal_sandbox', Prism\Constants::ENABLED)) {
            $config['mode'] = 'sandbox';
        } else {
            $config['mode'] = 'live';
        }

        return $config;
    }
}
