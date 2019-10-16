<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Country\Country;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewRegions extends JViewLegacy
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

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;
    protected $sortFields;

    protected $sidebar;

    /**
     * @var Country
     */
    protected $country;

    public function display($tpl = null)
    {
        $this->option     = JFactory::getApplication()->input->get('option');
        
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $countryCode = $this->state->get('filter.country_code');
        $this->country     = new Country(JFactory::getDbo());
        $this->country->load(['code' => $countryCode]);

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
            'a.name'            => JText::_('COM_CROWDFUNDING_NAME'),
            'a.id'              => JText::_('JGRID_HEADING_ID')
        );
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
        JToolbarHelper::title(JText::sprintf('COM_CROWDFUNDING_REGIONS_MANAGER_S', $this->country->getName()));
        JToolbarHelper::addNew('region.add');
        JToolbarHelper::editList('region.edit');

        JToolbarHelper::divider();
        JToolbarHelper::deleteList(JText::_('COM_CROWDFUNDING_DELETE_ITEMS_QUESTION'), 'regions.delete');
        JToolbarHelper::divider();
        JToolbarHelper::custom('regions.backToDashboard', 'dashboard', '', JText::_('COM_CROWDFUNDING_DASHBOARD'), false);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_REGIONS_MANAGER'));

        JHtml::_('behavior.multiselect');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('Prism.ui.joomlaList');
    }
}
