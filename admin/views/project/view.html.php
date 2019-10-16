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

class CrowdfundingViewProject extends JViewLegacy
{
    /**
     * @var JApplicationAdministrator
     */
    protected $app;

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

    protected $imagesUrl;
    protected $minAmount;
    protected $maxAmount;
    protected $minDays;
    protected $maxDays;
    protected $checkedDays;
    protected $checkedDate;
    protected $fundingDuration;

    protected $documentTitle;
    protected $option;

    public function display($tpl = null)
    {
        $this->app      = JFactory::getApplication();
        $this->option   = $this->app->input->get('option');
        $this->state    = $this->get('State');
        $this->item     = $this->get('Item');
        $this->form     = $this->get('Form');

        // Prepare parameters
        $this->params = $this->state->get('params');

        $imagesFolder    = $this->params->get('images_directory', 'images/crowdfunding');
        $this->imagesUrl = JUri::root() . $imagesFolder;

        // Set minimum values - days, amount,...
        $this->minAmount = (float)$this->params->get('project_amount_minimum', 100);
        $this->maxAmount = (float)$this->params->get('project_amount_maximum');

        $this->minDays = (int)$this->params->get('project_days_minimum', 30);
        $this->maxDays = (int)$this->params->get('project_days_maximum');

        $this->prepareFundingDurationType();

        // Prepare actions, behaviors, scripts and document
        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function prepareFundingDurationType()
    {
        $this->fundingDuration = $this->params->get('project_funding_duration');

        switch ($this->fundingDuration) {
            case 'days': // Only days type is enabled
                $this->checkedDays = 'checked="checked"';
                break;

            case 'date': // Only date type is enabled
                $this->checkedDate = 'checked="checked"';
                break;

            default: // Both ( days and date ) types are enabled
                $fundingStartDateValidator = new Prism\Validator\Date($this->item->funding_end);

                $this->checkedDays = 0;
                $this->checkedDate = '';

                if ($this->item->funding_days > 0) {
                    $this->checkedDays = 'checked="checked"';
                    $this->checkedDate = '';
                } elseif ($fundingStartDateValidator->isValid()) {
                    $this->checkedDays = '';
                    $this->checkedDate = 'checked="checked"';
                }

                // If missing both, select days
                if (!$this->checkedDays and !$this->checkedDate) {
                    $this->checkedDays = 'checked="checked"';
                }
                break;
        }
    }

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ((int)$this->item->id === 0);

        $this->documentTitle = $isNew ? JText::_('COM_CROWDFUNDING_NEW_PROJECT') : JText::_('COM_CROWDFUNDING_EDIT_PROJECT');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('project.apply');
        JToolbarHelper::save('project.save');

        if (!$isNew) {
            JToolbarHelper::cancel('project.cancel');
        } else {
            JToolbarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('Prism.ui.bootstrap2FileInput');
        JHtml::_('Prism.ui.bootstrap2Typeahead');
        JHtml::_('Prism.ui.joomlaHelper');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
