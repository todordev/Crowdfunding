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
class CrowdfundingControllerRewards extends JControllerAdmin
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelReward    The model.
     * @since    1.5
     */
    public function getModel($name = 'Reward', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @throws  Exception
     * @return  void
     * @since   3.0
     */
    public function saveOrderAjax()
    {
        // Get the input
        $pks   = $this->input->post->get('cid', array(), 'array');
        $order = $this->input->post->get('order', array(), 'array');

        // Sanitize the input
        $pks   = Joomla\Utilities\ArrayHelper::toInteger($pks);
        $order = Joomla\Utilities\ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the item
        try {
            $model->saveorder($pks, $order);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response = array(
            'success' => true,
            'title'   => JText::_('COM_CROWDFUNDING_SUCCESS'),
            'text'    => JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'),
            'data'    => array()
        );

        echo json_encode($response);
        JFactory::getApplication()->close();
    }
}
