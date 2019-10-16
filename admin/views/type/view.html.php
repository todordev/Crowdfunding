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

class CrowdfundingViewType extends JViewLegacy
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

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ((int)$this->item->id === 0);

        $this->documentTitle = $isNew ? JText::_('COM_CROWDFUNDING_NEW_TYPE') : JText::_('COM_CROWDFUNDING_EDIT_TYPE');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('type.apply');
        JToolBarHelper::save2new('type.save2new');
        JToolbarHelper::save('type.save');

        if (!$isNew) {
            JToolbarHelper::cancel('type.cancel');
        } else {
            JToolbarHelper::cancel('type.cancel', 'JTOOLBAR_CLOSE');
        }
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
