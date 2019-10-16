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
 * Crowdfunding story controller.
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerStory extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingModelStory    The model.
     * @since    1.5
     */
    public function getModel($name = 'Story', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function uploadImage()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId = JFactory::getUser()->get('id');
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        $projectId = $this->input->post->get('id', 0, 'int');

        // Validate place owner.
        if ($projectId > 0) {
            $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $projectId, $userId);
            if (!$validator->isValid()) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                    ->failure();

                echo $response;
                $app->close();
            }
        }

        $file = $this->input->files->get('pitch_image');
        if (!$file || empty($file['name'])) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            $app->close();
        }

        $fileDataResponse  = null;

        try {
            $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT);

            $model    = $this->getModel();
            /** @var $model CrowdfundingModelStory */

            $fileData = $model->uploadImage($file, $temporaryFolder);

            if (array_key_exists('filename', $fileData) && $fileData['filename'] !== '') {
                $filename = basename($fileData['filename']);
                $fileUrl  = JUri::base() . CrowdfundingHelper::getTemporaryImagesFolderUri() . '/' . $filename;
                $app->setUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT, $filename);

                $fileDataResponse = array(
                    'url'    => $fileUrl,
                    'width'  => $fileData['attributes']['width'],
                    'height' => $fileData['attributes']['height']
                );
            }

            if ($fileDataResponse === null) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setContent(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'))
                    ->failure();

                echo $response;
                $app->close();
            }

            $params = JComponentHelper::getParams('com_crowdfunding');
            if ($fileDataResponse['width'] < $params->get('pitch_image_width', 600) || $fileDataResponse['height'] < $params->get('pitch_image_height', 400)) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setContent(JText::sprintf('COM_CROWDFUNDING_ERROR_PICTURE_SIZE_S', $params->get('pitch_image_width', 600), $params->get('pitch_image_height', 400)))
                    ->failure();

                echo $response;
                $app->close();
            }

        } catch (InvalidArgumentException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent($e->getMessage())
                ->failure();

            echo $response;
            $app->close();
        } catch (RuntimeException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent($e->getMessage())
                ->failure();

            echo $response;
            $app->close();
        } catch (Exception $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_IMAGE_SAVED_SUCCESSFULLY'))
            ->setData($fileDataResponse)
            ->success();

        echo $response;
        $app->close();
    }

    public function cropImage()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId = JFactory::getUser()->get('id');
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingModelStory */

        $projectId = $this->input->post->getUint('id');

        // If there is a project, validate the owner.
        if ($projectId > 0) {
            $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $projectId, $userId);
            if (!$validator->isValid()) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PLACE'))
                    ->failure();

                echo $response;
                $app->close();
            }
        }

        $params        = JComponentHelper::getParams('com_crowdfunding');

        $fileName        = basename($app->getUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT));
        $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT);
        $temporaryFile   = JPath::clean($temporaryFolder .'/'. $fileName, '/');
        if (!$fileName || !JFile::exists($temporaryFile)) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_FILE_DOES_NOT_EXIST'))
                ->failure();

            echo $response;
            $app->close();
        }

        $fileUrl = '';

        try {
            $options = [
                'width'             => $this->input->getFloat('width'),
                'height'            => $this->input->getFloat('height'),
                'x'                 => $this->input->getFloat('x'),
                'y'                 => $this->input->getFloat('y'),
                'temporary_folder'  => $temporaryFolder
            ];

            // Crop and resize the picture.
            $image = $model->cropImage($temporaryFile, $options, $params);

            // If there is a project, store the images to database.
            // If there is NO project, store the images in the session.
            if ($image !== '' && $projectId > 0) {
                $options = [
                    'project_id'    => $projectId,
                    'user_id'       => $userId,
                    'source_folder' => $temporaryFolder,
                    'media_folder'  => CrowdfundingHelper::getImagesFolder(0, JPATH_ROOT)
                ];

                $model->updatePitchImage($image, $options);

                // Get the folder of the images where the pictures will be stored.
                $fileUrl = JUri::base() . CrowdfundingHelper::getImagesFolderUri() .'/'. $image;
            }
        } catch (RuntimeException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent($e->getMessage())
                ->failure();

            echo $response;
            $app->close();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_IMAGE_SAVED_SUCCESSFULLY'))
            ->setData(['url' => $fileUrl])
            ->success();

        echo $response;
        $app->close();
    }

    public function cancelImageCrop()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId   = (int)JFactory::getUser()->get('id');
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        try {
            // Remove old temporary image.
            $oldImage = basename($app->getUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT, ''));
            if ($oldImage !== '') {
                // Get the folder where the images will be stored
                $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT);

                // Remove old image if exists.
                $oldImage = JPath::clean($temporaryFolder .'/'. basename($oldImage), '/');
                if (JFile::exists($oldImage)) {
                    JFile::delete($oldImage);
                }

                // Set the name of the image in the session.
                $app->setUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT, null);
            }
        } catch (Exception $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_IMAGE_RESET_SUCCESSFULLY'))
            ->success();

        echo $response;
        $app->close();
    }

    public function removeImage()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId   = (int)JFactory::getUser()->get('id');
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        $itemId = $this->input->post->getUint('id');

        // Validate project owner.
        $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $itemId, $userId);
        if (!$itemId || !$validator->isValid()) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_IMAGE'))
                ->failure();

            echo $response;
            $app->close();
        }

        try {
            $mediaFolder = CrowdfundingHelper::getImagesFolder(0, JPATH_ROOT);

            $model = $this->getModel();
            $model->removeImage($itemId, $userId, $mediaFolder);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setContent(JText::_('COM_CROWDFUNDING_IMAGE_DELETED'))
            ->success();

        echo $response;
        $app->close();
    }
}
