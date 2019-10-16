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

class CrowdfundingViewReport extends JViewLegacy
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
    protected $form;

    protected $option;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $this->option = JFactory::getApplication()->input->get('option');
        
        $this->state  = $this->get('State');
        $this->item   = $this->get('Item');
        $this->form   = $this->get('Form');

        // Get params
        $this->params = $this->state->get('params');

        // Set a link to project page
//        $uri  = JUri::getInstance();
//        $this->link = $uri->toString(array('scheme', 'host')) . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);

        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            if (!$this->item) {
                $metaDescription = JText::_('COM_CROWDFUNDING_REPORT_CAMPAIGN');
            } else {
                $metaDescription = JText::sprintf('COM_CROWDFUNDING_REPORT_S', $this->item->title);
            }

            $this->document->setDescription($metaDescription);
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $app->getPathway();
        $currentBreadcrumb = (!$this->item) ? JText::_('COM_CROWDFUNDING_REPORT_CAMPAIGN') : JHtmlString::truncate($this->item->title, 16);
        $pathway->addItem($currentBreadcrumb);

        // Add scripts
        JHtml::_('jquery.framework');
        JHtml::_('Prism.ui.bootstrapMaxlength');
        JHtml::_('Prism.ui.bootstrap3Typeahead');

        $this->document->addScript('media/' . $this->option . '/js/site/report.js');
    }

    private function preparePageHeading()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $app->getMenu();
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
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page title
        if (!$this->item) {
            $title = JText::_('COM_CROWDFUNDING_REPORT_CAMPAIGN');
        } else {
            $title = JText::sprintf('COM_CROWDFUNDING_REPORT_S', $this->item->title);
        }

        // Add title before or after Site Name
        if (!$title) {
            $title = $app->get('sitename');
        } elseif ((int)$app->get('sitename_pagetitles', 0) === 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ((int)$app->get('sitename_pagetitles', 0) === 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);
    }
}
