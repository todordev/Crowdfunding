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

class CrowdfundingViewFriendmail extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;
    
    /**
     * @var JApplicationSite
     */
    public $app;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $item;

    protected $money;
    protected $imageFolder;
    protected $embedLink;
    protected $socialProfileLink;
    protected $displayCreator;
    protected $embedCode;
    protected $form;

    protected $option;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        /** @var $this->app JApplicationSite */

        $this->option = $this->app->input->get('option');

        $this->state  = $this->get('State');
        $this->item   = $this->get('Item');
        $this->form   = $this->get('Form');

        // Get params
        $this->params = $this->state->get('params');
        /** @var  $this->params Joomla\Registry\Registry */

        if (!$this->item) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));
            return;
        }

        if (!$this->params->get('security_display_friend_form', 0)) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_CANT_SEND_MAIL'), 'notice');
            $this->app->redirect(JRoute::_($this->item->link, false));
            return;
        }

        $this->prepareDocument();

        parent::display($tpl);
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
            $this->document->setDescription(JText::sprintf('COM_CROWDFUNDING_MAIL_TO_FRIEND_META_DESC_S', $this->item->title));
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

        // Add scripts
        JHtml::_('jquery.framework');

        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.formvalidation');
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
        $title = $this->item->title . ' | ' . JText::_('COM_CROWDFUNDING_EMAIL_TO_FRIEND');

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
