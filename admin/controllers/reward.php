<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Facade\Joomla as JoomlaFacade;

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding reward controller class.
 *
 * @package        Crowdfunding
 * @subpackage     Components
 */
class CrowdfundingControllerReward extends Prism\Controller\Form\Backend
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelReward|JModelLegacy    The model.
     * @since    1.5
     */
    public function getModel($name = 'Reward', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data      = $this->input->post->get('jform', array(), 'array');
        $itemId    = Joomla\Utilities\ArrayHelper::getValue($data, 'id');

        $dataFile  = $this->input->files->get('jform', array(), 'array');
        $image     = Joomla\Utilities\ArrayHelper::getValue($dataFile, 'image', array(), 'array');
        $imageName = Joomla\String\StringHelper::trim(Joomla\Utilities\ArrayHelper::getValue($image, 'name'));

        $redirectOptions = array(
            'task' => $this->getTask(),
            'id'   => $itemId
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelReward */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $params Joomla\Registry\Registry */

        // Prepare amounts.
        $moneyParser    = JoomlaFacade::getMoneyParser();
        $data['amount'] = $moneyParser->parse($data['amount']);

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        try {
            $itemId = $model->save($validData);

            $redirectOptions['id'] = $itemId;

            // Upload an image
            $imagesAllowed = $params->get('rewards_images', 0);
            
            // Upload images.
            if ($imagesAllowed && !empty($imageName) && !empty($itemId)) {
                $reward = new Crowdfunding\Reward(JFactory::getDbo());
                $reward->load($itemId);

                // Get the folder where the images will be stored
                $imagesFolder = CrowdfundingHelper::getImagesFolder($reward->getUserId(), JPATH_ROOT);
                if (!JFolder::exists($imagesFolder)) {
                    CrowdfundingHelper::createFolder($imagesFolder);
                }

                $images = $model->uploadImage($image, $imagesFolder);
                if (!empty($images)) {
                    $model->storeImage($images, $imagesFolder, $itemId);
                }
            }
        } catch (RuntimeException $e) {
            $this->displayError($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_REWARD_SAVED'), $redirectOptions);
    }

    public function removeImage()
    {
        // Check for request forgeries.
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        // Get item id
        $itemId    = $this->input->get->getInt('id');

        $redirectOptions = array(
            'view' => 'reward',
            'layout' => 'edit',
            'id' => $itemId
        );

        // Create an reward object.
        $reward = new Crowdfunding\Reward(JFactory::getDbo());
        $reward->load($itemId);

        // Check for registered user
        if (!$reward->getId()) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_INVALID_IMAGE'), $redirectOptions);
            return;
        }

        $imagesFolder = CrowdfundingHelper::getImagesFolder($reward->getUserId(), JPATH_ROOT);

        try {
            $model = $this->getModel();
            $model->removeImage($itemId, $imagesFolder);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_IMAGE_DELETED'), $redirectOptions);
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

        // Get item id
        $return    = $this->input->get->get('return');

        $redirectOptions = array(
            'force_direction' => base64_decode($return)
        );

        $transactionId = $this->input->get->getInt('txn_id');
        $state         = $this->input->get->getInt('state');

        $state = (!$state) ? Prism\Constants::UNPUBLISHED : Prism\Constants::PUBLISHED;

        if (!$transactionId) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_TRANSACTION'), $redirectOptions);
            return;
        }

        try {
            $model = $this->getModel();
            /** @var $model CrowdfundingModelReward */

            $model->updateRewardState($transactionId, $state);
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
