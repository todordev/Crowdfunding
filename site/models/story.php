<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

// no direct access
defined('_JEXEC') or die;

JLoader::register('CrowdfundingModelProject', CROWDFUNDING_PATH_COMPONENT_SITE . '/models/project.php');

class CrowdfundingModelStory extends CrowdfundingModelProject
{
    protected $formName = 'story';

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     *
     * @throws \Exception
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $data = $app->getUserState($this->option . '.edit.story.data', array());
        if (!$data) {
            $itemId = (int)$this->getState($this->getName() . '.id');
            $userId = JFactory::getUser()->get('id');

            $data = $this->getItem($itemId, $userId);
        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param    array    $data    The form data.
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return    mixed        The record id on success, null on failure.
     * @since    1.6
     */
    public function save($data)
    {
        $projectId   = ArrayHelper::getValue($data, 'id', 0, 'int');
        $description = ArrayHelper::getValue($data, 'description');

        $keys = array(
            'id'      => $projectId,
            'user_id' => (int)JFactory::getUser()->get('id'),
        );

        // Load a record from the database.
        /** @var $row CrowdfundingTableProject */
        $row = $this->getTable();
        $row->load($keys);

        $row->set('description', $description);

        $this->prepareTableData($row, $data);

        $row->store();

        // Trigger the event onContentAfterSave.
        $this->triggerEventAfterSave($row, 'story');

        return $row->get('id');
    }

    protected function prepareTableData($table, $data)
    {
        // Prepare the video
        $pitchVideo = ArrayHelper::getValue($data, 'pitch_video');
        $table->set('pitch_video', $pitchVideo);

        // Prepare the image.
        if (!empty($data['pitch_image'])) {
            // Delete old image if I upload a new one.
            if ($table->get('pitch_image') !== '') {
                $params       = JComponentHelper::getParams($this->option);
                /** @var  $params Joomla\Registry\Registry */

                $imagesFolder = $params->get('images_directory', 'images/crowdfunding');

                // Remove an image from the filesystem
                $pitchImage   = JPath::clean($imagesFolder .'/'. $table->get('pitch_image'), '/');
                if (JFile::exists($pitchImage)) {
                    JFile::delete($pitchImage);
                }
            }

            $table->set('pitch_image', $data['pitch_image']);
        }
    }

    /**
     * Upload the image.
     *
     * @param array $uploadedFileData
     * @param string $destination
     *
     * @throws Exception
     *
     * @return array
     */
    public function uploadImage($uploadedFileData, $destination)
    {
        $uploadedFile  = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName  = ArrayHelper::getValue($uploadedFileData, 'name');
        $errorCode     = ArrayHelper::getValue($uploadedFileData, 'error');

        // Joomla! media extension parameters
        /** @var  $mediaParams Joomla\Registry\Registry */
        $mediaParams   = JComponentHelper::getParams('com_media');

        // Prepare size validator.
        $KB            = pow(1024, 2);
        $uploadMaxSize = $mediaParams->get('upload_maxsize') * $KB;
        $fileSize      = ArrayHelper::getValue($uploadedFileData, 'size', 0, 'int');

        // Prepare file size validator
        $sizeValidator   = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode);

        // Prepare image validator.
        $imageValidator  = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $mimeTypes = explode(',', $mediaParams->get('upload_mime'));
        $imageValidator->setMimeTypes($mimeTypes);

        // Get allowed image extensions from media manager options
        $imageExtensions = explode(',', $mediaParams->get('image_extensions'));
        $imageValidator->setImageExtensions($imageExtensions);

        $file = new Prism\File\File($uploadedFile);
        $file
            ->addValidator($sizeValidator)
            ->addValidator($imageValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Upload the file in temporary folder.
        $options = new Registry(array(
            'filename_length'  => 16,
            'image_type'       => \JFile::getExt($uploadedName)
        ));

        $file = new Prism\File\Image($uploadedFile);
        $fileData = $file->toFile($destination, $options);

        if (!JFile::exists($fileData['filepath'])) {
            throw new RuntimeException('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
        }

        return $fileData;
    }

    /**
     * Delete pitch image.
     *
     * @param int $projectId
     * @param int $userId
     * @param string $mediaFolder
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function removeImage($projectId, $userId, $mediaFolder)
    {
        $keys = array(
            'id'      => $projectId,
            'user_id' => $userId
        );

        $project = new Crowdfunding\Project(JFactory::getDbo());
        $project->load($keys);

        // Delete the image.
        if ($project->getPitchImage()) {
            // Remove an image from the filesystem
            $pitchImage   = JPath::clean($mediaFolder .'/'. $project->getPitchImage(), '/');
            if (JFile::exists($pitchImage)) {
                JFile::delete($pitchImage);
            }

            $project->removePitchImage();
        }
    }

    /**
     * Crop the image and generates smaller ones.
     *
     * @param string $file
     * @param array $options
     * @param Registry $params
     *
     * @throws Exception
     *
     * @return string
     */
    public function cropImage($file, array $options, Registry $params)
    {
        $image              = new Prism\File\Image($file);
        $destinationFolder  = ArrayHelper::getValue($options, 'temporary_folder');
        
        // Generate temporary file name
        $generatedName = Prism\Utilities\StringHelper::generateRandomString(24);

        // Crop the image.
        $imageOptions = new Registry;
        $imageOptions->set('create_new', Prism\Constants::NO);
        $imageOptions->set('filename', $generatedName);
        $imageOptions->set('quality', $params->get('image_quality', Prism\Constants::QUALITY_VERY_HIGH));

        // Prepare width.
        $width  = ArrayHelper::getValue($options, 'width', 600);
        $width  = ($width < 400) ? 600 : $width;
        $imageOptions->set('width', $width);

        // Prepare height.
        $height = ArrayHelper::getValue($options, 'height', 400);
        $height = ($height < 400) ? 400 : $height;
        $imageOptions->set('height', $height);

        // Prepare starting points x and y.
        $left   = ArrayHelper::getValue($options, 'x', 0);
        $imageOptions->set('x', $left);
        $top    = ArrayHelper::getValue($options, 'y', 0);
        $imageOptions->set('y', $top);

        // Crop the image.
        $fileData = $image->crop($destinationFolder, $imageOptions);

        // Resize the image.
        $image                = new Prism\File\Image($fileData['filepath']);
        $croppedImageFilepath = $fileData['filepath'];

        $width  = $params->get('pitch_image_width', 600);
        $width  = ($width < 400) ? 600 : $width;
        $imageOptions->set('width', $width);

        $height = $params->get('pitch_image_height', 400);
        $height = ($height < 400) ? 400 : $height;
        $imageOptions->set('height', $height);

        $imageOptions->set('suffix', '_pimage');
        $imageOptions->set('scale', $params->get('image_resizing_scale', JImage::SCALE_INSIDE));
        $imageOptions->set('quality', $params->get('image_quality', Prism\Constants::QUALITY_VERY_HIGH));

        $fileData       = $image->resize($destinationFolder, $imageOptions);
        $resizedImage   = $fileData['filename'];

        // Remove the temporary file.
        if (JFile::exists($file)) {
            JFile::delete($file);
        }

        // Remove the temporary cropped file.
        if (JFile::exists($croppedImageFilepath)) {
            JFile::delete($croppedImageFilepath);
        }

        return $resizedImage;
    }

    /**
     * Store the temporary image to place record.
     * Move the new image ones from temporary folder to the image folder.
     *
     * @param string $filename
     * @param array $options
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    public function updatePitchImage($filename, $options)
    {
        $keys  = array(
            'id'      => ArrayHelper::getValue($options, 'project_id', 0, 'int'),
            'user_id' => ArrayHelper::getValue($options, 'user_id', 0, 'int')
        );

        $sourceFolder = ArrayHelper::getValue($options, 'source_folder');
        $mediaFolder  = ArrayHelper::getValue($options, 'media_folder');

        $project = new Crowdfunding\Project(JFactory::getDbo());
        $project->load($keys);

        // Delete old image if I upload the new one
        if ($project->getPitchImage()) {
            // Remove an image from the filesystem
            $pitchImage  = JPath::clean($mediaFolder .'/'. $project->getPitchImage(), '/');
            if (JFile::exists($pitchImage)) {
                JFile::delete($pitchImage);
            }
        }

        if ($filename !== '') {
            $resizedFile  = JPath::clean($sourceFolder .'/'. $filename, '/');
            $pitchImage   = JPath::clean($mediaFolder .'/'. $filename, '/');

            if (JFile::exists($resizedFile)) {
                JFile::move($resizedFile, $pitchImage);
            }

            // Update the name of the new image.
            $project->storePitchImage($filename);
        }
    }
}
