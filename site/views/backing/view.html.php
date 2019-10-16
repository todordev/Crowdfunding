<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Data\DataObject;
use Joomla\String\StringHelper;
use Crowdfunding\Facade\Joomla as JoomlaFacade;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewBacking extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $item;

    /**
     * @var Prism\Money\Currency
     */
    protected $currency;

    /**
     * @var Prism\Money\Formatter\IntlDecimalFormatter
     */
    protected $moneyFormatter;

    /**
     * @var Prism\Money\Parser\IntlDecimalParser
     */
    protected $moneyParser;

    protected $imageFolder;
    protected $layout;
    protected $rewardsEnabled;
    protected $disabledButton;
    protected $loginForm;
    protected $returnUrl;
    protected $layoutData;
    protected $rewardId;
    protected $rewards;
    protected $rewardAmount;
    protected $reward;
    protected $paymentAmount;
    protected $option;
    protected $container;
    protected $wizardSessionContext;
    protected $wizardSession;
    protected $event;

    protected $nextStepTask;
    protected $nextStepLayout;
    protected $nextStepLink;
    protected $nextStepAllowed;

    protected $wizardSteps = array();
    protected $stepContext;

    /**
     * @var JEventDispatcher
     */
    protected $dispatcher;

    protected $pageclass_sfx;

    /**
     * @var JApplicationSite
     */
    protected $app;

    /**
     * @var JUser
     */
    protected $user;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');

        $this->state  = $this->get('State');
        $this->item   = $this->get('Item');

        // Get params
        $this->params = $this->state->get('params');
        if (!$this->item) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));
            return;
        }

        $this->container      = Prism\Container::getContainer();

        // Prepare money formatter.
        $this->currency       = JoomlaFacade::getCurrency();
        $this->moneyFormatter = JoomlaFacade::getMoneyFormatter();
        $this->moneyParser    = JoomlaFacade::getMoneyParser();

        // Create an object that will contain the data during the payment process.
        $this->wizardSessionContext = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT.$this->item->id;
        $this->wizardSession        = $this->app->getUserState($this->wizardSessionContext);

        // Prepare media folder
        $this->imageFolder = CrowdfundingHelper::getImagesFolder();

        // Set a link that points to project page
        $filter    = JFilterInput::getInstance();
        $host      = $filter->clean(JUri::getInstance()->toString(array('scheme', 'host')));

        // Set a link to image
        $this->item->link =  $host . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);
        $this->item->link_image = $host . '/' . $this->imageFolder . '/' . $this->item->image;

        $this->user = JFactory::getUser();

        JPluginHelper::importPlugin('crowdfundingpayment');
        $this->dispatcher = JEventDispatcher::getInstance();

        $this->prepareLayout();
        $layout = $this->getLayout();

        // Create payment session
        if (!$this->wizardSession || empty($this->wizardSession->step1) || strcmp('default', $layout) === 0) {
            $this->wizardSession = $this->createWizardSession();
            $this->app->setUserState($this->wizardSessionContext, $this->wizardSession);
        }

        $this->prepareWizardSteps();
        $this->prepareNextStep();

        switch ($layout) {
            case 'step': // Step 2 on wizard in four steps.
                $this->prepareStep();
                break;

            case 'payment': // Step 2
                $this->preparePayment();
                break;

            case 'share': // Step 3
                $this->prepareShare();
                break;

            default: //  Step 1 ( Rewards )
                $this->prepareRewards();
                break;
        }

        // Get project type and check for enabled rewards.
        $this->rewardsEnabled = CrowdfundingHelper::isRewardsEnabled($this->item->id);

        // Check days left. If there is no days, disable the button.
        $this->disabledButton = '';
        if (!$this->item->days_left) {
            $this->disabledButton = 'disabled="disabled"';
        }

        // Prepare the data of the layout
        $this->layoutData = new DataObject(array(
            'layout'          => $this->layout,
            'item'            => $this->item,
            'paymentSession'  => $this->wizardSession,
            'rewards_enabled' => $this->rewardsEnabled,
            'wizard_steps'    => $this->wizardSteps
        ));

        $this->prepareDebugMode();
        $this->prepareDocument();

        // Store the new values of the payment process to the user session.
        $this->app->setUserState($this->wizardSessionContext, $this->wizardSession);

        parent::display($tpl);
    }

    /**
     * This method displays a content from a Crowdfunding Plugin.
     *
     * @throws \InvalidArgumentException
     */
    protected function prepareStep()
    {
        $nextStepParams = new DataObject([
            'task'    => $this->nextStepTask,
            'layout'  => $this->nextStepLayout,
            'link'    => $this->nextStepLink,
            'allowed' => $this->nextStepAllowed
        ]);

        // Trigger the event on a step and display the content.
        $context    = 'com_crowdfunding.payment.step.'. $this->stepContext;
        $results    = $this->dispatcher->trigger('onPreparePaymentStep', array($context, &$this->item, &$nextStepParams, &$this->params));

        $result                 = (string)array_pop($results);
        $this->event            = new stdClass;
        $this->event->onDisplay = StringHelper::trim($result);
    }

    protected function prepareRewards()
    {
        // Create payment session ID.
        $this->wizardSession->session_id = (string)Prism\Utilities\StringHelper::generateRandomString(32);

        // Get selected reward ID
        $this->rewardId = (int)$this->state->get('reward_id');

        // If it has been selected another reward, set the old one to 0.
        if ($this->rewardId !== (int)$this->wizardSession->rewardId) {
            $this->wizardSession->rewardId = 0;
            $this->wizardSession->step1    = false;
        }

        // Get amount from session
        $this->rewardAmount = $this->wizardSession->amount ?: 0.00;

        // Get rewards
        $this->rewards = new Crowdfunding\Rewards(JFactory::getDbo());
        $this->rewards->load(array('project_id' => $this->item->id, 'state' => Prism\Constants::PUBLISHED));

        // Compare amount with the amount of reward, that is selected.
        // If the amount of selected reward is larger than amount from session,
        // use the amount of selected reward.
        if ($this->rewardId > 0) {
            $reward = $this->rewards->getReward((int)$this->rewardId);

            if ($reward !== null && ($this->rewardAmount < $reward->getAmount())) {
                $this->rewardAmount     = $reward->getAmount();
                $this->wizardSession->step1  = false;
            }
        }
    }

    protected function preparePayment()
    {
        // If missing the flag "step1", redirect to first step.
        if (!$this->wizardSession->step1) {
            $this->returnToStep1(JText::_('COM_CROWDFUNDING_ERROR_INVALID_AMOUNT'));
        }

        // Authorise the user
        if (!$this->user->authorise('crowdfunding.donate', 'com_crowdfunding')) {
            $this->returnToStep1(JText::_('COM_CROWDFUNDING_ERROR_NO_SIGNED_PAYMENT'));
        }

        // Check for both user states. The user must have only one state - registered user or anonymous user.
        $userId  = (int)$this->user->get('id');
        $aUserId = Joomla\String\StringHelper::trim($this->app->getUserState('auser_id'));

        if (($userId > 0 && $aUserId !== '') || ($userId === 0 && !$aUserId)) {
            // Reset anonymous hash user ID and redirect to first step.
            $this->app->setUserState('auser_id', '');
        }

        if (!$this->item->days_left) {
            $this->returnToStep1(JText::_('COM_CROWDFUNDING_ERROR_PROJECT_COMPLETED'));
        }

        // Validate reward
        $this->reward = null;
        $keys         = array(
            'id'         => $this->wizardSession->rewardId,
            'project_id' => $this->item->id
        );

        $this->reward = new Crowdfunding\Reward(JFactory::getDbo());
        $this->reward->load($keys);

        if ($this->reward->getId() && ($this->reward->isLimited() && !$this->reward->getAvailable())) {
            $this->returnToStep1(JText::_('COM_CROWDFUNDING_ERROR_REWARD_NOT_AVAILABLE'));
        }

        // Set the amount that will be displayed in the view.
        $this->paymentAmount = $this->wizardSession->amount;

        // Validate the amount.
        if (!$this->paymentAmount) {
            $this->returnToStep1(JText::_('COM_CROWDFUNDING_ERROR_INVALID_AMOUNT'));
        }

        // Events

        $item = new stdClass();

        $item->id             = $this->item->id;
        $item->title          = $this->item->title;
        $item->slug           = $this->item->slug;
        $item->catslug        = $this->item->catslug;
        $item->fundingType    = $this->item->funding_type;
        $item->rewardId       = $this->wizardSession->rewardId;
        $item->amount         = $this->wizardSession->amount;
        $item->currencyCode   = $this->currency->getCode();

        $moneyAmount          = new Prism\Money\Money($item->amount, $this->currency);
        $item->amountFormated = $this->moneyFormatter->format($moneyAmount);
        $item->amountCurrency = $this->moneyFormatter->formatCurrency($moneyAmount);

        $this->item->event    = new stdClass();

        // onBeforePaymentAuthorize
        JPluginHelper::importPlugin('crowdfundingpayment');
        $dispatcher = JEventDispatcher::getInstance();
        $results    = (array)$dispatcher->trigger('onBeforePaymentAuthorize', array('com_crowdfunding.before.payment.authorize', &$item, &$this->params));

        if (count($results) > 0) {
            $this->item->event->onBeforePaymentAuthorize = trim(implode("\n", $results));
        } else { // onProjectPayment
            $results    = $dispatcher->trigger('onProjectPayment', array('com_crowdfunding.payment', &$item, &$this->params));
            $this->item->event->onProjectPayment = trim(implode("\n", $results));
        }
    }

    protected function prepareShare()
    {
        // Get amount from session that will be displayed in the view.
        $this->paymentAmount = $this->wizardSession->amount;

        // Get reward
        $this->reward = null;
        if ((int)$this->wizardSession->rewardId > 0) {
            $this->reward = new Crowdfunding\Reward(JFactory::getDbo());
            $this->reward->load(array(
                'id'         => (int)$this->wizardSession->rewardId,
                'project_id' => (int)$this->item->id
            ));
        }

        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher = JEventDispatcher::getInstance();

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.payment.share', &$this->item, &$this->params, &$this->container));

        $this->item->event                      = new stdClass();
        $this->item->event->afterDisplayContent = trim(implode("\n", $results));

        // Reset anonymous hash user ID.
        $this->app->setUserState('auser_id', '');

        // Initialize the payment session creating new one.
        $this->wizardSession = $this->createWizardSession();
        $this->app->setUserState($this->wizardSessionContext, $this->wizardSession);
    }

    protected function prepareWizardSteps()
    {
        $layout     = $this->getLayout();

        // onPrepareWizardSteps
        $results    = (array)$this->dispatcher->trigger('onPrepareWizardSteps', array('com_crowdfunding.payment.wizard', &$this->item, $layout));

        if (count($results) > 0) {
            foreach ($results as $result) {
                $result['layout']    = 'step.'.$result['context'];
                $this->wizardSteps[] = $result;
            }
        }
        
        return $results;
    }

    protected function prepareLayout()
    {
        $context       = '';
        $this->layout  = $this->getLayout();

        if (strpos($this->layout, 'step.') === 0) {
            list($layout, $context) = explode('.', $this->layout);
            $this->setLayout($layout);
        }

        $this->stepContext = $context;
    }

    protected function prepareNextStep()
    {
        $this->nextStepTask   = 'backing.process';
        $this->nextStepLayout = 'payment';

        if (count($this->wizardSteps) > 0) {
            $nextStepLayout = '';
            $lastStep       = end($this->wizardSteps);

            // If it is default layout, get first custom step as next one.
            if (strcmp('default', $this->layout) === 0) {
                $nextStep       = reset($this->wizardSteps);
                $nextStepLayout = $nextStep['layout'];

                if (is_array($nextStep)) {
                    if (array_key_exists('task', $nextStep)) {
                        $this->nextStepTask = $nextStep['task'];
                    }

                    if (array_key_exists('allowed', $nextStep)) {
                        $this->nextStepAllowed = (bool)$nextStep['allowed'];
                    }
                }

            // If it is the last custom step, set the layout of the next step 'payment'.
            } elseif (strcmp($lastStep['layout'], $this->layout) === 0) {
                $nextStepLayout = 'payment';
            } else {
                foreach ($this->wizardSteps as $step) {
                    if (strcmp($step['layout'], $this->layout) === 0) {
                        $nextStep       = current($this->wizardSteps);
                        $nextStepLayout = $nextStep['layout'];

                        if (is_array($nextStep)) {
                            if (array_key_exists('task', $nextStep)) {
                                $this->nextStepTask = $nextStep['task'];
                            }

                            if (array_key_exists('allowed', $nextStep)) {
                                $this->nextStepAllowed = (bool)$nextStep['allowed'];
                            }
                        }
                        break;
                    }
                }
            }

//            $this->nextStepTask   = 'backing.step';
            $this->nextStepLayout = $nextStepLayout;
            $this->nextStepLink   = JRoute::_(CrowdfundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug, $nextStepLayout));
        }
    }

    /**
     * Check the system for debug mode
     *
     * @param JData
     */
    protected function prepareDebugMode()
    {
        // Check for maintenance (debug) state.
        $params = $this->state->get('params');
        if ($params->get('debug_payment_disabled', 0)) {
            $msg = Joomla\String\StringHelper::trim($params->get('debug_disabled_functionality_msg'));
            if (!$msg) {
                $msg = JText::_('COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG');
            }
            $this->app->enqueueMessage($msg, 'notice');

            $this->disabledButton = 'disabled="disabled"';

            // Store the new values of the payment process to the user session.
            $this->wizardSession->step1 = false;
        }
    }

    protected function prepareDocument()
    {
        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->item->short_desc);
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $this->app->getPathway();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 16);
        $pathway->addItem($currentBreadcrumb);

        // Scripts
        JHtml::_('jquery.framework');
        $this->document->addScript('media/' . $this->option . '/js/site/backing.js');
    }

    protected function preparePageHeading()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $this->app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_BACKING_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    }

    protected function preparePageTitle()
    {
        // Prepare page title
        $title = JText::sprintf('COM_CROWDFUNDING_INVESTING_IN', $this->escape($this->item->title));

        switch ($this->getLayout()) {
            case 'payment':
                $title .= ' | ' . JText::_('COM_CROWDFUNDING_PAYMENT_METHODS');
                break;

            case 'share':
                $title .= ' | ' . JText::_('COM_CROWDFUNDING_SHARE');
                break;
        }

        // Add title before or after Site Name
        if (!$title) {
            $title = $this->app->get('sitename');
        } elseif ((int)$this->app->get('sitename_pagetitles', 0) === 1) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        } elseif ((int)$this->app->get('sitename_pagetitles', 0) === 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
        }

        $this->document->setTitle($title);
    }

    protected function returnToStep1($message = '')
    {
        // Reset the flag for step 1
        $this->wizardSession->step1 = false;
        $this->app->setUserState($this->wizardSessionContext, $this->wizardSession);

        if ($message !== '') {
            $this->app->enqueueMessage($message, 'warning');
        }
        $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
    }

    protected function createWizardSession()
    {
        $wizardSession             = new DataObject;
        $wizardSession->step1      = false;
        $wizardSession->step2      = false;
        $wizardSession->amount     = 0.00;
        $wizardSession->rewardId   = 0;
        $wizardSession->session_id = '';
        $wizardSession->terms      = 0;

        return $wizardSession;
    }
}
