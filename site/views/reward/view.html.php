<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewReward extends JViewLegacy
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
     * @var JApplicationSite
     */
    protected $app;

    protected $items;
    protected $pagination;

    protected $reward;
    protected $userId;
    protected $deliveryDate;
    protected $imagesFolder;
    protected $socialProfiles;
    protected $redirectUrl;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;

    protected $option;

    protected $pageclass_sfx;
    
    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');
        
        // Get user ID.
        $this->userId = JFactory::getUser()->get('id');

        // Get reward ID.
        $rewardId = $this->app->input->getInt('id');

        // Validate reward owner
        $validator = new Crowdfunding\Validator\Reward\Owner(JFactory::getDbo(), $rewardId, $this->userId);
        if (!$validator->isValid()) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARD'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute()));
            return;
        }

        $this->items      = $this->get('Items');
        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get('params');
        $this->params = $params;

        // Prepare an URL where user will be redirected when change the state of a reward.
        $this->redirectUrl = 'index.php?option=com_crowdfunding&view=reward&id='.$rewardId;

        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') === 0);

        // Load reward data.
        $this->reward = new Crowdfunding\Reward(JFactory::getDbo());
        $this->reward->load($rewardId);

        // Prepare reward delivery date.
        $dateValidator = new Prism\Validator\Date($this->reward->getDeliveryDate());
        $this->deliveryDate = $dateValidator->isValid() ? JHtml::_('date', $this->reward->getDeliveryDate(), JText::_('DATE_FORMAT_LC3')) : '--';

        // Get images folder.
        $this->imagesFolder = CrowdfundingHelper::getImagesFolderUri($this->userId);

        // Get social profile
        if ($this->params->get('integration_social_platform')) {
            $userIds = Prism\Utilities\ArrayHelper::getIds($this->items, 'user_id');
            $this->socialProfiles = CrowdfundingHelper::prepareIntegration($this->params->get('integration_social_platform'), $userIds);
        }

        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareDocument()
    {
        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        // Meta Description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        // Meta keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        // Scripts
        JHtml::_('bootstrap.tooltip');
        JHtml::_('Prism.ui.pnotify');
        JHtml::_('Prism.ui.joomlaHelper');

        $this->document->addScript('media/com_crowdfunding/js/site/reward_state.js');
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
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_REWARD_DEFAULT_PAGE_TITLE'));
        }
    }

    private function preparePageTitle()
    {
        // Prepare page title
        $title = JText::_('COM_CROWDFUNDING_REWARD_DEFAULT_PAGE_TITLE');

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
