<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding log controller class.
 *
 * @package        Crowdfunding
 * @subpackage     Components
 * @since          1.6
 */
class CrowdfundingControllerLog extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    object    The model.
     * @since    1.5
     */
    public function getModel($name = 'Log', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Remove a log file.
     * @throws \Exception
     */
    public function remove()
    {
        // Create response object
        $response = new Prism\Response\Json();

        $file = $this->input->post->get('file', null, 'raw');

        if (!$file) {
            JFactory::getApplication()->close(404);
        }

        $model = $this->getModel();
        /** @var $model CrowdfundingModelLog */

        try {
            // Clean and prepare the file.
            $fileSource = $model->prepareFile(JPath::clean($file));

            if (!$model->deleteFile($fileSource)) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setContent(JText::_('COM_CROWDFUNDING_ERROR_LOG_FILE_CANNOT_BE_REMOVED'))
                    ->failure();

                echo $response;
                JFactory::getApplication()->close();
            }
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
            ->setContent(JText::_('COM_CROWDFUNDING_LOG_FILE_REMOVED'))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    public function download()
    {
        $file = $this->input->get->get('file', null, 'raw');

        if (!$file) {
            JFactory::getApplication()->close(404);
        }

        $model = $this->getModel();
        /** @var $model CrowdfundingModelLog */

        try {
            $fileSource = $model->prepareFile(JPath::clean($file));

            $fileName = basename($fileSource);
            $fileSize = filesize($fileSource);

            $doc = JFactory::getDocument();

            if (strcmp('error_log', $fileName) === 0) {
                JResponse::setHeader('Content-Type', 'text/plain', true);
                $doc->setMimeEncoding('text/plain');
            } else {
                JResponse::setHeader('Content-Type', 'application/octet-stream', true);
                JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);

                $doc->setMimeEncoding('application/octet-stream');
            }

            JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
            JResponse::setHeader('Pragma', 'no-cache', true);
            JResponse::setHeader('Expires', '0', true);
            JResponse::setHeader('Content-Disposition', 'attachment; filename=' . $fileName, true);
            JResponse::setHeader('Content-Length', $fileSize, true);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        echo file_get_contents($fileSource);
        JFactory::getApplication()->close();
    }
}
