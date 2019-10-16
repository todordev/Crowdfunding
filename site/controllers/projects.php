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
 * Crowdfunding projects controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerProjects extends Prism\Controller\Admin
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelProjectItem    The model.
     * @since    1.5
     */
    public function getModel($name = 'ProjectItem', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function saveState()
    {
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        $userId = (int)JFactory::getUser()->get('id');
        if (!$userId) {
            $redirectOptions = array(
                'force_direction' => 'index.php?option=com_users&view=login'
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }

        // Get the data from the form
        $itemId = $this->input->get->get('id', 0, 'int');
        $state  = $this->input->get->get('state', 0, 'int');
        $state  = (!$state) ? 0 : 1;

        $return     = $this->input->get->get('return', '', 'base64');
        $returnLink = JRoute::_(CrowdfundingHelperRoute::getProjectsRoute(), false);

        // Get return link from parameters.
        if ($return !== '') {
            $returnLink = base64_decode($return);
        }

        $redirectOptions = array(
            'force_direction' => $returnLink
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelProjectItem */

        $item = $model->getItem($itemId, $userId);
        if (!$item->id) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), $redirectOptions);
            return;
        }

        try {
            $model->saveState($itemId, $userId, $state);
        } catch (RuntimeException $e) {
            $this->setMessage($e->getMessage(), 'warning');
            $this->setRedirect($returnLink);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Redirect to next page
        if (!$state) {
            $msg = JText::_('COM_CROWDFUNDING_PROJECT_STOPPED_SUCCESSFULLY');
        } else {
            $msg = JText::_('COM_CROWDFUNDING_PROJECT_LAUNCHED_SUCCESSFULLY_INFO');
        }

        $this->displayMessage($msg, $redirectOptions);
    }
}
