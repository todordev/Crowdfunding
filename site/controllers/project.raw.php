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
 * Crowdfunding project controller.
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerProject extends JControllerLegacy
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
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function getLocations()
    {
        $query = $this->input->get->get('query', '', 'string');

        $response = new Prism\Response\Json();

        try {
            $locations = new Crowdfunding\Locations(JFactory::getDbo());
            $locations->search(['query' => $query, 'mode' => 1]);

            $locationData  = $locations->toArray();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->setData($locationData)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function loadProject()
    {
        // Get the input
        $query = $this->input->get->get('query', '', 'string');

        $response = new Prism\Response\Json();

        try {
            $options = array(
                'published' => Prism\Constants::PUBLISHED,
                'approved'  => Prism\Constants::APPROVED,
            );

            $projects = new Crowdfunding\Projects(JFactory::getDbo());
            $projects->loadByString($query, $options);

            $projectData = $projects->toOptions();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->setData($projectData)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
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

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingModelProjectbasic */

        $projectId = $this->input->post->get('id', 0, 'int');

        // Validate project owner.
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

        $file = $this->input->files->get('project_image');
        if (!$file) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            $app->close();
        }

        $fileDataResponse  = null;

        try {
            if (!empty($file['name'])) {
                // Get the folder where the images will be stored
                $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT);

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
            ->setContent(JText::_('COM_CROWDFUNDING_IMAGE_SAVED'))
            ->setData($fileDataResponse)
            ->success();

        echo $response;
        $app->close();
    }

    public function cropImage()
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

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingModelProjectbasic */

        $projectId = $this->input->post->getUint('id');

        // If there is a project, validate the owner.
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

        // Get the filename from the session.
        $fileName = basename($app->getUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT));
        $temporaryFile = JPath::clean(CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT) .'/'. $fileName, '/');
        if (!$fileName || !JFile::exists($temporaryFile)) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_FILE_DOES_NOT_EXIST'))
                ->failure();

            echo $response;
            $app->close();
        }

        $imageUrl = '';

        try {
            // Get the folder where the images will be stored
            $destination = CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT);

            $params      = JComponentHelper::getParams('com_crowdfunding');

            $options = array(
                'width'         => $this->input->getFloat('width'),
                'height'        => $this->input->getFloat('height'),
                'x'             => $this->input->getFloat('x'),
                'y'             => $this->input->getFloat('y'),
                'destination'   => $destination
            );

            // Resize the picture.
            $images     = $model->cropImage($temporaryFile, $options, $params);
            $imageName  = basename(Joomla\Utilities\ArrayHelper::getValue($images, 'image'));

            // Remove the temporary images if they exist.
            $model->removeCroppedImages($app);

            // If there is a project, store the images to database.
            // If there is NO project, store the images in the session.
            if (count($images) > 0) {
                if ($projectId > 0) {
                    $options = array(
                        'project_id'    => $projectId,
                        'user_id'       => $userId,
                        'source_folder' => $destination,
                    );

                    $model->updateImages($images, $options, $params);
                    $app->setUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT, null);

                    // Get the folder of the images where the pictures will be stored.
                    $imageUrl = JUri::base() . CrowdfundingHelper::getImagesFolderUri() .'/'. $imageName;
                } else {
                    $app->setUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT, $images);

                    // Get the temporary folder where the images will be stored.
                    $imageUrl = JUri::base() . CrowdfundingHelper::getTemporaryImagesFolderUri() . '/' . $imageName;
                }
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
            ->setContent(JText::_('COM_CROWDFUNDING_IMAGE_SAVED'))
            ->setData(['src' => $imageUrl])
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

        $userId = JFactory::getUser()->get('id');
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
            ->setContent(JText::_('COM_CROWDFUNDING_IMAGE_RESET_SUCCESSFULLY'))
            ->success();

        echo $response;
        $app->close();
    }

    public function removeImage()
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

        $itemId  = $this->input->post->getUint('id');

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

    public function removeCroppedImages()
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
            $model = $this->getModel();
            $model->removeCroppedImages($app);
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

    /**
     * Method to follow a project.
     *
     * @throws Exception
     * @return  void
     */
    public function follow()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId  = JFactory::getUser()->get('id');

        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_USER'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get project ID.
        $projectId  = $this->input->post->getInt('pid', 0);

        if (!$projectId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setContent(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            $app->close();
        }

        $state = $this->input->post->getInt('state', 0);
        $state = (!$state) ? Prism\Constants::UNFOLLOWED : Prism\Constants::FOLLOWED;

        try {
            $user = new Crowdfunding\User\User(JFactory::getDbo());
            $user->setId($userId);

            if (!$state) {
                $user->unfollow($projectId);
            } else {
                $user->follow($projectId);
            }
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfunding');
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $responseData = array(
            'state' => $state
        );

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setData($responseData)
            ->success();

        echo $response;
        $app->close();
    }
}
