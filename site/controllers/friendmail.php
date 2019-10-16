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
 * Crowdfunding friend mail controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerFriendmail extends Prism\Controller\Form\Frontend
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelFriendMail|JModelLegacy    The model.
     * @since    1.5
     */
    public function getModel($name = 'Friendmail', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function send()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $redirectOptions = array(
            'view' => 'discover'
        );

        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $params Joomla\Registry\Registry */

        if (!$params->get('security_display_friend_form', 0)) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_CANT_SEND_MAIL'), $redirectOptions);
            return;
        }

        // Get the data from the form POST
        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'uint');

        // Get project slug and check for valid project.
        $slug   = Crowdfunding\Helper\ProjectHelper::getSlug($itemId);
        if (!$slug) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), $redirectOptions);
            return;
        }

        // Prepare redirect link
        $redirectOptions = array(
            'view' => 'friendmail',
            'id'   => $slug
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelFriendMail */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Test if the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        try {
            $model->send($validData);
        } catch (RuntimeException $e) {
            JLog::add($e->getMessage(), JLog::WARNING, 'com_crowdfunding');
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Redirect to next page
        $this->displayMessage(JText::_('COM_CROWDFUNDING_FRIEND_MAIL_SUCCESSFULLY_SEND'), $redirectOptions);
    }
}
