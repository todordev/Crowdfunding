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
 * Crowdfunding report controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerReport extends Prism\Controller\Form\Frontend
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelReport    The model.
     * @since    1.5
     */
    public function getModel($name = 'Report', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function send()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get the data from the form POST
        $data   = $this->input->post->get('cfreport', array(), 'array');
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, 'id');

        if (!$itemId) {
            $redirectOptions = array(
                'force_direction' => CrowdfundingHelperRoute::getReportRoute()
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), $redirectOptions);
            return;
        }

        // Get project
        $item = Crowdfunding\Project::getInstance(JFactory::getDbo(), $itemId);

        $redirectOptions = array(
            'force_direction' => CrowdfundingHelperRoute::getReportRoute($item->getId())
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelReport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Test if the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            $errors = $form->getErrors();
            $error  = array_shift($errors);
            $msg    = $error->getMessage();

            $this->displayNotice($msg, $redirectOptions);
            return;
        }

        try {
            $userId = (int)JFactory::getUser()->get('id');

            if ($userId > 0) {
                $validData['user_id'] = $userId;
            }

            $model->save($validData);
        } catch (RuntimeException $e) {
            $this->displayNotice($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Redirect to next page
        $this->displayMessage(JText::_('COM_CROWDFUNDING_REPORT_SENT_SUCCESSFULLY'), $redirectOptions);
    }
}
