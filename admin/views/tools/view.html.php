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

class CrowdfundingViewTools extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $sidebar;
    protected $option;

    protected $projects = array();
    protected $lists = array();

    public function display($tpl = null)
    {
        $this->option = JFactory::getApplication()->input->get('option');
        $this->params = JComponentHelper::getParams($this->option);

        JLoader::register('CrowdfundingInstallHelper', CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . '/helpers/install.php');

        // Load library language
        $lang = JFactory::getLanguage();
        $lang->load('com_crowdfunding.sys', CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR);

        if (JComponentHelper::isInstalled('com_acymailing')) {
            $this->prepareAcymailing();
        }

        // Prepare actions
        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function addSidebar()
    {
        // Add submenu
        CrowdfundingHelper::addSubmenu($this->getName());
        
        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

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
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_TOOLS'));

        // Add custom buttons
        $bar = JToolbar::getInstance();

        // Go to script manager
        $link = JRoute::_('index.php?option=com_crowdfunding&view=dashboard', false);
        $bar->appendButton('Link', 'dashboard', JText::_('COM_CROWDFUNDING_DASHBOARD'), $link);
    }

    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_TOOLS'));

        JHtml::_('bootstrap.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('Prism.ui.pnotify');
        JHtml::_('Prism.ui.joomlaHelper');
        JHtml::_('Prism.ui.serializeJson');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }

    protected function prepareAcymailing()
    {
        // Get projects
        $this->projects = $this->get('Projects');
        array_unshift($this->projects, array(
            'id' => '',
            'title' => JText::_('COM_CROWDFUNDING_SELECT_PROJECT')
        ));

        // Get lists
        $this->lists = $this->get('AcyLists');
        array_unshift($this->lists, array(
            'id' => '',
            'name' => JText::_('COM_CROWDFUNDING_SELECT_LIST')
        ));
    }
}
