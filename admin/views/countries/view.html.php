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

class CrowdfundingViewCountries extends JViewLegacy
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
    
    protected $regionsNumber;

    public function display($tpl = null)
    {
        $this->option     = JFactory::getApplication()->input->get('option');
        
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $countryCodes = Joomla\Utilities\ArrayHelper::getColumn($this->items, 'code');

        // Get number of rewards.
        $countryStatistic    = new Crowdfunding\Country\Statistic\Counter(JFactory::getDbo());
        $this->regionsNumber = $countryStatistic->countRegions($countryCodes);

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
            'a.name'      => JText::_('COM_CROWDFUNDING_NAME'),
            'a.code'      => JText::_('COM_CROWDFUNDING_COUNTRY_CODE'),
            'a.locale'    => JText::_('COM_CROWDFUNDING_COUNTRY_LOCALE'),
            'a.latitude'  => JText::_('COM_CROWDFUNDING_COUNTRY_LATITUDE'),
            'a.longitude' => JText::_('COM_CROWDFUNDING_COUNTRY_LONGITUDE'),
            'a.timezone'  => JText::_('COM_CROWDFUNDING_COUNTRY_TIMEZONE'),
            'a.id'        => JText::_('JGRID_HEADING_ID')
        );
    }

    protected function addSidebar()
    {
        CrowdfundingHelper::addSubmenu($this->getName());

        $this->sidebar = JHtmlSidebar::render();
    }

    protected function addToolbar()
    {
        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_COUNTRIES_MANAGER'));
        JToolbarHelper::addNew('country.add');
        JToolbarHelper::editList('country.edit');
        JToolbarHelper::divider();

        // Add custom buttons
        $bar = JToolbar::getInstance('toolbar');

        // Import
        $link = JRoute::_('index.php?option=com_crowdfunding&view=import&type=countries');
        $bar->appendButton('Link', 'upload', JText::_('COM_CROWDFUNDING_IMPORT_COUNTRIES'), $link);

        // Import Regions
        $link = JRoute::_('index.php?option=com_crowdfunding&view=import&type=regions');
        $bar->appendButton('Link', 'upload', JText::_('COM_CROWDFUNDING_IMPORT_REGIONS'), $link);

        JToolbarHelper::divider();
        JToolbarHelper::deleteList(JText::_('COM_CROWDFUNDING_DELETE_ITEMS_QUESTION'), 'countries.delete');
        JToolbarHelper::divider();
        JToolbarHelper::custom('countries.backToDashboard', 'dashboard', '', JText::_('COM_CROWDFUNDING_DASHBOARD'), false);
    }

    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_COUNTRIES_MANAGER'));

        // Scripts
        JHtml::_('behavior.multiselect');
        JHtml::_('bootstrap.tooltip');

        JHtml::_('formbehavior.chosen', 'select');

        JHtml::_('Prism.ui.joomlaList');
    }
}
