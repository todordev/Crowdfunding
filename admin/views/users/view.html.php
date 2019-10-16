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

class CrowdfundingViewUsers extends JViewLegacy
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
    protected $projects;
    protected $amounts;

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

        $this->currency         = JoomlaFacade::getCurrency();
        $this->moneyFormatter   = JoomlaFacade::getMoneyFormatter();

        // Get user IDs.
        $usersIds         = Prism\Utilities\ArrayHelper::getIds($this->items);
        
        // Get the number of user's project.
        $statistics      = new Crowdfunding\Statistics\Users(JFactory::getDbo(), $usersIds);
        $this->projects  = $statistics->getProjectsNumber();
        $this->amounts   = $statistics->getAmounts();

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
            'a.name'          => JText::_('COM_CROWDFUNDING_NAME'),
            'a.registerDate'  => JText::_('COM_CROWDFUNDING_REGISTRATION_DATE'),
            'a.id'            => JText::_('JGRID_HEADING_ID')
        );
    }

    protected function addSidebar()
    {
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
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_USERS_MANAGER'));
        JToolbarHelper::custom('users.view', 'eye', '', JText::_('COM_CROWDFUNDING_VIEW'), false);
        JToolbarHelper::divider();
        JToolbarHelper::custom('users.backToDashboard', 'dashboard', '', JText::_('COM_CROWDFUNDING_DASHBOARD'), false);
    }

    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_USERS_MANAGER'));

        JHtml::_('bootstrap.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('Prism.ui.joomlaList');
    }
}
