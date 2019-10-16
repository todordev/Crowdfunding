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

class CrowdfundingViewDashboard extends JViewLegacy
{
    use Crowdfunding\Helper\NumberHelper;

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

    protected $option;

    protected $popular;
    protected $mostFunded;
    protected $latestStarted;
    protected $latestCreated;
    protected $currency;
    protected $moneyFormatter;
    protected $numberFormatter;
    protected $version;
    protected $prismVersion;
    protected $prismVersionLowerMessage;

    protected $sidebar;

    public function display($tpl = null)
    {
        $this->option = JFactory::getApplication()->input->get('option');
        
        $this->state  = $this->get('State');
        $this->params = $this->state->get('params');

        $this->version = new Crowdfunding\Version();

        // Load Prism library version
        if (!class_exists('Prism\\Version')) {
            $this->prismVersion = JText::_('COM_CROWDFUNDING_PRISM_LIBRARY_DOWNLOAD');
        } else {
            $prismVersion       = new Prism\Version();
            $this->prismVersion = $prismVersion->getShortVersion();

            if (version_compare($this->prismVersion, $this->version->requiredPrismVersion, '<')) {
                $this->prismVersionLowerMessage = JText::_('COM_CROWDFUNDING_PRISM_LIBRARY_LOWER_VERSION');
            }
        }

        // Get popular projects.
        $this->popular = new Crowdfunding\Statistics\Projects\Popular(JFactory::getDbo());
        $this->popular->load(array('limit' => 5));

        // Get popular most funded.
        $this->mostFunded = new Crowdfunding\Statistics\Projects\MostFunded(JFactory::getDbo());
        $this->mostFunded->load(array('limit' => 5));

        // Get latest started.
        $options = array(
             'limit' => 10,
             'order_by' => 'funding_start',
             'order_direction' => 'DESC'
        );
        $this->latestStarted = new Crowdfunding\Statistics\Projects\Latest(JFactory::getDbo());
        $this->latestStarted->load($options);

        // Get latest created.
        $options = array(
            'limit' => 10,
            'order_by' => 'created',
            'order_direction' => 'DESC'
        );
        $this->latestCreated = new Crowdfunding\Statistics\Projects\Latest(JFactory::getDbo());
        $this->latestCreated->load($options);

        $this->currency        = JoomlaFacade::getCurrency();
        $this->moneyFormatter  = JoomlaFacade::getMoneyFormatter();

        $this->numberFormatter = $this->getNumberFormatter(0);

        // Add submenu
        CrowdfundingHelper::addSubmenu($this->getName());

        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_DASHBOARD'));

        JToolbarHelper::preferences('com_crowdfunding');
        JToolbarHelper::divider();

        // Help button
        $bar = JToolbar::getInstance();
        $bar->appendButton('Link', 'help', JText::_('JHELP'), JText::_('COM_CROWDFUNDING_HELP_URL'));
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_DASHBOARD'));
    }
}
