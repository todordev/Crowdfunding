<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Facade\Joomla as JoomlaFacade;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewProjects extends JViewLegacy
{
    /**
     * @var JApplicationAdministrator
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

    protected $moneyFormatter;
    protected $currency;
    protected $rewards;

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;
//    protected $sortFields;

    protected $sidebar;

    public $activeFilters;
    public $filterForm;

    public function display($tpl = null)
    {
        $this->app      = JFactory::getApplication();
        $this->option   = $this->app->input->get('option');

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->params     = $this->state->get('params');

        $this->currency         = JoomlaFacade::getCurrency();
        $this->moneyFormatter   = JoomlaFacade::getMoneyFormatter();

        // Get projects IDs
        $projectsIds = Prism\Utilities\ArrayHelper::getIds($this->items);

        // Get number of rewards.
        $projects       = new Crowdfunding\Projects(JFactory::getDbo());
        $this->rewards  = $projects->getRewardsNumber($projectsIds);

        // Prepare sorting data
        $this->prepareSorting();

        if ($this->getLayout() !== 'modal') {
            // Prepare actions
            $this->addToolbar();
            $this->addSidebar();
        }

        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Prepare sortable fields, sort values and filters.
     *
     * @throws \Exception
     */
    protected function prepareSorting()
    {
        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') === 0);

        if ($this->saveOrder and ($this->getLayout() !== 'modal')) {
            $this->saveOrderingUrl = 'index.php?option=' . $this->option . '&task=' . $this->getName() . '.saveOrderAjax&format=raw';
            JHtml::_('sortablelist.sortable', $this->getName() . 'List', 'adminForm', strtolower($this->listDirn), $this->saveOrderingUrl);
        }

        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
    }

    protected function addSidebar()
    {
        CrowdfundingHelper::addSubmenu($this->getName());

        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_PROJECTS_MANAGER'));
        JToolbarHelper::addNew('project.add');
        JToolbarHelper::editList('project.edit');
        JToolbarHelper::divider();
        JToolbarHelper::publishList('projects.publish');
        JToolbarHelper::unpublishList('projects.unpublish');
        JToolbarHelper::divider();
        JToolbarHelper::custom('projects.approve', 'ok', '', JText::_('COM_CROWDFUNDING_APPROVE'), false);
        JToolbarHelper::custom('projects.disapprove', 'ban-circle', '', JText::_('COM_CROWDFUNDING_DISAPPROVE'), false);

        JToolbarHelper::divider();

        if ((int)$this->state->get('filter.state') === -2) {
            JToolbarHelper::deleteList('', 'projects.delete', 'JTOOLBAR_EMPTY_TRASH');
        } else {
            JToolbarHelper::trash('projects.trash');
        }

        JToolbarHelper::divider();
        JToolbarHelper::custom('projects.backToDashboard', 'dashboard', '', JText::_('COM_CROWDFUNDING_DASHBOARD'), false);
    }

    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_PROJECTS_MANAGER'));

        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('Prism.ui.joomlaList');
    }
}
