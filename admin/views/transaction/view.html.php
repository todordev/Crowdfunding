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

class CrowdfundingViewTransaction extends JViewLegacy
{
    /**
     * @var JApplicationAdministrator
     */
    public $app;

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

    protected $layoutsBasePath;
    protected $extraData;

    protected $documentTitle;
    protected $option;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');

        $this->layoutsBasePath = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'layouts');
        
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        $this->params = $this->state->get('params');

        $this->extraData = array();
        if (!empty($this->item->extra_data)) {
            $this->extraData = json_decode($this->item->extra_data, true);
        }

        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $this->app->input->set('hidemainmenu', true);

        $this->documentTitle = JText::_('COM_CROWDFUNDING_EDIT_TRANSACTION');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('transaction.apply');
        JToolbarHelper::save('transaction.save');

        JToolbarHelper::cancel('transaction.cancel');
    }

    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        JHtml::_('formbehavior.chosen', 'select');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
