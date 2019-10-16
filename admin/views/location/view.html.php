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

class CrowdfundingViewLocation extends JViewLegacy
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
     * @var JApplicationSite
     */
    protected $app;

    protected $item;
    protected $form;

    protected $documentTitle;
    protected $option;
    
    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');

        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        $this->app->input->set('hidemainmenu', true);
        $isNew = ((int)$this->item->id === 0);

        $this->documentTitle = $isNew ? JText::_('COM_CROWDFUNDING_ADD_LOCATION') : JText::_('COM_CROWDFUNDING_EDIT_LOCATION');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('location.apply');
        JToolbarHelper::save2new('location.save2new');
        JToolbarHelper::save('location.save');

        if (!$isNew) {
            JToolbarHelper::cancel('location.cancel');
        } else {
            JToolbarHelper::cancel('location.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        JHtml::_('formbehavior.chosen', 'select');

        $this->document->addScript(JUri::root() . 'media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
