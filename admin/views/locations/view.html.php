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

class CrowdfundingViewLocations extends JViewLegacy
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
     * @var JApplicationAdministrator
     */
    protected $app;

    protected $items;
    protected $pagination;

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;
    protected $sortFields;

    protected $sidebar;

    public function display($tpl = null)
    {
        $this->app        = JFactory::getApplication();
        $this->option     = $this->app->input->get('option');
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Add submenu
        CrowdfundingHelper::addSubmenu($this->getName());

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
            'a.published'    => JText::_('JSTATUS'),
            'a.name'         => JText::_('COM_CROWDFUNDING_NAME'),
            'a.country_code' => JText::_('COM_CROWDFUNDING_COUNTRY_CODE'),
            'a.time_zone'    => JText::_('COM_CROWDFUNDING_TIMEZONE'),
            'a.id'           => JText::_('JGRID_HEADING_ID')
        );
    }

    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('archived' => false, 'trash' => false)), 'value', 'text', $this->state->get('filter.state'), true)
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
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_LOCATIONS_MANAGER'));
        JToolbarHelper::addNew('location.add');
        JToolbarHelper::editList('location.edit');
        JToolbarHelper::divider();

        // Add custom buttons
        $bar = JToolbar::getInstance();

        // Import Locations
        $link = JRoute::_('index.php?option=com_crowdfunding&view=import&type=locations');
        $bar->appendButton('Link', 'upload', JText::_('COM_CROWDFUNDING_IMPORT'), $link);

        JToolbarHelper::divider();
        JToolbarHelper::publishList('locations.publish');
        JToolbarHelper::unpublishList('locations.unpublish');
        JToolbarHelper::divider();
        JToolbarHelper::deleteList(JText::_('COM_CROWDFUNDING_DELETE_ITEMS_QUESTION'), 'locations.delete');
        JToolbarHelper::divider();
        JToolbarHelper::custom('locations.backToDashboard', 'dashboard', '', JText::_('COM_CROWDFUNDING_DASHBOARD'), false);
    }

    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_LOCATIONS_MANAGER'));

        JHtml::_('behavior.multiselect');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('Prism.ui.joomlaList');
    }
}
