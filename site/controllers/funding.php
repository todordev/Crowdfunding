<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Facade\Joomla as JoomlaFacade;

// no direct access
defined('_JEXEC') or die;

JLoader::register('CrowdfundingModelProject', JPATH_COMPONENT .DIRECTORY_SEPARATOR. 'models' .DIRECTORY_SEPARATOR. 'project.php');

/**
 * Crowdfunding funding controller
 *
 * @package      Crowdfunding
 * @subpackage   Components
 */
class CrowdfundingControllerFunding extends Prism\Controller\Form\Frontend
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelFunding|JModelLegacy    The model.
     * @since    1.5
     */
    public function getModel($name = 'Funding', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, 'id');

        $redirectOptions = array(
            'view'   => 'project',
            'layout' => 'funding',
            'id'     => $itemId
        );

        // Get component parameters.
        $params = JComponentHelper::getParams($this->option);
        /** @var $params Joomla\Registry\Registry */

        $moneyParser  = JoomlaFacade::getMoneyParser();

        $data['goal'] = $moneyParser->parse($data['goal']);

        $model = $this->getModel();
        /** @var $model CrowdfundingModelFunding */

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

        // Validate project owner.
        $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $itemId, $userId);
        if (!$itemId || !$validator->isValid()) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), $redirectOptions);
            return;
        }

        // Include plugins to validate content.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        // Trigger onContentValidate event.
        $context = $this->option . '.funding';
        $results = $dispatcher->trigger('onContentValidate', array($context, &$validData, &$params));

        // If there is an error, redirect to current step.
        foreach ($results as $result) {
            if ((bool)$result['success'] === false) {
                $this->displayWarning(Joomla\Utilities\ArrayHelper::getValue($result, 'message'), $redirectOptions);
                return;
            }
        }

        try {
            $redirectOptions['id']     = $model->save($validData);
            $redirectOptions['layout'] = 'story';
        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_FUNDING_SUCCESSFULLY_SAVED'), $redirectOptions);
    }
}
