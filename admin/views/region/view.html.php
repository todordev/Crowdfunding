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

class CrowdfundingViewRegion extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;
    
    /**
     * @var JApplicationAdministrator
     */
    public $app;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;
    protected $item;
    protected $form;

    protected $documentTitle;
    protected $option;

    protected $sidebar;

    protected $countryCode;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        
        $this->option = $this->app->input->get('option');
        $this->state  = $this->get('State');
        $this->item   = $this->get('Item');
        $this->form   = $this->get('Form');

        $this->countryCode  = $this->app->input->getCmd('code');
        if ($this->item !== null) {
            $this->countryCode = $this->item->country_code;
        }
        
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

        $this->documentTitle = $isNew ? JText::_('COM_CROWDFUNDING_ADD_REGION') : JText::_('COM_CROWDFUNDING_EDIT_REGION');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('region.apply');
        JToolbarHelper::save2new('region.save2new');
        JToolbarHelper::save('region.save');

        if (!$isNew) {
            JToolbarHelper::cancel('region.cancel');
        } else {
            JToolbarHelper::cancel('region.cancel', 'JTOOLBAR_CLOSE');
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
