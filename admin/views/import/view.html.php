<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

class CrowdfundingViewImport extends JViewLegacy
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

    protected $form;

    protected $option;

    protected $importType;
    protected $legend;
    protected $uploadTask;

    protected $resourcesInformation;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');
        
        $this->state = $this->get('State');
        $this->form  = $this->get('Form');

        $this->importType = $this->state->get('import.context');

        switch ($this->importType) {
            case 'locations':
                $this->legend     = JText::_('COM_CROWDFUNDING_IMPORT_LOCATIONS_DATA');
                $this->uploadTask = 'import.locations';

                $this->resourcesInformation = JText::_('COM_CROWDFUNDING_RESOURCES_INFORMATION2');
                break;

            case 'countries':
                $this->legend     = JText::_('COM_CROWDFUNDING_IMPORT_COUNTRIES_DATA');
                $this->uploadTask = 'import.countries';

                $this->resourcesInformation = JText::_('COM_CROWDFUNDING_RESOURCES_INFORMATION');
                break;

            case 'regions':
                $this->legend     = JText::_('COM_CROWDFUNDING_IMPORT_REGIONS_DATA');
                $this->uploadTask = 'import.regions';

                $this->resourcesInformation = JText::_('COM_CROWDFUNDING_RESOURCES_INFORMATION2');
                break;

            default: // Currencies
                $this->legend     = JText::_('COM_CROWDFUNDING_IMPORT_CURRENCY_DATA');
                $this->uploadTask = 'import.currencies';

                $this->resourcesInformation = JText::_('COM_CROWDFUNDING_RESOURCES_INFORMATION');
                break;
        }

        // Prepare actions
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
        // Add submenu
        CrowdfundingHelper::addSubmenu($this->importType);
        
        // Set toolbar items for the page
        JToolbarHelper::title(JText::sprintf('COM_CROWDFUNDING_IMPORT_MANAGER_S', $this->legend));

        // Upload
        JToolbarHelper::custom($this->uploadTask, 'upload', '', JText::_('COM_CROWDFUNDING_UPLOAD_AND_IMPORT'), false);

        JToolbarHelper::divider();
        JToolbarHelper::cancel('import.cancel');
    }

    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_IMPORT_MANAGER'));

        JHtml::_('behavior.formvalidation');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('Prism.ui.bootstrap2FileInput');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
