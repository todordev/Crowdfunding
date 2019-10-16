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

class CrowdfundingViewLog extends JViewLegacy
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

    protected $files;

    protected $documentTitle;
    protected $option;

    protected $includeFiles = array(
        '/error_log',
        '/php_errorlog'
    );

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');
        $this->state  = $this->get('State');

        $layout = $this->getLayout();

        switch ($layout) {
            case 'view':
                $this->item = $this->get('Item');
                break;

            case 'files':
                $this->files = new Crowdfunding\Log\Files($this->includeFiles);
                $this->files->load();
                break;
        }

        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $this->app->input->set('hidemainmenu', true);

        $layout = $this->getLayout();

        switch ($layout) {
            case 'view':
                $this->documentTitle = JText::_('COM_CROWDFUNDING_VIEW_LOG_DATA');
                JToolbarHelper::custom('logs.delete', 'delete', '', JText::_('JTOOLBAR_DELETE'), false);
                break;

            case 'files':
                $this->documentTitle = JText::_('COM_CROWDFUNDING_VIEW_LOG_FILES');

                $bar = JToolbar::getInstance();
                $bar->appendButton('Link', 'refresh', JText::_('COM_CROWDFUNDING_RELOAD'), JRoute::_('index.php?option=com_crowdfunding&view=log&layout=files'));

                break;

        }

        JToolbarHelper::title($this->documentTitle);
        JToolbarHelper::cancel('log.cancel', 'JTOOLBAR_CLOSE');
    }
    
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        $layout = $this->getLayout();

        // Scripts
        JHtml::_('jquery.framework');
        JHtml::_('bootstrap.tooltip');

        switch ($layout) {
            case 'files':
                // HTML Helpers
                JHtml::_('prism.ui.pnotify');
                JHtml::_('Prism.ui.joomlaHelper');

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_DELETE_FILE_QUESTION');
                break;

        }

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
