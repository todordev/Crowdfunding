<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Payment;

use Prism;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Prism\Payment\Result as PaymentResult;
use Crowdfunding;
use Emailtemplates;
use Prism\Utilities\RouteHelper;

use Crowdfunding\Container\MoneyHelper;
use Crowdfunding\Transaction\Transaction;
use Crowdfunding\Payment\Session\Session as PaymentSession;
use Crowdfunding\Payment\Session\Mapper as PaymentSessionMapper;
use Crowdfunding\Payment\Session\Repository as PaymentSessionRepository;
use Crowdfunding\Payment\Session\Gateway\JoomlaGateway as PaymentSessionGateway;

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding payment plugin class.
 *
 * @package      Crowdfunding
 * @subpackage   Plugin
 */
class Plugin extends \JPlugin
{
    protected $serviceProvider;
    protected $serviceAlias;

    protected $log;
    protected $textPrefix = 'PLG_CROWDFUNDINGPAYMENT';
    protected $debugType  = 'DEBUG_PAYMENT_PLUGIN';
    protected $errorType  = 'ERROR_PAYMENT_PLUGIN';

    protected $logFile    = 'com_crowdfunding.payment.php';
    protected $logTable   = '#__crowdf_logs';

    /**
     * @var \JApplicationSite
     */
    protected $app;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * This property contains keys of response data
     * that will be used to be generated an array with extra data.
     *
     * @var array
     */
    protected $extraDataKeys = array();

    /**
     * @var Container
     */
    protected $container;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->textPrefix  .= '_' . strtoupper($this->serviceAlias);
        $this->debugType   .= '_' . strtoupper($this->serviceAlias);
        $this->errorType   .= '_' . strtoupper($this->serviceAlias);

        // Create log object
        $this->log = new Prism\Log\Log();

        // Set database log writer if Joomla! debug is enabled.
        if ($this->logTable !== null and $this->logTable !== '' and JDEBUG) {
            $this->log->addAdapter(new Prism\Log\Adapter\Database(\JFactory::getDbo(), $this->logTable));
        }

        // Set file log adapter.
        if ($this->logFile !== null and $this->logFile !== '') {
            $file = \JPath::clean($this->app->get('log_path') .DIRECTORY_SEPARATOR. basename($this->logFile));
            $this->log->addAdapter(new Prism\Log\Adapter\File($file));
        }

