<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding rewards controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerRewards extends Prism\Controller\Admin
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelRewards|bool    The model.
     * @since    1.5
     */
    public function getModel($name = 'Rewards', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $userId = (int)JFactory::getUser()->get('id');
        if (!$userId) {
            $redirectOptions = array(
                'force_direction' => 'index.php?option=com_users&view=login'
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }

        $params        = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $params Joomla\Registry\Registry */

        // Get the data from the form POST
        $projectId    = $this->input->post->get('id', 0, 'int');

        // Check if rewards are enabled.
        if (!$params->get('rewards_enabled', 1)) {
            $redirectOptions = array(
                'view'   => 'project',
                'layout' => 'manager',
                'id'     => $projectId
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_REWARDS_DISABLED'), $redirectOptions);
            return;
        }

        $data_        = (array)$this->input->post->get('rewards', array(), 'array');
        $actionSubmit = $this->input->post->getCmd('btn_submit', 'save');

        // Reorder items.
        $data = array();
        foreach ($data_ as $item) {
            $ordering = array_key_exists('ordering', $item) ? (int)abs($item['ordering']) : 0;
            if (!$ordering || $ordering > 30) {
                continue;
            }

            $data[$ordering] = $item;
        }
        unset($data_);

        $images       = $this->input->files->get('images', array(), 'array');

        // Get wizard type
        $wizardType   = $params->get('project_wizard_type', 'five_steps');
        $fiveStepsWizard = (strcmp($wizardType, 'five_steps') === 0);

        // If it is five steps wizard type, redirect to manager.
        // If it is six steps wizard type, redirect to extras.
        if (!$fiveStepsWizard) {
            $layout = (strcmp($actionSubmit, 'save_continue') === 0) ? 'extras' : 'rewards';
        } else {
            $layout = (strcmp($actionSubmit, 'save_continue') === 0) ? 'manager' : 'rewards';
        }

        $redirectOptions = array(
            'view'   => 'project',
            'layout' => $layout,
            'id'     => $projectId
        );

        // Validate project owner.
        $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $projectId, $userId);
        if (!$projectId || !$validator->isValid()) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), $redirectOptions);
            return;
        }

        $model = $this->getModel();
        /** @var $model CrowdfundingModelRewards */

        try {
            $validData     = $model->validate($data);
            $rewardsIds    = $model->save($validData, $projectId);
            $imagesAllowed = $params->get('rewards_images', 0);

            // Upload images.
            if ($imagesAllowed && count($images) > 0 && count($rewardsIds) > 0) {
                // Get the folder where the images will be stored
                $imagesFolder = CrowdfundingHelper::getImagesFolder($userId, JPATH_ROOT);

                if (!JFolder::exists($imagesFolder)) {
                    CrowdfundingHelper::createFolder($imagesFolder);
                }

                $options = array(
                    'temporary_path' => JFactory::getApplication()->get('tmp_path'),
                    'destination_path' => $imagesFolder,
                );

                $images  = $model->uploadImages($images, $rewardsIds, $options, $params);

                if (count($images) > 0) {
                    $model->storeImages($images, $imagesFolder);
                }
            }
        } catch (InvalidArgumentException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Redirect to next page
        $this->displayMessage(JText::_('COM_CROWDFUNDING_REWARDS_SUCCESSFULLY_SAVED'), $redirectOptions);
    }

    /**
     * Method to change state of reward.
     *
     * @throws Exception
     * @return  void
     */
    public function changeState()
    {
        // Check for request forgeries.
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        $userId = JFactory::getUser()->get('id');
        if (!$userId) {
            $redirectOptions = array(
                'force_direction' => JRoute::_('index.php?option=com_users&view=login', false)
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }

        $redirect = base64_decode($this->input->get('redirect'));
        $redirectOptions = array(
            'force_direction' => JRoute::_($redirect, false)
        );

        $txnId = $this->input->get->getUint('txn_id');
        $state = $this->input->get->getInt('state');

        $state = (!$state) ? Prism\Constants::UNPUBLISHED : Prism\Constants::PUBLISHED;

        if (!$txnId) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_TRANSACTION'), $redirectOptions);
            return;
        }

        $keys = array(
            'id' => $txnId,
            'receiver_id' => $userId
        );

        $transaction = new Crowdfunding\Transaction\Transaction(JFactory::getDbo());
        $transaction->load($keys);

        if (!$transaction->getId()) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_TRANSACTION'), $redirectOptions);
            return;
        }

        try {
            $transaction->updateRewardState($state);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        if (!$state) {
            $msg = JText::_('COM_CROWDFUNDING_REWARD_HAS_BEEN_SET_AS_NOT_SENT');
        } else {
            $msg = JText::_('COM_CROWDFUNDING_REWARD_HAS_BEEN_SET_AS_SENT');
        }

        $this->displayMessage($msg, $redirectOptions);
    }
}
