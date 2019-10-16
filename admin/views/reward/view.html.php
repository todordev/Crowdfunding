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

class CrowdfundingViewReward extends JViewLegacy
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

    protected $rewardsImagesUri;
    protected $allowedImages;
    protected $projectTitle;
    protected $rewards;
    protected $deliveryDate;
    protected $socialProfile;
    protected $profileLink;
    protected $imagesFolder;
    protected $rewardOwnerId;
    protected $returnUrl;

    protected $documentTitle;
    protected $option;
    protected $layout;
    
    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');
        
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        $this->params     = $this->state->get('params');

        // Get rewards images URI.
        if (!empty($this->item->id)) {
            $userId = CrowdfundingHelper::getUserIdByRewardId($this->item->id);
            $uri = JUri::getInstance();
            $this->rewardsImagesUri = $uri->toString(array('scheme', 'host')) . '/' . CrowdfundingHelper::getImagesFolderUri($userId);
        }

        // Get project title.
        $projectId = $this->app->getUserState('com_crowdfunding.rewards.pid');
        $this->projectTitle = CrowdfundingHelper::getProjectTitle($projectId);

        // Get a property that give us ability to upload images.
        $this->allowedImages = $this->params->get('rewards_images', 0);

        $this->layout = $this->getLayout();

        if (strcmp('default', $this->layout) === 0) {
            $this->prepareDefaultLayout();
        }

        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function prepareDefaultLayout()
    {
        $this->rewards = new Crowdfunding\User\Rewards(JFactory::getDbo());
        $this->rewards->load(array('reward_id' => $this->item->id));

        $this->rewardOwnerId = CrowdfundingHelper::getUserIdByRewardId($this->item->id);

        $dateValidator = new Prism\Validator\Date($this->item->delivery);
        $this->deliveryDate = $dateValidator->isValid() ? JHtml::_('date', $this->item->delivery, JText::_('DATE_FORMAT_LC3')) : '--';

        $this->imagesFolder = CrowdfundingHelper::getImagesFolderUri($this->rewardOwnerId);

        // Get social profile
        $socialPlatform = $this->params->get('integration_social_platform');

        if ($socialPlatform) {
            $this->socialProfile = CrowdfundingHelper::prepareIntegration($socialPlatform, $this->rewardOwnerId);
            $this->profileLink   = $this->socialProfile->getLink();
        }

        $this->returnUrl = base64_encode('index.php?option=com_crowdfunding&view=reward&id='.$this->item->id);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        if (strcmp('default', $this->layout) !== 0) { // Layout 'edit'.
            $this->app->input->set('hidemainmenu', true);
            $isNew = ((int)$this->item->id === 0);

            $this->documentTitle = $isNew ?
                JText::sprintf('COM_CROWDFUNDING_NEW_REWARD', $this->projectTitle) :
                JText::sprintf('COM_CROWDFUNDING_EDIT_REWARD', $this->projectTitle);

            JToolbarHelper::title($this->documentTitle);

            JToolbarHelper::apply('reward.apply');
            JToolbarHelper::save2new('reward.save2new');
            JToolbarHelper::save('reward.save');

            if (!$isNew) {
                JToolbarHelper::cancel('reward.cancel');
            } else {
                JToolbarHelper::cancel('reward.cancel', 'JTOOLBAR_CLOSE');
            }

        } else { // Layout 'default'.
            $this->documentTitle = JText::sprintf('COM_CROWDFUNDING_VIEW_REWARD_S_PROJECT_S', $this->item->title, $this->projectTitle);
            JToolbarHelper::title($this->documentTitle);
            JToolbarHelper::cancel('reward.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        JHtml::_('Prism.ui.bootstrap2FileInput');
        JHtml::_('Prism.ui.pnotify');
        JHtml::_('Prism.ui.joomlaHelper');
        JHtml::_('formbehavior.chosen', 'select');

        // Add scripts
        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
