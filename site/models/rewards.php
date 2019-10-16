<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Crowdfunding\Container\MoneyHelper;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelRewards extends JModelLegacy
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

    public function getItems($projectId)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.id, a.amount, a.title, a.description, a.number, a.distributed, a.delivery, a.image_thumb')
            ->from($db->quoteName('#__crowdf_rewards', 'a'))
            ->where('a.project_id = ' . (int)$projectId)
            ->where('a.published = ' . (int)Prism\Constants::PUBLISHED)
            ->order('a.ordering ASC');

        $db->setQuery($query);

        return $db->loadAssocList();
    }

    public function validate($data)
    {
        if (!is_array($data) || count($data) === 0) {
            throw new InvalidArgumentException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARDS'));
        }

        $filter = JFilterInput::getInstance();

        $params = JComponentHelper::getParams('com_crowdfunding');
        /** @var  $params Joomla\Registry\Registry */

        $container    = Prism\Container::getContainer();
        $moneyParser  = MoneyHelper::getMoneyParser($container, $params);

        foreach ($data as $key => &$item) {
            $item['amount'] = $moneyParser->parse($item['amount']);

            // Filter data
            if (!is_numeric($item['amount'])) {
                $item['amount'] = 0.00;
            }

            $item['title'] = $filter->clean($item['title'], 'string');
            $item['title'] = StringHelper::trim($item['title']);
            $item['title'] = StringHelper::substr($item['title'], 0, 128);

            $item['description'] = $filter->clean($item['description'], 'string');
            $item['description'] = StringHelper::trim($item['description']);
            $item['description'] = StringHelper::substr($item['description'], 0, 500);

            $item['number'] = (int)$item['number'];

            $item['delivery'] = trim($item['delivery']);
            $item['delivery'] = $filter->clean($item['delivery'], 'string');

            if (!empty($item['delivery'])) {
                $item['delivery'] = CrowdfundingHelper::convertToSql($item['delivery']);
                $validatorDate    = new Prism\Validator\Date($item['delivery']);

                if (!$validatorDate->isValid()) {
                    $item['delivery'] = '';
                }
            }

            if (!$item['title']) {
                throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_TITLE'));
            }

            if (!$item['description']) {
                throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_DESCRIPTION'));
            }

            if (!$item['amount']) {
                throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_AMOUNT'));
            }
        }

        unset($item);

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param array $data
     * @param int $projectId
     *
     * @return    mixed        The record id on success, null on failure.
     *
     * @throws Exception
     *
     * @since    1.6
     */
    public function save($data, $projectId)
    {
        $ids = array();

        $ordering = 1;

        JPluginHelper::importPlugin('content');

        $dispatcher = JEventDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRewardsSave', array('com_crowdfunding.project.wizard.rewards', &$data));

        foreach ($data as $item) {
            // Load a record from the database
            $row    = $this->getTable();
            $itemId = ArrayHelper::getValue($item, 'id', 0, 'int');

            if ($itemId > 0) {
                $keys = array('id' => $itemId, 'project_id' => $projectId);
                $row->load($keys);

                if (!$row->get('id')) {
                    throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARD'));
                }
            }

            $amount      = ArrayHelper::getValue($item, 'amount');
            $title       = ArrayHelper::getValue($item, 'title');
            $description = ArrayHelper::getValue($item, 'description');
            $number      = ArrayHelper::getValue($item, 'number');
            $delivery    = ArrayHelper::getValue($item, 'delivery');

            $row->set('amount', $amount);
            $row->set('title', $title);
            $row->set('description', $description);
            $row->set('number', $number);
            $row->set('delivery', $delivery);
            $row->set('project_id', $projectId);
            $row->set('ordering', $ordering);

            $row->store();

            $ids[] = $row->get('id');

            // Increase the number of ordering.
            $ordering++;
        }

        return $ids;
    }

    public function remove($rewardId, $imagesFolder)
    {
        // Get reward row.
        /** @var $table CrowdfundingTableReward */
        $table = $this->getTable();
        $table->load($rewardId);

        if (!$table->get('id')) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARD'));
        }

        // Delete the images from filesystem.
        $this->deleteImages($table, $imagesFolder);

        $table->delete();
    }

    /**
     * Upload images.
     *
     * @param  array $files
     * @param  array $rewardsIds
     * @param  array $options
     * @param  Joomla\Registry\Registry $params
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \LogicException
     *
     * @return array
     */
    public function uploadImages(array $files, array $rewardsIds, array $options, $params)
    {
        // Joomla! Media parameters
        $mediaParams = JComponentHelper::getParams('com_media');
        /** @var  $mediaParams Joomla\Registry\Registry */

        $KB = pow(1024, 2);

        $uploadMaxSize   = $mediaParams->get('upload_maxsize') * $KB;
        $mimeTypes       = explode(',', $mediaParams->get('upload_mime'));
        $mimeTypes       = array_map('trim', $mimeTypes);
        $imageExtensions = explode(',', $mediaParams->get('image_extensions'));
        $imageExtensions = array_map('trim', $imageExtensions);

        $images          = array();
        $rewardsIds      = ArrayHelper::toInteger($rewardsIds);

        $temporaryFolder   = JPath::clean($options['temporary_path'], '/');
        $destinationFolder = JPath::clean($options['destination_path'], '/');
        
        $resizeOptions = new Joomla\Registry\Registry;
        $resizeOptions->set('filename_length', 12);
        
        foreach ($files as $rewardId => $uploadedFileData) {
            // If the image is set to not valid reward, continue to next one.
            // It is impossible to store image to missed reward.
            if (!in_array((int)$rewardId, $rewardsIds, true)) {
                continue;
            }

            $uploadedFile = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
            $uploadedName = StringHelper::trim(ArrayHelper::getValue($uploadedFileData, 'name', '', 'string'));
            $errorCode    = ArrayHelper::getValue($uploadedFileData, 'error');
            
            $result       = array('image' => '', 'thumb' => '', 'square' => '');
            
            if ($uploadedName !== '') {
                // Prepare file size validator.
                $fileSize      = ArrayHelper::getValue($uploadedFileData, 'size', 0, 'int');
                $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

                // Prepare server validator.
                $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

                // Prepare image validator.
                $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

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
                    continue;
                }

                // Upload the file in temporary folder.
                $filesystemLocal = new Prism\Filesystem\Adapter\Local($temporaryFolder);
                $sourceFile      = $filesystemLocal->upload($uploadedFileData);

                if ($sourceFile !== '') {
                    // Copy original image.
                    $result['image'] = 'reward_'.basename($sourceFile);
                    $destinationFile = JPath::clean($destinationFolder .'/'. $result['image'], '/');
                    JFile::copy($sourceFile, $destinationFile);

                    // Create thumbnail.
                    $resizeOptions->set('width', $params->get('rewards_image_thumb_width', 200));
                    $resizeOptions->set('height', $params->get('rewards_image_thumb_height', 200));
                    $resizeOptions->set('scale', $params->get('rewards_image_resizing_scale', JImage::SCALE_INSIDE));
                    $resizeOptions->set('quality', $params->get('image_quality', Prism\Constants::QUALITY_VERY_HIGH));
                    $resizeOptions->set('prefix', 'reward_thumb_');
                    
                    $image    = new Prism\File\Image($sourceFile);
                    $fileData = $image->resize($destinationFolder, $resizeOptions);
                    $result['thumb'] = $fileData['filename'];

                    // Create square image.
                    $resizeOptions->set('width', $params->get('rewards_image_square_width', 50));
                    $resizeOptions->set('height', $params->get('rewards_image_square_height', 50));
                    $resizeOptions->set('prefix', 'reward_square_');
                    $fileData = $image->resize($destinationFolder, $resizeOptions);
                    $result['square'] = $fileData['filename'];

                    // Remove the original file from temporary folder.
                    if (JFile::exists($sourceFile)) {
                        JFile::delete($sourceFile);
                    }

                    $images[$rewardId] = $result;
                }
            }
        }

        return $images;
    }

    /**
     * Save reward images to the reward.
     *
     * @param array $images
     * @param string $imagesFolder
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    public function storeImages($images, $imagesFolder)
    {
        if (!$images or !is_array($images)) {
            throw new InvalidArgumentException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_IMAGES'));
        }

        foreach ($images as $rewardId => $pictures) {
            /** @var $table CrowdfundingTableReward */
            $table = $this->getTable();
            $table->load($rewardId);

            if (!$table->get('id')) {
                continue;
            }

            // Delete old reward image ( image, thumb and square ) from the filesystem.
            $this->deleteImages($table, $imagesFolder);

            // Store the new one.
            $image  = ArrayHelper::getValue($pictures, 'image');
            $thumb  = ArrayHelper::getValue($pictures, 'thumb');
            $square = ArrayHelper::getValue($pictures, 'square');

            $table->set('image', $image);
            $table->set('image_thumb', $thumb);
            $table->set('image_square', $square);

            $table->store();
        }
    }

    public function removeImage($rewardId, $imagesFolder)
    {
        // Get reward row.
        /** @var $table CrowdfundingTableReward */
        $table = $this->getTable();
        $table->load($rewardId);

        if (!$table->get('id')) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_REWARD'));
        }

        // Delete the images from filesystem.
        $this->deleteImages($table, $imagesFolder);

        $table->set('image', null);
        $table->set('image_thumb', null);
        $table->set('image_square', null);

        $table->store(true);
    }

    /**
     * Remove images from the filesystem.
     *
     * @param CrowdfundingTableReward $table
     * @param string $imagesFolder
     */
    protected function deleteImages(&$table, $imagesFolder)
    {
        // Remove image.
        if ($table->get('image')) {
            $fileSource = $imagesFolder .'/'. $table->get('image');
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }

        // Remove thumbnail.
        if ($table->get('image_thumb')) {
            $fileSource = $imagesFolder .'/'. $table->get('image_thumb');
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }

        // Remove square image.
        if ($table->get('image_square')) {
            $fileSource = $imagesFolder .'/'. $table->get('image_square');
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }
    }
}
