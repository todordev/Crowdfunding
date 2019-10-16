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
class CrowdfundingControllerRewards extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelRewards|bool  The model.
     * @since    1.5
     */
    public function getModel($name = 'Rewards', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to remove records via AJAX.
     *
     * @throws  Exception
     * @return  void
     */
    public function remove()
    {
        // Get the input
        $app    = JFactory::getApplication();
        $pks    = $app->input->post->get('rid', array(), 'array');
        $userId = JFactory::getUser()->get('id');

        $response = new Prism\Response\Json();

        // Sanitize the input
        $pks = Joomla\Utilities\ArrayHelper::toInteger($pks);

        // Validate user
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Validate primary keys
        if (!$pks) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARDS_SELECTED'))
                ->failure();

            echo $response;
            $app->close();
        }

        $rewardId = Joomla\Utilities\ArrayHelper::getValue($pks, 0);

        // Validate reward owner.
        $validator = new Crowdfunding\Validator\Reward\Owner(JFactory::getDbo(), $rewardId, $userId);
        if (!$rewardId || !$validator->isValid()) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARDS_SELECTED'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the model
        $model = $this->getModel();

        try {
            $reward = new Crowdfunding\Reward(JFactory::getDbo());
            $reward->load($rewardId);

            // If the reward is part of transaction,
            // set it as trashed.
            if ($reward->isSelectedByUser()) {
                $reward->trash();
            } else {
                // Get the folder where the images are stored
                $imagesFolder = CrowdfundingHelper::getImagesFolder($userId, JPATH_ROOT);
                $model->remove($rewardId, $imagesFolder);
            }
        } catch (RuntimeException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent($e->getMessage())
                ->failure();

            echo $response;
            $app->close();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_REWARD_SUCCESSFULY_REMOVED'))
            ->success();

        echo $response;
        $app->close();
    }

    /**
     * Method to remove image via AJAX.
     *
     * @throws  Exception
     *
     * @return  void
     */
    public function removeImage()
    {
        // Get the input
        $rewardId = $this->input->post->get('rid', 0, 'int');

        $userId = JFactory::getUser()->get('id');

        $response = new Prism\Response\Json();

        // Validate user
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $params Joomla\Registry\Registry */

        if (!$params->get('rewards_images', 0)) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARD'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        // Validate reward owner.
        $validator = new Crowdfunding\Validator\Reward\Owner(JFactory::getDbo(), $rewardId, $userId);
        if (!$rewardId || !$validator->isValid()) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARD'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        // Get the model
        $model = $this->getModel();

        try {
            // Get the folder where the images will be stored
            $imagesFolder = CrowdfundingHelper::getImagesFolder($userId, JPATH_ROOT);

            $model->removeImage($rewardId, $imagesFolder);
        } catch (RuntimeException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent($e->getMessage())
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_REWARD_IMAGE_REMOVED_SUCCESSFULLY'))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
