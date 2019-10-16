<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewLogs extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    protected $items;
    protected $pagination;

    protected $numberLogFilse;

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;
    protected $sortFields;

    protected $sidebar;

    protected $includeFiles = array(
        '/error_log',
        '/php_errorlog'
    );

    public function display($tpl = null)
    {
        $this->option     = JFactory::getApplication()->input->get('option');

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Get log files and count them.
        $logFiles = new Crowdfunding\Log\Files($this->includeFiles);
        $logFiles->load();

        $this->numberLogFilse = count($logFiles);

        // Prepare sorting data
        $this->prepareSorting();

        // Prepare actions
        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function prepareSorting()
    {
        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') === 0);

        if ($this->saveOrder) {
            $this->saveOrderingUrl = 'index.php?option=' . $this->option . '&task=' . $this->getName() . '.saveOrderAjax&format=raw';
            JHtml::_('sortablelist.sortable', $this->getName() . 'List', 'adminForm', strtolower($this->listDirn), $this->saveOrderingUrl);
        }

        $this->sortFields = array(
            'a.title'       => JText::_('COM_CROWDFUNDING_TITLE'),
            'a.type'        => JText::_('COM_CROWDFUNDING_TYPE'),
            'a.record_date' => JText::_('JDATE'),
            'a.id'          => JText::_('JGRID_HEADING_ID')
        );
    }

    protected function addSidebar()
    {
        // Add submenu
        CrowdfundingHelper::addSubmenu($this->getName());

        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        $filters = Crowdfunding\Filters::getInstance(JFactory::getDbo());
        $types   = $filters->getLogTypes();

        JHtmlSidebar::addFilter(
            JText::_('COM_CROWDFUNDING_SELECT_TYPE'),
            'filter_type',
            JHtml::_('select.options', $types, 'value', 'text', $this->state->get('filter.type'), true)
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
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_LOGS_MANAGER'));

        $bar = JToolbar::getInstance();
        $bar->appendButton(
            'Link',
            'eye-open',
            JText::sprintf('COM_CROWDFUNDING_VIEW_LOG_FILES_BUTTON', $this->numberLogFilse),
            JRoute::_('index.php?option=com_crowdfunding&view=log&layout=files')
        );

        $bar->appendButton('Link', 'refresh', JText::_('COM_CROWDFUNDING_RELOAD'), JRoute::_('index.php?option=com_crowdfunding&view=logs'));

        JToolbarHelper::custom('logs.removeall', 'trash', '', JText::_('COM_CROWDFUNDING_DELETE_ALL'), false);
        JToolbarHelper::deleteList(JText::_('COM_CROWDFUNDING_DELETE_ITEMS_QUESTION'), 'logs.delete');
        JToolbarHelper::divider();
        JToolbarHelper::custom('logs.backToDashboard', 'dashboard', '', JText::_('COM_CROWDFUNDING_DASHBOARD'), false);
    }

    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_LOGS_MANAGER'));

        // Load language string in JavaScript
        JText::script('COM_CROWDFUNDING_DELETE_ALL_ITEMS');

        JHtml::_('behavior.multiselect');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('Prism.ui.joomlaList');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
