<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\String\StringHelper;
use Crowdfunding\Facade\Joomla as JoomlaFacade;

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding backing controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerBacking extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelBacking|bool
     * @since    1.5
     */
    public function getModel($name = 'Backing', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Authorize payment process step.
     *
     * @throws RuntimeException
     */
    public function authorize()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get params
        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $params Joomla\Registry\Registry */

        $model = $this->getModel();
        /** @var $model CrowdfundingModelBacking */

        $itemId = $this->input->getUint('id', 0);
        $item   = $model->getItem($itemId);
        $user   = JFactory::getUser();

        $isAuthorisedStep = true;

        // Trigger the event of a plugin that authorize payment process.
        JPluginHelper::importPlugin('crowdfundingpayment');
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onPaymentAuthorize', array('com_crowdfunding.payment.authorize', &$item, &$params, &$user));

        foreach ($results as $result) {
            if (false === $result) {
                $isAuthorisedStep = false;
                break;
            }
        }

        if (!$isAuthorisedStep) {
            $returnUrl = CrowdfundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
            $this->setRedirect(JRoute::_($returnUrl, false), JText::_('COM_CROWDFUNDING_ERROR_NO_PERMISSIONS'), 'warning');
            return;
        }

        // Redirect to next page
        $layout = $this->input->getCmd('layout');
        $link   = CrowdfundingHelperRoute::getBackingRoute($item->slug, $item->catslug, $layout);
        $this->setRedirect(JRoute::_($link, false));
    }

    public function process()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $token  = $this->input->getString('token');
        if (!$token) {
            // Check for request forgeries.
            $requestMethod = $app->input->getMethod();
            if (strcmp('POST', $requestMethod) === 0) {
                JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
            } else {
                JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
            }
        } else {
            $expected  = hash('sha256', $app->get('secret'));

            if (strcmp($token, $expected) !== 0) {
                jexit(JText::_('JINVALID_TOKEN'));
            }
        }

        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $params Joomla\Registry\Registry */

        // Get the data from the form.
        $itemId   = $this->input->getInt('id', 0);
        if (!$itemId) {
            $this->setRedirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false), JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'warning');
            return;
        }

        $model   = $this->getModel();
        /** @var $model CrowdfundingModelBacking */

        /** @var stdClass $item */
        $item    = $model->getItem($itemId);

        // Check for valid project
        if ($item !== null && is_object($item) && (int)$item->id === 0) {
            $this->setRedirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute()), JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'warning');
            return;
        }

        $returnUrl = CrowdfundingHelperRoute::getBackingRoute($item->slug, $item->catslug);

        $rewardId  = $this->input->getUint('rid', 0);
        $amount    = $this->input->getString('amount');

        // Parse the amount.
        $moneyParser  = JoomlaFacade::getMoneyParser();
        $amount       = $moneyParser->parse($amount);

        // Get user ID
        $user   = JFactory::getUser();
        $userId = (int)$user->get('id');

        // Anonymous user ID
        $aUserId = '';

        // Check for maintenance (debug) state.
        if ($params->get('debug_payment_disabled', 0)) {
            $msg = StringHelper::trim($params->get('debug_disabled_functionality_msg'));
            if (!$msg) {
                $msg = JText::_('COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG');
            }

            $this->setRedirect(JRoute::_($returnUrl, false), $msg, 'notice');
            return;
        }

        // Check for agreed conditions from the user.
        $terms = 0;
        if ($params->get('backing_terms', 0)) {
            $terms = $this->input->get('terms', 0, 'int');
            if (!$terms) {
                $this->setRedirect(JRoute::_($returnUrl, false), JText::_('COM_CROWDFUNDING_ERROR_TERMS_NOT_ACCEPTED'), 'warning');
                return;
            }
        }

        // Check for valid amount.
        if (!$amount) {
            $this->setRedirect(JRoute::_($returnUrl, false), JText::_('COM_CROWDFUNDING_ERROR_INVALID_AMOUNT'), 'warning');
            return;
        }

        $currency         = JoomlaFacade::getCurrency();
        $moneyFormatter   = JoomlaFacade::getMoneyFormatter();

        // Check minimum allowed amount.
        $minimumAmount  = (float)$params->get('backing_minimum_amount');
        $moneyAmount    = new Prism\Money\Money($minimumAmount, $currency);
            
        $minimumAmountFormatted = $moneyFormatter->formatCurrency($moneyAmount);
        if ($minimumAmount > 0 && ($minimumAmount > $amount)) {
            $this->setRedirect(JRoute::_($returnUrl, false), JText::sprintf('COM_CROWDFUNDING_ERROR_MINIMUM_AMOUNT_S', $minimumAmountFormatted), 'warning');
            return;
        }

        // Check maximum allowed amount.
        $maximumAmount  = (float)$params->get('backing_maximum_amount');
        $moneyAmount    = new Prism\Money\Money($maximumAmount, $currency);
        
        $maximumAmountFormatted  = $moneyFormatter->formatCurrency($moneyAmount);
        if ($maximumAmount > 0 && ($maximumAmount < $amount)) {
            $this->setRedirect(JRoute::_($returnUrl, false), JText::sprintf('COM_CROWDFUNDING_ERROR_MAXIMUM_AMOUNT_S', $maximumAmountFormatted), 'warning');
            return;
        }

        // Store payment process data

        // Get the payment process object and
        // store the selected data from the user.
        $wizardSessionContext    = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT.$item->id;
        $wizardSession           = $app->getUserState($wizardSessionContext);

        $wizardSession->step1    = true;
        $wizardSession->amount   = $amount;
        $wizardSession->rewardId = $rewardId;
        $wizardSession->terms    = $terms;

        $app->setUserState($wizardSessionContext, $wizardSession);

        // Generate hash user ID used for anonymous payment.
        if (!$userId) {
            $aUserId = $app->getUserState('auser_id');
            if (!$aUserId) {
                // Generate a hash ID for anonymous user.
                $aUserId = Prism\Utilities\StringHelper::generateRandomString(32);
                $app->setUserState('auser_id', $aUserId);
            }
        }

        $date   = new JDate();

        // Create an intention record.
        $intentionId = 0;
        if ($userId > 0) {
            $intentionKeys = array(
                'user_id'    => $userId,
                'project_id' => $item->id
            );

            $intention = new Crowdfunding\Intention(JFactory::getDbo());
            $intention->load($intentionKeys);

            $intentionData = array(
                'user_id'     => $userId,
                'project_id'  => $item->id,
                'reward_id'   => $rewardId,
                'record_date' => $date->toSql()
            );

            $intention->bind($intentionData);
            $intention->store();

            $intentionId = $intention->getId();
        }

        // Create payment session.
        $paymentSession = new Crowdfunding\Payment\Session\Session;
        $paymentSessionData = array(
            'user_id'      => $userId,
            'auser_id'     => $aUserId, // Anonymous user hash ID
            'project_id'   => $item->id,
            'reward_id'    => $rewardId,
            'record_date'  => $date->toSql(),
            'session_id'   => $wizardSession->session_id,
            'intention_id' => $intentionId
        );

        $paymentSession->bind($paymentSessionData);

        $gateway     = new Crowdfunding\Payment\Session\Gateway\JoomlaGateway(\JFactory::getDbo());
        $repository  = new Crowdfunding\Payment\Session\Repository(new Crowdfunding\Payment\Session\Mapper($gateway));
        $repository->store($paymentSession);

        // Redirect to next page
        $layout = $this->input->getCmd('layout', 'default');
        $link   = CrowdfundingHelperRoute::getBackingRoute($item->slug, $item->catslug, $layout);
        $this->setRedirect(JRoute::_($link, false));
    }
}
