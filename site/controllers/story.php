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
 * Crowdfunding story controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerStory extends Prism\Controller\Form\Frontend
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelStory|JModelLegacy    The model.
     * @since    1.5
     */
    public function getModel($name = 'Story', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $userId = JFactory::getUser()->id;
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

        $redirectOptions = array(
            'view'   => 'project',
            'layout' => 'story',
            'id'     => $itemId
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelStory */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Check for valid data.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        // Validate project owner.
        $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $itemId, $userId);
        if (!$validator->isValid()) {
            $redirectOptions = array('view' => 'discover');
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), $redirectOptions);
            return;
        }

        // Get component parameters.
        $params = JComponentHelper::getParams($this->option);
        /** @var $params Joomla\Registry\Registry */

        // Include plugins to validate content.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        // Trigger onContentValidate event.
        $context = $this->option . '.story';
        $results = $dispatcher->trigger('onContentValidate', array($context, &$validData, &$params));

        // If there is an error, redirect to current step.
        foreach ($results as $result) {
            if ($result['success'] === false) {
                $this->displayWarning(Joomla\Utilities\ArrayHelper::getValue($result, 'message'), $redirectOptions);
                return;
            }
        }

        try {
            $itemId = $model->save($validData);
            $redirectOptions['id'] = $itemId;
        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (InvalidArgumentException $e) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Trigger the event onContentValidateAfterSave.
        $item    = $model->getItem($itemId, $userId);
        $results = $dispatcher->trigger('onContentValidateAfterSave', array($context, &$item, &$params));

        // If there is an error, redirect to current step.
        foreach ($results as $result) {
            if ((bool)$result['success'] === false) {
                $this->displayWarning(Joomla\Utilities\ArrayHelper::getValue($result, 'message'), $redirectOptions);
                return;
            }
        }

        // Redirect to next page
        $redirectOptions = array(
            'view'   => 'project',
            'layout' => $this->getNextLayout($params),
            'id'     => $itemId
        );

        $this->displayMessage(JText::_('COM_CROWDFUNDING_STORY_SUCCESSFULLY_SAVED'), $redirectOptions);
    }

    /**
     * Prepare next layout.
     *
     * @param Joomla\Registry\Registry $params
     *
     * @return string
     */
    protected function getNextLayout($params)
    {
        // If it is five steps wizard type, redirect to manager.
        // If it is six steps wizard type, redirect to extras.
        $layout = 'rewards';

        if ($params->get('project_wizard_type', 'five_steps') !== 'five_steps') { // six_steps
            if ((int)$params->get('rewards_enabled', 1) === Prism\Constants::DISABLED) {
                $layout = 'extras';
            }
        } else {
            if ((int)$params->get('rewards_enabled', 1) === Prism\Constants::DISABLED) {
                $layout = 'manager';
            }
        }

        return $layout;
    }
}
