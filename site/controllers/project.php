<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding project controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerProject extends Prism\Controller\Form\Frontend
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelProjectbasic|JModelLegacy    The model.
     * @since    1.5
     */
    public function getModel($name = 'Projectbasic', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $userId = JFactory::getUser()->get('id');
        if (!$userId) {
            $redirectOptions = array(
                'force_direction' => 'index.php?option=com_users&view=login'
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }

        // Get the data from the form POST
        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'int');
        $terms  = Joomla\Utilities\ArrayHelper::getValue($data, 'terms', false, 'bool');

        $redirectOptions = array(
            'view' => 'project',
            'id'   => $itemId
        );

        if (!$this->allowSave($data)) {
            $redirectOptions = array(
                'force_direction' => JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false)
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NO_PERMISSIONS_TO_DO_ACTION'), $redirectOptions);
            return;
        }

        $model = $this->getModel();
        /** @var $model CrowdfundingModelProjectbasic */

        // Get component parameters
        $params = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Validate data.
        $validData = $model->validate($form, $data);
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        // Verify terms of use during the process of creating a project.
        if (!$itemId && !$terms && $params->get('project_terms', 0)) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_TERMS_NOT_ACCEPTED'), $redirectOptions);
            return;
        }

        // Include plugins to validate content.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        // Trigger onContentValidate event.
        $context = $this->option . '.basic';
        $results = $dispatcher->trigger('onContentValidate', array($context, &$validData, &$params));

        // If there is an error, redirect to current step.
        foreach ($results as $result) {
            if ((bool)$result['success'] === false) {
                $this->displayWarning(Joomla\Utilities\ArrayHelper::getValue($result, 'message'), $redirectOptions);
                return;
            }
        }

        try {
            $itemId = $model->save($validData);

            // Set the project ID to redirect options.
            $redirectOptions['id'] = $itemId;

            // Get the images from the session.
            $croppedImages = (array)$app->getUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT);

            // Store the images to the project record.
            if ($itemId > 0 && count($croppedImages) > 0) {
                $options = array(
                    'project_id'    => $itemId,
                    'user_id'       => $userId,
                    'source_folder' => CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT),
                );

                // Move the pictures from the temporary folder to the images folder.
                // Store the names of the pictures in project record.
                $model->updateImages($croppedImages, $options, $params);

                // Remove the pictures from the session.
                $app->setUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT, null);
            }
        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (InvalidArgumentException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Redirect to next page
        $redirectOptions = array(
            'view'   => 'project',
            'layout' => 'funding',
            'id'     => $itemId
        );

        $this->displayMessage(JText::_('COM_CROWDFUNDING_PROJECT_SUCCESSFULLY_SAVED'), $redirectOptions);
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param   array $data An array of input data.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowAdd($data = array())
    {
        $user  = JFactory::getUser();

        if ($user->authorise('core.create', 'com_crowdfunding')) {
            return true;
        }

        return false;
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array  $data An array of input data.
     * @param   string $key  The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $user     = JFactory::getUser();
        if ($user->authorise('core.edit', 'com_crowdfunding')) {
            return true;
        }

        // Validate item owner.
        if ($user->authorise('core.edit.own', 'com_crowdfunding')) {
            $itemId = ArrayHelper::getValue($data, $key);
            $userId = $user->get('id');

            // Validate item owner.
            $itemValidator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $itemId, $userId);
            if ($itemValidator->isValid()) {
                return true;
            }
        }

        return false;
    }
}
