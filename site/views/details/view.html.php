<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Facade\Joomla as JoomlaFacade;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewDetails extends JViewLegacy
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

    /**
     * @var Joomla\DI\Container
     */
    protected $container;

    protected $item;

    protected $imageFolder;
    protected $screen;
    protected $items;
    protected $form;
    protected $userId;
    protected $isOwner;
    protected $avatarsSize;
    protected $socialProfiles;
    protected $defaultAvatar;
    protected $onCommentAfterDisplay;
    protected $commentsEnabled;
    protected $currency;
    protected $moneyFormatter;
    protected $displayAmounts;

    protected $option;

    protected $pageclass_sfx;

    /**
     * @var JApplicationSite
     */
    protected $app;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');
        
        // Get model state.
        $this->state  = $this->get('State');
        $this->item   = $this->get('Item');

        // Get params
        $this->params = $this->state->get('params');
        /** @var  $this->params Joomla\Registry\Registry */

        $model  = $this->getModel();
        $user   = JFactory::getUser();
        $userId = $user->get('id');

        // Handle bus helper.
        $helperBus = new Prism\Helper\HelperBus($this->item);
        $helperBus->addCommand(new Crowdfunding\Helper\PrepareItemParamsHelper());
        $helperBus->addCommand(new Crowdfunding\Helper\PrepareItemFundingHelper());
        $helperBus->addCommand(new Crowdfunding\Helper\PrepareItemAccessHelper($user));
        $helperBus->handle();

        if (!$this->item || !$model->isAllowed($this->item, $userId)) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));
            return;
        }

        $this->currency       = JoomlaFacade::getCurrency();
        $this->moneyFormatter = JoomlaFacade::getMoneyFormatter();

        // Get the path to the images.
        $this->imageFolder    = $this->params->get('images_directory', 'images/crowdfunding');

        $this->defaultAvatar  = JUri::base() . $this->params->get('integration_avatars_default');
        $this->avatarsSize    = $this->params->get('integration_avatars_size', 'small');

        // Prepare the link that points to project page.
        $host             = JUri::getInstance()->toString(array('scheme', 'host'));
        $this->item->link = $host . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug));

        // Prepare the link that points to project image.
        $this->item->link_image = $host . '/' . $this->imageFolder . '/' . $this->item->image;

        // Get the current screen.
        $this->screen = $this->app->input->getCmd('screen', 'home');

        $this->prepareDocument();

        // Import content plugins
        JPluginHelper::importPlugin('content');

        switch ($this->screen) {
            case 'updates':
                $this->prepareUpdatesScreen();
                break;

            case 'comments':
                $this->prepareCommentsScreen();
                break;

            case 'funders':
                $this->prepareFundersScreen();
                break;

            default: // Home
                break;
        }

        // Events
        $dispatcher        = JEventDispatcher::getInstance();
        $this->item->event = new stdClass();

        $results                                 = $dispatcher->trigger('onContentBeforeDisplay', array('com_crowdfunding.details', &$this->item, &$this->params));
        $this->item->event->beforeDisplayContent = trim(implode("\n", $results));

        $results                                 = $dispatcher->trigger('onContentAfterDisplayMedia', array('com_crowdfunding.details', &$this->item, &$this->params));
        $this->item->event->onContentAfterDisplayMedia = trim(implode("\n", $results));

        $results                                  = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.details', &$this->item, &$this->params));
        $this->item->event->onContentAfterDisplay = trim(implode("\n", $results));

        // Count hits
        $model->hit($this->item->id);

        parent::display($tpl);
    }

    protected function prepareUpdatesScreen()
    {
        $model       = JModelLegacy::getInstance('Updates', 'CrowdfundingModel', $config = array('ignore_request' => false));
        /** @var CrowdfundingModelUpdates $model */

        $this->items = $model->getItems();
        $this->form  = $model->getForm();

        $this->userId  = JFactory::getUser()->id;
        $this->isOwner = ($this->userId === $this->item->user_id);

        // Get users IDs
        $usersIds             = Prism\Utilities\ArrayHelper::getIds($this->items, 'user_id');

        // Prepare social integration.
        $this->socialProfiles = CrowdfundingHelper::prepareIntegration($this->params->get('integration_social_platform'), $usersIds);

        // Scripts
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        JHtml::_('prism.ui.pnotify');

        JHtml::_('prism.ui.joomlaHelper');

        $this->document->addScript('media/' . $this->option . '/js/site/updates.js');
    }

    protected function prepareCommentsScreen()
    {
        $this->commentsEnabled = $this->params->get('comments_enabled', 1);

        // Initialize default comments functionality.
        if ($this->commentsEnabled) {
            $model       = JModelLegacy::getInstance('Comments', 'CrowdfundingModel', $config = array('ignore_request' => false));
            /** @var CrowdfundingModelComments $model */

            $this->items = $model->getItems();
            $this->form  = $model->getForm();

            $this->userId  = JFactory::getUser()->get('id');
            $this->isOwner = ($this->userId === $this->item->user_id);

            // Get users IDs
            $usersIds             = Prism\Utilities\ArrayHelper::getIds($this->items, 'user_id');

            // Prepare social integration.
            $this->socialProfiles = CrowdfundingHelper::prepareIntegration($this->params->get('integration_social_platform'), $usersIds);

            // Scripts
            JHtml::_('behavior.keepalive');
            JHtml::_('behavior.formvalidation');
            JHtml::_('prism.ui.pnotify');

            JHtml::_('Prism.ui.joomlaHelper');

            $this->document->addScript('media/' . $this->option . '/js/site/comments.js');
        }

        // Trigger comments plugins.
        $dispatcher                  = JEventDispatcher::getInstance();

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.comments', &$this->item, &$this->params));
        $this->onCommentAfterDisplay = trim(implode("\n", $results));
    }

    protected function prepareFundersScreen()
    {
        $model       = JModelLegacy::getInstance('Funders', 'CrowdfundingModel', $config = array('ignore_request' => false));
        /** @var CrowdfundingModelFunders $model */

        $this->items = $model->getItems();

        // Create a currency object if I have to display funders amounts.
        $this->displayAmounts = $this->params->get('funders_display_amounts', 0);
        if ($this->displayAmounts) {
            $this->moneyFormatter  = JoomlaFacade::getMoneyFormatter();
        }

        // Prepare social integration.
        $usersIds             = Prism\Utilities\ArrayHelper::getIds($this->items);
        $this->socialProfiles = CrowdfundingHelper::prepareIntegration($this->params->get('integration_social_platform'), $usersIds);
    }

    protected function prepareDocument()
    {
        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        // Meta description
        $this->document->setDescription($this->item->short_desc);

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $this->app->getPathway();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 32);
        $pathway->addItem($currentBreadcrumb);

        // Add scripts
        JHtml::_('jquery.framework');
    }

    private function preparePageHeading()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $this->app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_DETAILS_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    }

    private function preparePageTitle()
    {
        // Prepare page title
        $title = $this->item->title;

        switch ($this->screen) {
            case 'updates':
                $title .= ' | ' . JText::_('COM_CROWDFUNDING_UPDATES');
                break;

            case 'comments':
                $title .= ' | ' . JText::_('COM_CROWDFUNDING_COMMENTS');
                break;

            case 'funders':
                $title .= ' | ' . JText::_('COM_CROWDFUNDING_FUNDERS');
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
}