        $this->container = Prism\Container::getContainer();
    }

    /**
     * Update rewards properties - availability, distributed,...
     *
     * @param $data
     *
     * @throws \InvalidArgumentException
     * @return \Crowdfunding\Reward|null
     *
     * @throws \RuntimeException
     * @deprecated v2.8 Use Crowdfunding\Observer\Transaction\TransactionObserver
     */
    protected function updateReward($data)
    {
        // Get reward.
        $keys = array(
            'id'         => ArrayHelper::getValue($data, 'reward_id'),
            'project_id' => ArrayHelper::getValue($data, 'project_id')
        );
        
        $reward = new Crowdfunding\Reward(\JFactory::getDbo());
        $reward->load($keys);

        // DEBUG DATA
        JDEBUG ? $this->log->add(\JText::_($this->textPrefix . '_DEBUG_REWARD_OBJECT'), $this->debugType, $reward->getProperties()) : null;

        // Check for valid reward.
        if (!$reward->getId()) {
            // Log data in the database
            $this->log->add(
                \JText::_($this->textPrefix . '_ERROR_INVALID_REWARD'),
                $this->debugType,
                array('data' => $data, 'reward object' => $reward->getProperties())
            );

            return null;
        }

        // Check for valida amount between reward value and payed by user
        $txnAmount = ArrayHelper::getValue($data, 'txn_amount');
        if ($txnAmount < $reward->getAmount()) {
            // Log data in the database
            $this->log->add(
                \JText::_($this->textPrefix . '_ERROR_INVALID_REWARD_AMOUNT'),
                $this->debugType,
                array('data' => $data, 'reward object' => $reward->getProperties())
            );

            return null;
        }

        // Verify the availability of rewards
        if ($reward->isLimited() and !$reward->getAvailable()) {
            // Log data in the database
            $this->log->add(
                \JText::_($this->textPrefix . '_ERROR_REWARD_NOT_AVAILABLE'),
                $this->debugType,
                array('data' => $data, 'reward object' => $reward->getProperties())
            );

            return null;
        }

        // Increase the number of distributed rewards.
        $reward->increaseDistributed();
        $reward->updateDistributed();

        return $reward;
    }

    /**
     * Send emails to the administrator, project owner and the user who have made a donation.
     *
     * @param PaymentResult   $paymentResult
     * @param Registry    $params
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     * @throws Prism\Domain\BindException
     *
     * @return void
     */
    protected function sendMails(PaymentResult $paymentResult, $params)
    {
        if (!\JComponentHelper::isInstalled('com_emailtemplates')) {
            \JLog::add(\JText::_('LIB_CROWDFUNDING_EMAIL_TEMPLATES_INSTALLATION'), \JLog::WARNING, 'com_crowdfunding');
            return;
        }

        $transaction = $paymentResult->transaction;
        /** @var Crowdfunding\Transaction\Transaction $transaction */

        $project = $paymentResult->project;
        /** @var Crowdfunding\Project $project */

        $reward = $paymentResult->reward;
        /** @var Crowdfunding\Reward $reward */

        // Get website
        $uri       = \JUri::getInstance();
        $website   = $uri->toString(array('scheme', 'host'));

        $emailMode   = $this->params->get('email_mode', 'plain');
        $isHtmlMode  = (strcmp('html', $emailMode) === 0);

        // Get money formatter.
        $moneyFormatter = MoneyHelper::getMoneyFormatter($this->container, $params);
        $currency       = MoneyHelper::getCurrency($this->container, $params);
        /** @var Prism\Money\Formatter\IntlDecimalFormatter $moneyFormatter */

        // Prepare data for parsing.
        $data = array(
            'site_name'      => $this->app->get('sitename'),
            'site_url'       => \JUri::root(),
            'item_title'     => $project->getTitle(),
            'item_url'       => $website . \JRoute::_(\CrowdfundingHelperRoute::getDetailsRoute($project->getSlug(), $project->getCatSlug())),
            'amount'         => $moneyFormatter->formatCurrency(new Prism\Money\Money($transaction->getAmount(), $currency)),
            'transaction_id' => $transaction->getTransactionId(),
            'reward_title'   => '',
            'delivery_date'  => '',
            'payer_name'     => '',
            'payer_email'    => ''
        );

        // Set data used in Bank Transfer payment plugin.
        if (array_key_exists('banktransfer', $paymentResult->paymentData)) {
            $bankTransfer = (array)$paymentResult->paymentData['banktransfer'];

            $data['iban']         = array_key_exists('iban', $bankTransfer) ? $bankTransfer['iban'] : '';
            $data['bank_account'] = array_key_exists('bank_account', $bankTransfer) ? $bankTransfer['bank_account'] : '';

            if ($data['bank_account'] !== '' and $isHtmlMode) {
                $bankTransfer['bank_account'] = nl2br($bankTransfer['bank_account']);
            }
        }

        // Prepare data about payer if he is NOT anonymous ( is registered user with profile ).
        if ((int)$transaction->getInvestorId() > 0) {
            $investor            = \JFactory::getUser($transaction->getInvestorId());
            $data['payer_email'] = $investor->get('email');
            $data['payer_name']  = $investor->get('name');
        }

        // Set reward data.
        if (is_object($reward)) {
            $data['reward_title'] = $reward->getTitle();

            $dateValidator = new Prism\Validator\Date($reward->getDeliveryDate());
            if ($dateValidator->isValid()) {
                $date = new \JDate($reward->getDeliveryDate());
                $data['delivery_date'] = $date->format($this->params->get('date_format_views', \JText::_('DATE_FORMAT_LC3')));
            }
        }

        // Send mail to the administrator
        $emailId = (int)$this->params->get('admin_mail_id', 0);
        if ($emailId > 0) {
            $email = new Emailtemplates\Email();
            $email->setDb(\JFactory::getDbo());
            $email->load($emailId);

            if (!$email->getSenderName()) {
                $email->setSenderName($this->app->get('fromname'));
            }
            if (!$email->getSenderEmail()) {
                $email->setSenderEmail($this->app->get('mailfrom'));
            }

            $recipientId = (int)$params->get('administrator_id', 0);
            if ($recipientId > 0) {
                $recipient     = \JFactory::getUser($recipientId);
                $recipientName = $recipient->get('name');
                $recipientMail = $recipient->get('email');
            } else {
                $recipientName = $this->app->get('fromname');
                $recipientMail = $this->app->get('mailfrom');
            }

            // Prepare data for parsing
            $data['sender_name']     = $email->getSenderName();
            $data['sender_email']    = $email->getSenderEmail();
            $data['recipient_name']  = $recipientName;
            $data['recipient_email'] = $recipientMail;

            // DEBUG
            JDEBUG ? $this->log->add(\JText::_($this->textPrefix . '_DEBUG_SEND_MAIL_ADMINISTRATOR'), $this->debugType, $data) : null;

            $email->parse($data);
            $subject = $email->getSubject();
            $body    = $email->getBody($emailMode);

            $mailer = \JFactory::getMailer();
            if ($isHtmlMode) { // Send as HTML message
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);
            } else { // Send as plain text.
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);
            }

            // Check for an error.
            if ($return !== true) {
                $this->log->add(\JText::_($this->textPrefix . '_ERROR_MAIL_SENDING_ADMIN'), $this->errorType, $mailer->ErrorInfo);
            }
        }

        // Send mail to project owner.
        $emailId = (int)$this->params->get('creator_mail_id', 0);
        if ($emailId > 0) {
            $email = new Emailtemplates\Email();
            $email->setDb(\JFactory::getDbo());
            $email->load($emailId);

            if (!$email->getSenderName()) {
                $email->setSenderName($this->app->get('fromname'));
            }
            if (!$email->getSenderEmail()) {
                $email->setSenderEmail($this->app->get('mailfrom'));
            }

            $user          = \JFactory::getUser($transaction->getReceiverId());
            $recipientName = $user->get('name');
            $recipientMail = $user->get('email');

            // Prepare data for parsing
            $data['sender_name']     = $email->getSenderName();
            $data['sender_email']    = $email->getSenderEmail();
            $data['recipient_name']  = $recipientName;
            $data['recipient_email'] = $recipientMail;

            // DEBUG
            JDEBUG ? $this->log->add(\JText::_($this->textPrefix . '_DEBUG_SEND_MAIL_PROJECT_OWNER'), $this->debugType, $data) : null;

            $email->parse($data);
            $subject = $email->getSubject();
            $body    = $email->getBody($emailMode);

            $mailer = \JFactory::getMailer();
            if ($isHtmlMode) { // Send as HTML message
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);
            } else { // Send as plain text.
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);
            }

            // Check for an error.
            if ($return !== true) {
                $this->log->add(\JText::_($this->textPrefix . '_ERROR_MAIL_SENDING_PROJECT_OWNER'), $this->errorType, $mailer->ErrorInfo);
            }
        }

        // Send mail to backer.
        $emailId = (int)$this->params->get('user_mail_id', 0);
        if ($emailId > 0 and (int)$transaction->getInvestorId() > 0) {
            $email = new Emailtemplates\Email();
            $email->setDb(\JFactory::getDbo());
            $email->load($emailId);

            if (!$email->getSenderName()) {
                $email->setSenderName($this->app->get('fromname'));
            }
            if (!$email->getSenderEmail()) {
                $email->setSenderEmail($this->app->get('mailfrom'));
            }

            $user          = \JFactory::getUser($transaction->getInvestorId());
            $recipientName = $user->get('name');
            $recipientMail = $user->get('email');

            // Prepare data for parsing
            $data['sender_name']     = $email->getSenderName();
            $data['sender_email']    = $email->getSenderEmail();
            $data['recipient_name']  = $recipientName;
            $data['recipient_email'] = $recipientMail;

            // DEBUG
            JDEBUG ? $this->log->add(\JText::_($this->textPrefix . '_DEBUG_SEND_MAIL_BACKER'), $this->debugType, $data) : null;

            $email->parse($data);
            $subject = $email->getSubject();
            $body    = $email->getBody($emailMode);

            $mailer = \JFactory::getMailer();
            if ($isHtmlMode) { // Send as HTML message
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);
            } else { // Send as plain text.
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);
            }

            // Check for an error.
            if ($return !== true) {
                $this->log->add(\JText::_($this->textPrefix . '_ERROR_MAIL_SENDING_PROJECT_OWNER'), $this->errorType, $mailer->ErrorInfo);
            }
        }
    }

    /**
     * Send email to the administrator if there is a problem with a payment plugin.
     *
     * @param string $message
     *
     * @throws \Exception
     */
    protected function notifyAdministrator($message)
    {
        $app = \JFactory::getApplication();
        /** @var $app \JApplicationSite */

        $componentParams = \JComponentHelper::getParams('com_crowdfunding');

        $adminId = (int)$componentParams->get('administrator_id', 0);
        if ($adminId > 0) {
            $recipient     = \JFactory::getUser($adminId);
            $recipientMail = $recipient->get('email');
        } else {
            $recipientMail = $app->get('mailfrom');
        }

        // Get website
        $website = \JUri::getInstance()->toString(array('scheme', 'host'));

        $subject = \JText::_($this->textPrefix . '_ERROR_SUBJECT');
        $body    = \JText::sprintf($this->textPrefix . '_ERROR_BODY', $this->serviceProvider, $website, htmlentities($message, ENT_QUOTES, 'UTF-8'));

        $mailer  = \JFactory::getMailer();
        $return  = $mailer->sendMail($app->get('mailfrom'), $app->get('fromname'), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);

        // Check for an error.
        if ($return !== true) {
            $this->log->add(\JText::_($this->textPrefix . '_ERROR_MAIL_SENDING_ADMIN'), $this->errorType, $mailer->ErrorInfo);
        }
    }

    /**
     * This method returns payment session.
     *
     * @param array $options The keys used to load payment session data from database.
     * @param bool $legacy
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return PaymentSession
     * @deprecated v2.7 We will return the new Session object after v3.0
     */
    public function getPaymentSession(array $options, $legacy = true)
    {
        // The legacy code will be removed in v3.0
        if ($legacy) {
            $id        = ArrayHelper::getValue($options, 'id', 0, 'int');
            $sessionId = ArrayHelper::getValue($options, 'session_id', '');
            $uniqueKey = ArrayHelper::getValue($options, 'unique_key', '');
            $orderId   = ArrayHelper::getValue($options, 'order_id', '');

            // Prepare keys for anonymous user.
            if ($id > 0) {
                $keys = $id;
            } elseif ($sessionId !== '') {
                $keys = array(
                    'session_id'   => $sessionId
                );
            } elseif ($uniqueKey !== '' and $orderId !== '') { // Prepare keys to get record by unique key and order ID.
                $keys = array(
                    'unique_key' => $uniqueKey,
                    'order_id' => $orderId
                );
            } elseif ($uniqueKey !== '') { // Prepare keys to get record by unique key.
                $keys = array(
                    'unique_key' => $uniqueKey
                );
            } elseif ($orderId !== '') { // Prepare keys to get record by order ID.
                $keys = array(
                    'order_id' => $orderId
                );
            } else {
                throw new \UnexpectedValueException(\JText::_('LIB_CROWDFUNDING_INVALID_PAYMENT_SESSION_KEYS'));
            }

            $paymentSession = new Crowdfunding\Payment\Session(\JFactory::getDbo());
            $paymentSession->load($keys);

            return $paymentSession;
        }

        // The following code returns the new Session class.
        $id        = ArrayHelper::getValue($options, 'id', 0, 'int');
        $sessionId = ArrayHelper::getValue($options, 'session_id', '');
        $token     = ArrayHelper::getValue($options, 'token', '');
        $orderId   = ArrayHelper::getValue($options, 'order_id', '');

        $conditions = new Prism\Database\Request\Conditions();

        // Prepare keys for anonymous user.
        if ($id > 0) {
            $conditions->addCondition(new Prism\Database\Request\Condition(['column' => 'id', 'value' => $id]));
        } elseif ($sessionId) {
            $conditions->addCondition(new Prism\Database\Request\Condition(['column' => 'session_id', 'value' => $sessionId]));
        } elseif ($token && $orderId) { // Prepare keys to get record by unique key and order ID.
            $conditions
                ->addCondition(new Prism\Database\Request\Condition(['column' => 'order_id', 'value' => $orderId]))
                ->addSpecificCondition('token', new Prism\Database\Request\Condition(['column' => 'token', 'value' => $token, 'table' => 'b']));
        } elseif ($token) { // Prepare keys to get record by unique key.
            $conditions->addSpecificCondition('token', new Prism\Database\Request\Condition(['column' => 'token', 'value' => $token, 'table' => 'b']));
        } elseif ($orderId) { // Prepare keys to get record by order ID.
            $conditions->addCondition(new Prism\Database\Request\Condition(['column' => 'order_id', 'value' => $orderId]));
        } else {
            throw new \InvalidArgumentException(\JText::_('LIB_CROWDFUNDING_INVALID_PAYMENT_SESSION_KEYS'));
        }

        $databaseRequest = new Prism\Database\Request\Request;
        $databaseRequest->setConditions($conditions);

        $repository = new PaymentSessionRepository(new PaymentSessionMapper(new PaymentSessionGateway(\JFactory::getDbo())));

        return $repository->fetch($databaseRequest);
    }

    /**
     * Generate a system message.
     *
     * @param string $message
     * @param array $options
     *
     * @return string
     *
     * @todo Remove backward compatible break in v2.8
     */
    protected function generateSystemMessage($message, $options = array())
    {
        if (is_array($options)) {
            $title = array_key_exists('title', $options) ? $options['title'] : '';
            $type  = array_key_exists('type', $options) ? $options['type'] : 'error';
            $icon  = array_key_exists('icon', $options) ? $options['icon'] : 'info-circle';
        } elseif (is_string($options)) { // BC fix
            $type  = $options;
            $title = '';
            $icon  = 'info-circle';
        }

        $html = '
        <div id="system-message-container">
			<div id="system-message">
                <div class="alert alert-'.$type.'">
                    <a data-dismiss="alert" class="close">×</a>
                    ';

        if ($title !== '') {
            $html .= '<h4 class="alert-heading"><span class="fa fa-'.$icon.'"></span> ' . $title . '</h4>';
        }

        $html .= '<div>';

        if ($icon !== '' and $title === '') {
            $html .= '<p><span class="fa fa-'.$icon.'"></span> ' . htmlentities($message, ENT_QUOTES, 'UTF-8') . '</p>';
        } else {
            $html .= '<p>' . htmlentities($message, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $html .= '</div>
                </div>
            </div>
	    </div>';

        return $html;
    }

    /**
     * This method get fees from Crowdfunding Finance.
     *
     * @param $fundingType
     *
     * @return array
     */
    protected function getFees($fundingType)
    {
        $fees = array();

        if (\JComponentHelper::isEnabled('com_crowdfundingfinance')) {
            $params = \JComponentHelper::getParams('com_crowdfundingfinance');
            /** @var $params Registry */

            if (strcmp('FIXED', $fundingType) === 0) {
                if ($params->get('fees_fixed_campaign_percent')) {
                    $fees['fixed_campaign_percent'] = (float)$params->get('fees_fixed_campaign_percent');
                }

                if ($params->get('fees_fixed_campaign_amount')) {
                    $fees['fixed_campaign_amount'] = (float)$params->get('fees_fixed_campaign_amount');
                }
            }

            if (strcmp('FLEXIBLE', $fundingType) === 0) {
                if ($params->get('fees_flexible_campaign_percent')) {
                    $fees['flexible_campaign_percent'] = (float)$params->get('fees_flexible_campaign_percent');
                }

                if ($params->get('fees_flexible_campaign_amount')) {
                    $fees['flexible_campaign_amount'] = (float)$params->get('fees_flexible_campaign_amount');
                }
            }
        }

        return $fees;
    }

    /**
     * This method calculates a fee which is set by Crowdfunding Finance.
     *
     * @param $fundingType
     * @param $fees
     * @param $txnAmount
     *
     * @throws \InvalidArgumentException
     *
     * @return float
     */
    protected function calculateFee($fundingType, $fees, $txnAmount)
    {
        $result = 0;

        $feePercent = 0.0;
        $feeAmount  = 0.0;

        switch ($fundingType) {
            case 'FIXED':
                $feePercent = ArrayHelper::getValue($fees, 'fixed_campaign_percent', 0.0, 'float');
                $feeAmount  = ArrayHelper::getValue($fees, 'fixed_campaign_amount', 0.0, 'float');
                break;

            case 'FLEXIBLE':
                $feePercent = ArrayHelper::getValue($fees, 'flexible_campaign_percent', 0.0, 'float');
                $feeAmount  = ArrayHelper::getValue($fees, 'flexible_campaign_amount', 0.0, 'float');
                break;
        }

        // Calculate fee based on percent.
        if ($feePercent > 0) {
            // Calculate amount.
            $feePercentAmount = Prism\Utilities\MathHelper::calculateValueFromPercent($feePercent, $txnAmount);

            if ($txnAmount > $feePercentAmount) {
                $result += $feePercentAmount;
            }
        }

        // Calculate fees based on amount.
        if ($feeAmount > 0 and ($txnAmount > $feeAmount)) {
            $result += $feeAmount;
        }

        // Check for invalid value that is less than zero.
        if ($result < 0) {
            $result = 0;
        }

        return (float)$result;
    }

    /**
     * Return a link (notification URL) where the payment service will send information about a payment.
     *
     * @param bool $htmlEncoded
     *
     * @return string
     */
    protected function getCallbackUrl($htmlEncoded = false)
    {
        $page   = trim($this->params->get('callback_url'));

        $uri    = \JUri::getInstance();
        $domain = $uri->toString(array('host'));

        // Encode to valid HTML.
        if ($htmlEncoded) {
            $page = str_replace('&', '&amp;', $page);
        }

        // Add the domain to the URL.
        if (false === strpos($page, $domain)) {
            $page = \JUri::root() . $page;
        }

        return $page;
    }

    /**
     * Return a link where the player will be redirected after successful payment.
     *
     * @param string $slug
     * @param string $catslug
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getReturnUrl($slug, $catslug)
    {
        $page = trim($this->params->get('return_url'));
        if (!$page) {
            $page   = RouteHelper::siteRoute(\CrowdfundingHelperRoute::getBackingRoute($slug, $catslug, 'share'));
        }

        if (false === strpos($page, '://')) {
            $uri  = \JUri::getInstance();
            $page = $uri->toString(array('scheme', 'host')) . $page;
        }

        return $page;
    }

    /**
     * Return a link where the player will be redirected if he refuses to pay.
     *
     * @param string $slug
     * @param string $catslug
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getCancelUrl($slug, $catslug)
    {
        $page = trim($this->params->get('cancel_url'));
        if (!$page) {
            $page   = RouteHelper::siteRoute(\CrowdfundingHelperRoute::getBackingRoute($slug, $catslug, 'default'));
        }

        return $page;
    }

    /**
     * Prepare extra data.
     *
     * @param array  $data
     * @param string $note
     *
     * @return array
     */
    protected function prepareExtraData(array $data, $note = '')
    {
        $date        = new \JDate();
        $trackingKey = $date->toUnix();

        $extraData = array(
            $trackingKey => array()
        );

        foreach ($this->extraDataKeys as $key) {
            if (array_key_exists($key, $data)) {
                $extraData[$trackingKey][$key] = $data[$key];
            }
        }

        if ($note) {
            $extraData[$trackingKey]['NOTE'] = $note;
        }

        return $extraData;
    }

    /**
     * Check for valid payment gateway.
     *
     * @param string $gateway
     *
     * @return bool
     */
    protected function isValidPaymentGateway($gateway)
    {
        $value1 = strtolower($this->serviceAlias);
        $value2 = strtolower($gateway);

        return (strcmp($value1, $value2) === 0);
    }

    /**
     * Remove an intention and payment session records.
     *
     * @param Crowdfunding\Payment\Session $paymentSession
     * @param bool $removeIntention Remove or not the intention record.
     *
     * @deprecated v2.8 Use removeIntention.
     */
    protected function closePaymentSession($paymentSession, $removeIntention = false)
    {
        // Remove intention record.
        if ($paymentSession->getIntentionId() and $removeIntention) {
            $intention = new Crowdfunding\Intention(\JFactory::getDbo());
            $intention->load($paymentSession->getIntentionId());

            if ($intention->getId()) {
                $intention->delete();
            }
        }

        // Remove payment session record.
        $paymentSession->delete();
    }

    /**
     * Remove an intention records.
     *
     * @param PaymentSession $paymentSession
     * @param Transaction $transaction
     */
    protected function removeIntention(PaymentSession $paymentSession, Transaction $transaction)
    {
        // Remove intention record.
        $removeIntention  = (strcmp('completed', $transaction->getStatus()) === 0 or strcmp('pending', $transaction->getStatus()) === 0);
        if ($paymentSession->getIntentionId() and $removeIntention) {
            $intention = new Crowdfunding\Intention(\JFactory::getDbo());
            $intention->load($paymentSession->getIntentionId());

            if ($intention->getId()) {
                $intention->delete();
            }
        }
    }

    /**
     * This method is executed after complete payment notification.
     * It is used to be sent mails to users and the administrator.
     *
     * <code>
     * $paymentResult->transaction;
     * $paymentResult->project;
     * $paymentResult->reward;
     * $paymentResult->paymentSession;
     * $paymentResult->serviceProvider;
     * $paymentResult->serviceAlias;
     * $paymentResult->response;
     * $paymentResult->returnUrl;
     * $paymentResult->message;
     * $paymentResult->triggerEvents;
     * </code>
     *
     * @param string $context
     * @param PaymentResult $paymentResult  Object that contains Transaction, Reward, Project, PaymentSession, etc.
     * @param Registry $params Component parameters
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     * @throws Prism\Domain\BindException
     */
    public function onAfterPaymentNotify($context, $paymentResult, $params)
    {
        if (!preg_match('/com_crowdfunding\.(notify|payments).*\.'.$this->serviceAlias.'$/', $context)) {
            return;
        }

        if ($this->app->isAdmin()) {
            return;
        }

        // Check document type
        $docType = \JFactory::getDocument()->getType();
        if (!in_array($docType, array('raw', 'html'), true)) {
            return;
        }

        // Send mails
        $this->sendMails($paymentResult, $params);
    }

    /**
     * This method will be executed after all payment events, especially onAfterPaymentNotify.
     * It is used to close payment session.
     *
     * <code>
     * $paymentResult->transaction;
     * $paymentResult->project;
     * $paymentResult->reward;
     * $paymentResult->paymentSession;
     * $paymentResult->serviceProvider;
     * $paymentResult->serviceAlias;
     * $paymentResult->response;
     * $paymentResult->returnUrl;
     * $paymentResult->message;
     * $paymentResult->triggerEvents;
     * </code>
     *
     * @param string $context
     * @param \stdClass $paymentResult  Object that contains Transaction, Reward, Project, PaymentSession, etc.
     * @param Registry $params Component parameters
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function onAfterPayment($context, $paymentResult, $params)
    {
        if (!preg_match('/com_crowdfunding\.(notify|payments).*\.'.$this->serviceAlias.'$/', $context)) {
            return;
        }

        if ($this->app->isAdmin()) {
            return;
        }

        // Check document type
        $docType = \JFactory::getDocument()->getType();
        if (!in_array($docType, array('raw', 'html'), true)) {
            return;
        }

        $paymentSession = $paymentResult->paymentSession;
        /** @var PaymentSession $paymentSession */

        // Remove payment session record from database.
        if (($paymentSession instanceof PaymentSession) and $paymentSession->getId()) {
            $repository = new PaymentSessionRepository(new PaymentSessionMapper(new PaymentSessionGateway(\JFactory::getDbo())));
            $repository->delete($paymentSession);
        }
    }
}
