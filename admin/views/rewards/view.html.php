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

class CrowdfundingViewRewards extends JViewLegacy
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

    protected $currency;
    protected $moneyFormatter;
    protected $projectTitle;

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;
    protected $sortFields;

    protected $sidebar;

    public function display($tpl = null)
    {
        $this->option     = JFactory::getApplication()->input->get('option');

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->params     = $this->state->get('params');

        $this->currency        =  JoomlaFacade::getCurrency();
        $this->moneyFormatter  =  JoomlaFacade::getMoneyFormatter();

        $projectId          = $this->state->get('project_id');
        $this->projectTitle = CrowdfundingHelper::getProjectTitle($projectId);

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

        $this->sortFields = array(
            'a.title'       => JText::_('COM_CROWDFUNDING_TITLE'),
            'a.published'   => JText::_('JSTATUS'),
            'a.amount'      => JText::_('COM_CROWDFUNDING_AMOUNT'),
            'a.number'      => JText::_('COM_CROWDFUNDING_NUMBER'),
            'a.distributed' => JText::_('COM_CROWDFUNDING_DISTRIBUTED'),
            'available'     => JText::_('COM_CROWDFUNDING_AVAILABLE'),
            'a.delivery'    => JText::_('COM_CROWDFUNDING_DELIVERY'),
            'a.id'          => JText::_('JGRID_HEADING_ID')
        );
    }

    protected function addSidebar()
    {
        // Add submenu
        CrowdfundingHelper::addSubmenu('projects');

        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('archived' => false)), 'value', 'text', $this->state->get('filter.state'), true)
        );

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
        JToolbarHelper::title(JText::sprintf('COM_CROWDFUNDING_REWARDS_MANAGER', $this->projectTitle));

        JToolbarHelper::addNew('reward.add');
        JToolbarHelper::editList('reward.edit');
        JToolbarHelper::divider();

        if ((int)$this->state->get('filter.state') === -2) {
            JToolbarHelper::deleteList('', 'rewards.delete', 'JTOOLBAR_EMPTY_TRASH');
        } else {
            JToolbarHelper::trash('rewards.trash');
        }

        JToolbarHelper::divider();

        // Add custom buttons
        $bar = JToolbar::getInstance();

        // Back to projects
        $link = JRoute::_('index.php?option=com_crowdfunding&view=projects');
        $bar->appendButton('Link', 'arrow-left-3', JText::_('COM_CROWDFUNDING_BACK_TO_PROJECTS'), $link);

        JToolbarHelper::divider();
        JToolbarHelper::custom('projects.backToDashboard', 'dashboard', '', JText::_('COM_CROWDFUNDING_DASHBOARD'), false);
    }

    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_REWARDS_MANAGER_BROWSER_TITLE'));

        JHtml::_('behavior.multiselect');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('Prism.ui.joomlaList');
    }
}
