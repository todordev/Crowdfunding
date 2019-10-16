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

class CrowdfundingViewUser extends JViewLegacy
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

    protected $currency;
    protected $moneyFormatter;
    protected $projects;
    protected $investedAmount;
    protected $investedTransactions;
    protected $receivedAmount;
    protected $receivedTransactions;
    protected $socialProfile;
    protected $profileLink;
    protected $rewards;
    protected $returnUrl;

    protected $documentTitle;
    protected $option;
    
    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');

        // Get user ID
        $userId = $this->app->input->getInt('id');

        $model = $this->getModel();

        $this->state = $model->getState();
        $this->item  = $model->getItem($userId);

        $this->params = JComponentHelper::getParams($this->option);

        $this->currency         = JoomlaFacade::getCurrency();
        $this->moneyFormatter   = JoomlaFacade::getMoneyFormatter();

        // Get number of rewards.
        $statistics      = new Crowdfunding\Statistics\User(JFactory::getDbo(), $this->item->id);
        $this->projects  = $statistics->getProjectsNumber();

        $amounts   = $statistics->getAmounts();

        if (!empty($amounts['invested'])) {
            $this->investedAmount = (float)$amounts['invested']['amount'];
            $this->investedTransactions = (int)$amounts['invested']['number'];
        }

        if (!empty($amounts['received'])) {
            $this->receivedAmount = (float)$amounts['received']['amount'];
            $this->receivedTransactions = (int)$amounts['received']['number'];
        }

        // Get social profile
        if ($this->params->get('integration_social_platform')) {
            $this->socialProfile = CrowdfundingHelper::prepareIntegration($this->params->get('integration_social_platform'), $this->item->id);
            $this->profileLink   = $this->socialProfile->getLink();
        }

        $this->rewards = new Crowdfunding\User\Rewards(JFactory::getDbo());
        $this->rewards->load(array('user_id' => $this->item->id));

        $this->returnUrl = base64_encode('index.php?option=com_crowdfunding&view=user&id='.$this->item->id);

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

        $this->documentTitle = JText::_('COM_CROWDFUNDING_VIEW_USER');

        JToolbarHelper::title($this->documentTitle);

        // Refresh page.
        $bar = JToolbar::getInstance();
        $bar->appendButton('Link', 'refresh', JText::_('COM_CROWDFUNDING_REFRESH'), JRoute::_('index.php?option=com_crowdfunding&view=user&id='.$this->item->id));

        JToolbarHelper::cancel('user.cancel', 'JTOOLBAR_CLOSE');
    }

    /**
     * Method to set up the document properties
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        // Scripts
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('Prism.ui.pnotify');
        JHtml::_('Prism.ui.joomlaHelper');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
