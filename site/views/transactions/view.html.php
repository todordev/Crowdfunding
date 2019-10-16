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

class CrowdfundingViewTransactions extends JViewLegacy
{
    /**
     * @var JApplicationSite
     */
    protected $app;

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

    protected $items;
    protected $pagination;


    protected $currency;
    protected $moneyFormatter;
    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $userId;
    protected $redirectUrl;

    protected $option;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');
        
        $userId = JFactory::getUser()->get('id');
        if (!$userId) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), 'notice');
            $this->app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
            return;
        }

        $this->items      = $this->get('Items');
        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');

        $this->params     = $this->state->get('params');
        /** @var  $params Joomla\Registry\Registry */

        if (is_array($this->items) && count($this->items) > 0) {
            $this->currency         = JoomlaFacade::getCurrency();
            $this->moneyFormatter   = JoomlaFacade::getMoneyFormatter();
        }

        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') === 0);

        $this->userId = JFactory::getUser()->get('id');

        $this->redirectUrl = CrowdfundingHelperRoute::getTransactionsRoute();

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
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_TRANSACTIONS_DEFAULT_PAGE_TITLE'));
        }
    }

    private function preparePageTitle()
    {
        // Prepare page title
        $title = $this->params->get('page_title', '');

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
