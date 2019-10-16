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

class CrowdfundingViewCategories extends JViewLegacy
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

    protected $items;
    protected $pagination;

    protected $allowedLayouts = array('grid', 'list');
    protected $templateView;
    protected $descriptionLength;
    protected $displayProjectsNumber;
    protected $projectsNumber;
    protected $numberInRow;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;

    protected $option;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $this->option     = JFactory::getApplication()->input->get('option');

        $this->items      = $this->get('Items');
        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');

        $this->params     = $this->state->get('params');

        $this->displayProjectsNumber = $this->params->get('categories_show_projects_number', 0);
        $this->numberInRow           = $this->params->get('categories_categories_row', 3);

        // Get description length
        $this->descriptionLength = $this->params->get('categories_description_length');

        // Prepare items parameters.
        if (is_array($this->items) and count($this->items) > 0) {
            $this->prepareItems($this->items);
        }

        // Get layout
        $layout = $this->params->get('categories_layout', 'grid');
        $this->templateView = in_array($layout, $this->allowedLayouts, true) ? $layout : 'grid';

        $this->params = $this->state->get('params');
        /** @var  $params Joomla\Registry\Registry */

        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') === 0);

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepare document
     */
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
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_CATEGORIES_DEFAULT_PAGE_TITLE'));
        }
    }

    private function preparePageTitle()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page title
        $title = $this->params->get('page_title', '');

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

    private function prepareItems($items)
    {
        $options   = array(
            'count_projects' => $this->displayProjectsNumber,
            'project_state'  => array(
                'state' => Prism\Constants::PUBLISHED,
                'approved' => Prism\Constants::APPROVED
            )
        );

        $helperBus = new Prism\Helper\HelperBus($items);
        $helperBus->addCommand(new Crowdfunding\Helper\PrepareCategoriesHelper(new \Crowdfunding\Category\Helper\Gateway\Joomla\ProjectCounter(JFactory::getDbo())));
        $helperBus->handle($options);
    }
}
