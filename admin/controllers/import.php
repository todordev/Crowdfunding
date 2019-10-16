<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding import controller.
 *
 * @package      Crowdfunding
 * @subpackage   Components
 */
class CrowdfundingControllerImport extends Prism\Controller\Form\Backend
{
    /**
     * @param string $name
     * @param string $prefix
     * @param array  $config
     *
     * @return CrowdfundingModelImport
     */
    public function getModel($name = 'Import', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function currencies()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            'view' => 'currencies'
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelImport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        $fileData = ArrayHelper::getValue($data, 'data');
        if (!$fileData || empty($fileData['name'])) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        }

        try {
            $filePath = $model->uploadFile($fileData, 'currencies');

            $resetId   = ArrayHelper::getValue($data, 'reset_id', false, 'bool');
            $removeOld = ArrayHelper::getValue($data, 'remove_old', false, 'bool');
            if ($removeOld) {
                $model->removeAll('currencies');
            }

            $model->importCurrencies($filePath, $resetId);
        } catch (RuntimeException $e) {
            $this->displayError($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_CURRENCIES_IMPORTED'), $redirectOptions);
    }

    public function locations()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            'view' => 'locations',
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelImport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        $fileData = ArrayHelper::getValue($data, 'data');
        if (!$fileData || empty($fileData['name'])) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        }

        try {
            $filePath = $model->uploadFile($fileData, 'locations');

            $options = array(
                'truncate' => ArrayHelper::getValue($data, 'remove_old', false, 'bool'),
                'minimum_population' => ArrayHelper::getValue($data, 'minimum_population', 0, 'int'),
                'country_code' => ArrayHelper::getValue($data, 'country', '')
            );

            $model->importLocations($filePath, $options);
        } catch (RuntimeException $e) {
            $this->displayError($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_LOCATIONS_IMPORTED'), $redirectOptions);
    }

    public function countries()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            'view' => 'countries',
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelImport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        $fileData = ArrayHelper::getValue($data, 'data');
        if (!$fileData || empty($fileData['name'])) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        }

        try {
            $filePath = $model->uploadFile($fileData, 'countries');

            $resetId   = ArrayHelper::getValue($data, 'reset_id', false, 'bool');
            $removeOld = ArrayHelper::getValue($data, 'remove_old', false, 'bool');
            if ($removeOld) {
                $model->removeAll('countries');
            }

            $model->importCountries($filePath, $resetId);
        } catch (RuntimeException $e) {
            $this->displayError($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_COUNTRIES_IMPORTED'), $redirectOptions);
    }

    public function regions()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            'view' => 'countries',
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelImport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        $fileData = ArrayHelper::getValue($data, 'data');
        if (empty($fileData) || empty($fileData['name'])) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        }

        try {
            $filePath = $model->uploadFile($fileData, 'regions');

            $options = array(
                'truncate'     => ArrayHelper::getValue($data, 'remove_old', false, 'bool'),
                'country_code' => ArrayHelper::getValue($data, 'country', '')
            );

            $model->importRegions($filePath, $options);
        } catch (RuntimeException $e) {
            $this->displayError($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_REGIONS_IMPORTED'), $redirectOptions);
    }

    public function cancel($key = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $view = $app->getUserState('import.context', 'currencies');

        // Redirect to locations if the view is 'states'.
        if (strcmp('regions', $view) === 0) {
            $view = 'countries';
        }

        $link = $this->defaultLink . '&view=' . $view;
        $this->setRedirect(JRoute::_($link, false));
    }
}
