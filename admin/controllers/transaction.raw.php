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

/**
 * Crowdfunding project controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerTransaction extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelTransaction    The model.
     * @since    1.5
     */
    public function getModel($name = 'Transaction', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Method to save the submitted reward status for records via AJAX.
     *
     * @throws  Exception
     * @return  void
     * @since   3.0
     */
    public function changeRewardsState()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $id    = $this->input->post->getUint('id');
        $state = $this->input->post->getUint('state');

        $response = new Prism\Response\Json();

        // Get the model
        $model = $this->getModel();

        try {
            $model->changeRewardsState($id, $state);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->success()
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_REWARD_STATE_CHANGED_SUCCESSFULLY'));

        echo $response;
        JFactory::getApplication()->close();
    }

    /**
     * Method to save the submitted transaction status values for records via AJAX.
     *
     * @throws  Exception
     * @return  void
     * @since   3.0
     */
    public function changeStatus()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $id     = $this->input->post->getUint('id');
        $status = $this->input->post->getCmd('status');

        $response = new Prism\Response\Json();

        // Get the model
        $model = $this->getModel();

        try {
            $model->changeTransactionStatus($id, $status);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->success()
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_TRANSACTION_STATUS_CHANGED_SUCCESSFULLY'));

        echo $response;
        JFactory::getApplication()->close();
    }
}
