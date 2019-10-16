<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelReward extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableReward|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Reward', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|bool   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.reward', 'reward', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     * @throws \Exception
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var  $app JApplicationAdministrator */

        // Check the session for previously entered form data.
        $data = $app->getUserState($this->option . '.edit.reward.data', array());
        if (empty($data)) {
            /** @var stdClass $data */
            $data = $this->getItem();

            // Set project ID to form data, if it is a new record.
            if (empty($data->id)) {
                $data->project_id = $app->getUserState('com_crowdfunding.rewards.pid');
            }
        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data   The data about item
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     *
     * @return   int  Item ID
     */
    public function save($data)
    {
        $id          = ArrayHelper::getValue($data, 'id');
        $title       = ArrayHelper::getValue($data, 'title');
        $description = ArrayHelper::getValue($data, 'description');
        $amount      = ArrayHelper::getValue($data, 'amount');
        $number      = ArrayHelper::getValue($data, 'number');
        $distributed = ArrayHelper::getValue($data, 'distributed');
        $delivery    = ArrayHelper::getValue($data, 'delivery');
        $shipping    = ArrayHelper::getValue($data, 'shipping');
        $published   = ArrayHelper::getValue($data, 'published');
        $projectId   = ArrayHelper::getValue($data, 'project_id');

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set('title', $title);
        $row->set('description', $description);
        $row->set('amount', $amount);
        $row->set('number', $number);
        $row->set('distributed', $distributed);
        $row->set('delivery', $delivery);
        $row->set('shipping', $shipping);
        $row->set('published', $published);
        $row->set('project_id', $projectId);

        $this->prepareTable($row);

        $row->store();

        return $row->get('id');
    }

    /**
     * Prepare project images before saving.
     *
     * @param   JTable $table
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        // Set order value
        if (!$table->get('id') && !$table->get('ordering')) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('MAX(a.ordering)')
                ->from($db->quoteName('#__crowdf_rewards', 'a'))
                ->where('a.project_id = '. (int)$table->get('project_id'));

            $db->setQuery($query, 0, 1);

            $max = $db->loadResult();

            $table->set('ordering', $max + 1);
        }
    }

    /**
     * Upload an image.
     *
     * @param  array  $uploadedFileData
     * @param  string $destinationFolder
     *
     * @throws \Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws LogicException
     *
     * @return array
     */
    public function uploadImage($uploadedFileData, $destinationFolder)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $params = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams('com_media');
        /** @var  $mediaParams Joomla\Registry\Registry */

        $names = array();
        
        $uploadedFile = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName = StringHelper::trim(ArrayHelper::getValue($uploadedFileData, 'name'));
        $errorCode    = ArrayHelper::getValue($uploadedFileData, 'error');

        if (StringHelper::strlen($uploadedName) > 0) {
            $KB = pow(1024, 2);
            $uploadMaxSize   = $mediaParams->get('upload_maxsize') * $KB;
            $mimeTypes       = explode(',', $mediaParams->get('upload_mime'));
            $imageExtensions = explode(',', $mediaParams->get('image_extensions'));
            
            // Prepare file size validator.
            $fileSize        = ArrayHelper::getValue($uploadedFileData, 'size', 0, 'int');
            $sizeValidator   = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

            // Prepare server validator.
            $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

            // Prepare image validator.
            $imageValidator  = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

            // Get allowed mime types from media manager options
            $imageValidator->setMimeTypes($mimeTypes);

            // Get allowed image extensions from media manager options
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
            $temporaryFolder = JPath::clean($app->get('tmp_path'), '/');
            $filesystemLocal = new Prism\Filesystem\Adapter\Local($temporaryFolder);
            $sourceFile      = $filesystemLocal->upload($uploadedFileData);

            if (!JFile::exists($sourceFile)) {
                throw new RuntimeException('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
            }

            $names['image']  = 'reward_' . basename($sourceFile);
            $destinationFile = JPath::clean($destinationFolder .'/'. $names['image'], '/');
            JFile::copy($sourceFile, $destinationFile);

            // Resize image
            $options = new Joomla\Registry\Registry();
            $options->set('filename_length', 12);
            $options->set('scale', $params->get('image_resizing_scale', \JImage::SCALE_INSIDE));
            $options->set('quality', $params->get('image_quality', Prism\Constants::QUALITY_HIGH));
            $options->set('width', $params->get('rewards_image_thumb_width', 200));
            $options->set('height', $params->get('rewards_image_thumb_height', 200));
            $options->set('prefix', 'reward_thumb_');

            $image          = new Prism\File\Image($sourceFile);
            $result         = $image->resize($destinationFolder, $options);
            $names['thumb'] = $result['filename'];

            // Create square image.
            $options->set('width', $params->get('rewards_image_square_width', 50));
            $options->set('height', $params->get('rewards_image_square_height', 50));
            $options->set('prefix', 'reward_square_');

            $result          = $image->resize($destinationFolder, $options);
            $names['square'] = $result['filename'];
        }

        return $names;
    }

    /**
     * Save reward images to the reward.
     *
     * @param array $images
     * @param string $imagesFolder
     * @param int $rewardId
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function storeImage($images, $imagesFolder, $rewardId)
    {
        if (!$images || !is_array($images)) {
            throw new InvalidArgumentException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_IMAGES'));
        }

        // Get reward row.
        $table = $this->getTable();
        $table->load($rewardId);
        /** @var $table CrowdfundingTableReward */

        if (!$table->get('id')) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARD'));
        }

        // Delete old reward image ( image, thumb and square ) from the filesystem.
        $this->deleteImages($table, $imagesFolder);

        // Store the new one.
        $image  = ArrayHelper::getValue($images, 'image');
        $thumb  = ArrayHelper::getValue($images, 'thumb');
        $square = ArrayHelper::getValue($images, 'square');

        $table->set('image', $image);
        $table->set('image_thumb', $thumb);
        $table->set('image_square', $square);

        $table->store();
    }

    public function removeImage($rewardId, $imagesFolder)
    {
        // Get reward row.
        $table = $this->getTable();
        $table->load($rewardId);
        /** @var $table CrowdfundingTableReward */

        if (!$table->get('id')) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARD'));
        }

        // Delete the images from filesystem.
        $this->deleteImages($table, $imagesFolder);

        $table->set('image');
        $table->set('image_thumb');
        $table->set('image_square');

        $table->store(true);
    }

    /**
     * Remove images from the filesystem.
     *
     * @param CrowdfundingTableReward $table
     * @param string $imagesFolder
     *
     * @throws \UnexpectedValueException
     */
    protected function deleteImages(&$table, $imagesFolder)
    {
        // Remove image.
        if ($table->get('image')) {
            $fileSource = JPath::clean($imagesFolder .'/'. $table->get('image'), '/');
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }

        // Remove thumbnail.
        if ($table->get('image_thumb')) {
            $fileSource = JPath::clean($imagesFolder .'/'. $table->get('image_thumb'), '/');
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }

        // Remove square image.
        if ($table->get('image_square')) {
            $fileSource = JPath::clean($imagesFolder .'/'. $table->get('image_square'), '/');
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }
    }

    public function updateRewardState($transactionId, $state)
    {
        $state  = (!$state) ? Prism\Constants::UNPUBLISHED : Prism\Constants::PUBLISHED;

        $db     = $this->getDbo();
        $query  = $db->getQuery(true);

        $query
            ->update($db->quoteName('#__crowdf_transactions'))
            ->set($db->quoteName('reward_state') . ' = ' . (int)$state)
            ->where($db->quoteName('id') . ' = ' . (int)$transactionId);

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param    stdClass $table A record object.
     *
     * @return   array    An array of conditions to add to add to ordering queries.
     * @since    1.6
     */
    protected function getReorderConditions($table)
    {
        $condition   = array();
        $condition[] = 'project_id = ' . (int)$table->project_id;

        return $condition;
    }
}
