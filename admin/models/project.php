<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelProject extends JModelAdmin
{
    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = 'com_crowdfunding.project';

    /**
     * The context used for the associations table
     *
     * @var      string
     * @since    3.4.4
     */
    protected $associationsContext = 'com_crowdfunding.project';
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableProject|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdfundingTable', $config = array())
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
        $form = $this->loadForm($this->option . '.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @throws \Exception
     */
    protected function loadFormData()
    {
        $app  = JFactory::getApplication();
        
        // Check the session for previously entered form data.
        $data = $app->getUserState($this->option . '.edit.project.data', array());

        if (!$data) {
            $data = $this->getItem();

            // Load location from database.
            if ((int)$data->location_id > 0) {
                $location = new Crowdfunding\Location(JFactory::getDbo());
                $location->load($data->location_id);
                $locationName = $location->getName(true);

                // Set the name to the form element.
                if ($locationName !== '') {
                    $data->location_preview = $locationName;
                }
            }

            $projectId = (int)$this->getState('project.id');
            if ($projectId === 0) {
                $filters = (array)$app->getUserState('com_crowdfunding.projects.filter');

                $state           = (array_key_exists('state', $filters) and $filters['state'] !== '') ? $filters['state'] : null;
                $data->published = $app->input->getInt('published', $state);

                $data->catid     = $app->input->getInt('catid', (!empty($filters['category']) ? $filters['category'] : null));
                $data->access    = $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')));
                $data->type_id   = $app->input->getInt('type', (!empty($filters['type']) ? $filters['type'] : null));
                $data->approved  = $app->input->getInt('approved', (!empty($filters['approved']) ? $filters['approved'] : null));
            }
        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data The data about item
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return  int    Item ID
     */
    public function save($data)
    {
        $id          = ArrayHelper::getValue($data, 'id', 0, 'int');
        $title       = ArrayHelper::getValue($data, 'title');
        $alias       = ArrayHelper::getValue($data, 'alias');
        $catId       = ArrayHelper::getValue($data, 'catid', 0, 'int');
        $typeId      = ArrayHelper::getValue($data, 'type_id', 0, 'int');
        $userId      = ArrayHelper::getValue($data, 'user_id', 0, 'int');
        $locationId  = ArrayHelper::getValue($data, 'location_id');
        $published   = ArrayHelper::getValue($data, 'published', 0, 'int');
        $approved    = ArrayHelper::getValue($data, 'approved', 0, 'int');
        $shortDesc   = ArrayHelper::getValue($data, 'short_desc');
        $created     = ArrayHelper::getValue($data, 'created');
        $params      = ArrayHelper::getValue($data, 'params');

        $goal        = ArrayHelper::getValue($data, 'goal');
        $funded      = ArrayHelper::getValue($data, 'funded');
        $fundingType = ArrayHelper::getValue($data, 'funding_type');

        $pitchVideo  = ArrayHelper::getValue($data, 'pitch_video');
        $description = ArrayHelper::getValue($data, 'description');

        $app           = JFactory::getApplication();
        $filters       = (array)$app->getUserState($this->option.'.items.filter');
        $defaultAccess = (!empty($filters['access']) ? $filters['access'] : $app->get('access'));
        $access        = Joomla\Utilities\ArrayHelper::getValue($data, 'access', $defaultAccess, 'int');

        // Encode parameters to JSON format.
        $params      = ($params !== null and is_array($params)) ? json_encode($params) : null;

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        if (!$row->get('id')) {
            $row->set('user_id', $userId);
        }

        $row->set('title', $title);
        $row->set('alias', $alias);
        $row->set('catid', $catId);
        $row->set('type_id', $typeId);
        $row->set('location_id', $locationId);
        $row->set('published', $published);
        $row->set('approved', $approved);
        $row->set('short_desc', $shortDesc);
        $row->set('created', $created);
        $row->set('params', $params);
        $row->set('access', $access);

        $row->set('goal', $goal);
        $row->set('funded', $funded);
        $row->set('funding_type', $fundingType);

        $row->set('pitch_video', $pitchVideo);
        $row->set('description', $description);

        $this->prepareTableData($row, $data);

        $row->store();

        // Clear the component's cache
        $this->cleanCache();

        return $row->get('id');
    }

    /**
     * Prepare project images before saving.
     *
     * @param   CrowdfundingTableProject $table
     * @param   array  $data
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function prepareTableData($table, $data)
    {
        $params    = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        // Set order value
        if (!$table->get('id') and !$table->get('ordering')) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('MAX(ordering)')
                ->from($db->quoteName('#__crowdf_projects'));

            $db->setQuery($query, 0, 1);
            $max = $db->loadResult();

            $table->set('ordering', $max + 1);
        }

        // Prepare image.
        if (!empty($data['image'])) {
            // Delete old image if I upload a new one
            if ($table->get('image')) {
                $imagesFolder = $params->get('images_directory', 'images/crowdfunding');

                // Remove an image from the filesystem
                $fileImage  = JPath::clean(JPATH_ROOT .'/'. $imagesFolder .'/'. $table->get('image'), '/');
                $fileSmall  = JPath::clean(JPATH_ROOT .'/'. $imagesFolder .'/'. $table->get('image_small'), '/');
                $fileSquare = JPath::clean(JPATH_ROOT .'/'. $imagesFolder .'/'. $table->get('image_square'), '/');

                if (is_file($fileImage)) {
                    JFile::delete($fileImage);
                }

                if (is_file($fileSmall)) {
                    JFile::delete($fileSmall);
                }

                if (is_file($fileSquare)) {
                    JFile::delete($fileSquare);
                }
            }

            $table->set('image', $data['image']);
            $table->set('image_small', $data['image_small']);
            $table->set('image_square', $data['image_square']);
        }


        // Prepare pitch image.
        if (!empty($data['pitch_image'])) {
            // Delete old image if I upload a new one
            if ($table->get('pitch_image')) {
                $imagesFolder = $params->get('images_directory', 'images/crowdfunding');

                // Remove an image from the filesystem
                $pitchImage = JPath::clean(JPATH_ROOT .'/'. $imagesFolder .'/'. $table->get('pitch_image'), '/');

                if (is_file($pitchImage)) {
                    JFile::delete($pitchImage);
                }
            }

            $table->set('pitch_image', $data['pitch_image']);
        }

        // If an alias does not exist, I will generate the new one using the title.
        if (!$table->get('alias')) {
            $table->set('alias', $table->get('title'));
        }
        $alias = Prism\Utilities\StringHelper::stringUrlSafe($table->get('alias'));
        $table->set('alias', $alias);

        // Prepare funding duration
        $durationType = ArrayHelper::getValue($data, 'duration_type');
        $fundingStart = ArrayHelper::getValue($data, 'funding_start');
        $fundingEnd   = ArrayHelper::getValue($data, 'funding_end');
        $fundingDays  = ArrayHelper::getValue($data, 'funding_days');

        // Prepare funding start date.
        $fundingStartValidator = new Prism\Validator\Date($fundingStart);
        if (!$fundingStartValidator->isValid()) {
            $table->funding_start = Prism\Constants::DATE_DEFAULT_SQL_DATE;
        } else {
            $date                 = new JDate($fundingStart);
            $table->funding_start = $date->toSql();
        }

        switch ($durationType) {
            case 'days':
                // Set funding day.
                $table->funding_days    = $fundingDays;

                // Calculate end date
                $fundingStartValidator  = new Prism\Validator\Date($table->funding_start);
                if (!$fundingStartValidator->isValid()) {
                    $table->funding_end = Prism\Constants::DATE_DEFAULT_SQL_DATE;
                } else {
                    $fundingStartDate   = new Crowdfunding\Date($table->funding_start);
                    $fundingEndDate     = $fundingStartDate->calculateEndDate($table->funding_days);
                    $table->funding_end = $fundingEndDate->format(Prism\Constants::DATE_FORMAT_SQL_DATETIME);
                }
                break;

            case 'date':
                $fundingEndValidator = new Prism\Validator\Date($fundingEnd);
                if (!$fundingEndValidator->isValid()) {
                    throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_DATE'));
                }

                $date = new JDate($fundingEnd);

                $table->funding_days = 0;
                $table->funding_end  = $date->toSql();
                break;

            default:
                $table->funding_days = 0;
                $table->funding_end  = Prism\Constants::DATE_DEFAULT_SQL_DATE;
                break;
        }
    }

    /**
     * Method to change the approved state of one or more records.
     *
     * @param   array   $pks   A list of the primary keys to change.
     * @param   integer $value The value of the approved state.
     *
     * @throws Exception
     */
    public function approve(array $pks, $value)
    {
        if (count($pks) > 0) {
            $db = JFactory::getDbo();

            $query = $db->getQuery(true);
            $query
                ->update($db->quoteName('#__crowdf_projects'))
                ->set($db->quoteName('approved') . ' = ' . (int)$value)
                ->where($db->quoteName('id') . ' IN (' . implode(',', $pks) . ')');

            $db->setQuery($query);
            $db->execute();

            // Trigger change state event

            $context = $this->option . '.' . $this->name;

            // Include the content plugins for the change of state event.
            JPluginHelper::importPlugin('content');

            // Trigger the onContentChangeState event.
            $dispatcher = JEventDispatcher::getInstance();
            $result     = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

            if (in_array(false, $result, true)) {
                throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_TRIGGERING_PLUGIN'));
            }

            // Clear the component's cache
            $this->cleanCache();
        }
    }

    /**
     * Method to toggle the featured setting of articles.
     *
     * @param   array   $pks   The ids of the items to toggle.
     * @param   integer $value The value to toggle to.
     *
     * @return  boolean  True on success.
     * @throws \RuntimeException
     */
    public function featured(array $pks, $value = 0)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true);
        $query
            ->update($db->quoteName('#__crowdf_projects'))
            ->set($db->quoteName('featured') .' = ' . (int)$value)
            ->where($db->quoteName('id'). ' IN (' . implode(',', $pks) . ')');

        $db->setQuery($query);
        $db->execute();

        // Clear the component's cache
        $this->cleanCache();
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array   &$pks  A list of the primary keys to change.
     * @param   integer $value The value of the published state.
     *
     * @throws Exception
     *
     * @return  boolean  True on success.
     *
     * @since   12.2
     */
    public function publish(&$pks, $value = 0)
    {
        $table = $this->getTable();
        /** @var $table CrowdfundingTableProject */

        $pks   = (array)$pks;

        // Access checks.
        foreach ($pks as $pk) {
            $table->reset();

            if ($table->load($pk)) {
                // Publish a project and calculate start date, end date and duration.
                if ((int)$value === Prism\Constants::PUBLISHED) {
                    // Validate funding period
                    $endDateValidator = new Prism\Validator\Date($table->get('funding_end'));
                    if (!$table->get('funding_days') and !$endDateValidator->isValid()) {
                        throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_DURATION_PERIOD'));
                    }

                    // Calculate starting date if the user publishes a project for first time.
                    $startDateValidator = new Prism\Validator\Date($table->get('funding_start'));
                    if (!$startDateValidator->isValid()) {
                        $app = JFactory::getApplication();
                        /** @var $app JApplicationSite */

                        $fundingStart           = new JDate('now', $app->get('offset'));
                        $table->funding_start   = $fundingStart->toSql();

                        // If funding type is 'days', calculate end date.
                        if ((int)$table->get('funding_days') > 0) {
                            $fundingStartDate   = new Crowdfunding\Date($table->get('funding_start'));
                            $fundingEndDate     = $fundingStartDate->calculateEndDate($table->get('funding_days'));
                            $table->funding_end = $fundingEndDate->format(Prism\Constants::DATE_FORMAT_SQL_DATETIME);
                        }
                    }

                    // Validate the period if the funding type is days
                    $params = JComponentHelper::getParams('com_crowdfunding');
                    /** @var  $params Joomla\Registry\Registry */

                    $minDays = (int)$params->get('project_days_minimum', 15);
                    $maxDays = (int)$params->get('project_days_maximum');

                    $startDateValidator = new Prism\Validator\Date($table->get('funding_start'));
                    if ($startDateValidator->isValid()) {
                        $periodValidator = new Crowdfunding\Validator\Project\Period($table->get('funding_start'), $table->get('funding_end'), $minDays, $maxDays);
                        if (!$periodValidator->isValid()) {
                            if ($maxDays > 0) {
                                throw new RuntimeException(JText::sprintf('COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS', $minDays, $maxDays));
                            } else {
                                throw new RuntimeException(JText::sprintf('COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_DAYS', $minDays));
                            }
                        }

                    }

                    $table->set('published', Prism\Constants::PUBLISHED);
                    $table->store();

                } else { // Set other states - unpublished, trash,...
                    $table->publish(array($pk), $value);
                }
            }
        }

        // Trigger change state event

        $context = $this->option . '.' . $this->name;

        // Include the content plugins for the change of state event.
        JPluginHelper::importPlugin('content');

        // Trigger the onContentChangeState event.
        $dispatcher = JEventDispatcher::getInstance();
        $result     = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

        if (in_array(false, $result, true)) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_CHANGE_STATE'));
        }

        // Clear the component's cache
        $this->cleanCache();
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param    CrowdfundingTableProject $table A record object.
     *
     * @return    array    An array of conditions to add to add to ordering queries.
     * @since    1.6
     */
    protected function getReorderConditions($table)
    {
        $condition   = array();
        $condition[] = 'catid = ' . (int)$table->get('catid');

        return $condition;
    }

    /**
     * Method to delete one or more records.
     *
     * @param   array &$pks An array of record primary keys.
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws  \UnexpectedValueException
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   12.2
     */
    public function delete(&$pks)
    {
        $params       = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $folderImages = $params->get('images_directory', 'images/crowdfunding');

        foreach ($pks as $id) {
            $project = new Crowdfunding\Project(JFactory::getDbo());
            $project->load($id);

            $this->deleteProjectImages($project, $folderImages);
            $this->removeIntentions($project);
            $this->removeComments($project);
            $this->removeUpdates($project);
            $this->removeRewards($project);
            $this->removeReports($project);
            $this->removeTransactions($project);
            $this->removeFollowers($project);
        }

        return parent::delete($pks);
    }

    protected function deleteProjectImages(Crowdfunding\Project $project, $folderImages)
    {
        $images = array(
            'image'        => $project->getImage(),
            'image_square' => $project->getSquareImage(),
            'image_small'  => $project->getSmallImage(),
            'pitch_image'  => $project->getPitchImage()
        );

        // Remove
        foreach ($images as $image) {
            $imageFile = JPath::clean(JPATH_ROOT .'/'. $folderImages .'/'. $image, '/');
            if (JFile::exists($imageFile)) {
                JFile::delete($imageFile);
            }
        }
    }

    protected function removeIntentions(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__crowdf_intentions'))
            ->where($db->quoteName('project_id') . '=' . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeComments(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__crowdf_comments'))
            ->where($db->quoteName('project_id') . '=' . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeUpdates(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__crowdf_updates'))
            ->where($db->quoteName('project_id') . '=' . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeRewards(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__crowdf_rewards'))
            ->where($db->quoteName('project_id') . '=' . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeReports(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__crowdf_reports'))
            ->where($db->quoteName('project_id') . '=' . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeTransactions(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__crowdf_transactions'))
            ->where($db->quoteName('project_id') . '=' . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeFollowers(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__crowdf_followers'))
            ->where($db->quoteName('project_id') . '=' . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Upload and resize the image
     *
     * @param array $uploadedFileData
     *
     * @throws Exception
     *
     * @return array
     */
    public function uploadImage($uploadedFileData)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $uploadedFile = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName = ArrayHelper::getValue($uploadedFileData, 'name');
        $errorCode    = ArrayHelper::getValue($uploadedFileData, 'error');

        // Load parameters.
        $params     = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams('com_media');
        /** @var  $mediaParams Joomla\Registry\Registry */

        // Prepare size validator.
        $KB            = pow(1024, 2);
        $fileSize      = ArrayHelper::getValue($uploadedFileData, 'size', 0, 'int');
        $uploadMaxSize = $mediaParams->get('upload_maxsize') * $KB;

        // Prepare file size validator
        $sizeValidator   = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

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
        $temporaryFolder = JPath::clean($app->get('tmp_path'), '/');
        $filesystemLocal = new Prism\Filesystem\Adapter\Local($temporaryFolder);
        $sourceFile      = $filesystemLocal->upload($uploadedFileData);

        if (!JFile::exists($sourceFile)) {
            throw new RuntimeException('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
        }

        // Resize image
        $names = array(
            'image'        => '',
            'image_small'  => '',
            'image_square' => ''
        );

        $destinationFolder = JPath::clean(JPATH_ROOT .'/'. $params->get('images_directory', 'images/crowdfunding'), '/');

        // Create main image
        $options = new Joomla\Registry\Registry();
        $options->set('filename_length', 24);
        $options->set('scale', $params->get('image_resizing_scale', \JImage::SCALE_INSIDE));
        $options->set('quality', $params->get('image_quality', Prism\Constants::QUALITY_HIGH));
        $options->set('width', $params->get('image_width', 200));
        $options->set('height', $params->get('image_height', 200));
        $options->set('suffix', '_image');

        $image   = new Prism\File\Image($sourceFile);
        $result  = $image->resize($destinationFolder, $options);
        $names['image'] = $result['filename'];

        // Create small image
        $options->set('width', $params->get('image_small_width', 100));
        $options->set('height', $params->get('image_small_height', 100));
        $options->set('suffix', '_small');

        $result  = $image->resize($destinationFolder, $options);
        $names['image_small'] = $result['filename'];

        // Create square image
        $options->set('width', $params->get('image_square_width', 50));
        $options->set('height', $params->get('image_square_height', 50));
        $options->set('suffix', '_square');

        $result  = $image->resize($destinationFolder, $options);
        $names['image_square'] = $result['filename'];

        // Remove the temporary file.
        if (JFile::exists($sourceFile)) {
            JFile::delete($sourceFile);
        }

        return $names;
    }

    /**
     * Upload a pitch image.
     *
     * @param  array $uploadedFileData
     *
     * @throws Exception
     *
     * @return string
     */
    public function uploadPitchImage($uploadedFileData)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $uploadedFile = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName = ArrayHelper::getValue($uploadedFileData, 'name');
        $errorCode    = ArrayHelper::getValue($uploadedFileData, 'error');

        // Load parameters.
        $params       = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams('com_media');
        /** @var  $mediaParams Joomla\Registry\Registry */

        // Prepare size validator.
        $KB            = pow(1024, 2);
        $fileSize      = ArrayHelper::getValue($uploadedFileData, 'size', 0, 'int');
        $uploadMaxSize = $mediaParams->get('upload_maxsize') * $KB;

        $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

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
        $temporaryFolder = JPath::clean($app->get('tmp_path'), '/');
        $filesystemLocal = new Prism\Filesystem\Adapter\Local($temporaryFolder);
        $sourceFile      = $filesystemLocal->upload($uploadedFileData);

        if (!JFile::exists($sourceFile)) {
            throw new RuntimeException('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
        }

        // Resize image
        $destinationFolder = JPath::clean(JPATH_ROOT .'/'. $params->get('images_directory', 'images/crowdfunding'), '/');

        // Resize image
        $options = new Joomla\Registry\Registry();
        $options->set('filename_length', 24);
        $options->set('scale', $params->get('image_resizing_scale', \JImage::SCALE_INSIDE));
        $options->set('quality', $params->get('image_quality', Prism\Constants::QUALITY_HIGH));
        $options->set('width', $params->get('pitch_image_width', 600));
        $options->set('height', $params->get('pitch_image_height', 400));
        $options->set('suffix', '_pimage');
        $options->set('create_new', Prism\Constants::NO);

        $image     = new Prism\File\Image($sourceFile);
        $result    = $image->resize($destinationFolder, $options);
        $imageName = $result['filename'];

        // Remove the temporary
        if (JFile::exists($sourceFile)) {
            JFile::delete($sourceFile);
        }

        return $imageName;
    }

    /**
     * Delete the images - image, image_small, image_square.
     *
     * @throws \UnexpectedValueException
     *
     * @param integer $id Item id
     */
    public function removeImage($id)
    {
        /** @var $row CrowdfundingTableProject */
        $row = $this->getTable();
        $row->load($id);

        // Delete old image if I upload the new one
        if ($row->get('image') !== null and $row->get('image') !== '') {
            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = JPath::clean(JPATH_ROOT .'/'. $params->get('images_directory', 'images/crowdfunding'), '/');

            // Remove an image from the filesystem
            $fileImage  = $imagesFolder .'/'. $row->image;
            $fileSmall  = $imagesFolder .'/'. $row->image_small;
            $fileSquare = $imagesFolder .'/'. $row->image_square;

            if (JFile::exists($fileImage)) {
                JFile::delete($fileImage);
            }

            if (JFile::exists($fileSmall)) {
                JFile::delete($fileSmall);
            }

            if (JFile::exists($fileSquare)) {
                JFile::delete($fileSquare);
            }
        }

        $row->set('image', '');
        $row->set('image_small', '');
        $row->set('image_square', '');
        $row->store();
    }

    /**
     * Delete the pitch image.
     *
     * @throws \UnexpectedValueException
     *
     * @param integer $id Item id
     */
    public function removePitchImage($id)
    {
        // Load category data
        $row = $this->getTable();
        $row->load($id);

        // Delete old image if I upload the new one
        if ($row->get('pitch_image') !== null and $row->get('pitch_image') !== '') {
            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = JPath::clean(JPATH_ROOT .'/'. $params->get('images_directory', 'images/crowdfunding'), '/');

            // Remove an image from the filesystem
            $pitchImage = $imagesFolder .'/'. $row->get('pitch_image');

            if (JFile::exists($pitchImage)) {
                JFile::delete($pitchImage);
            }
        }

        $row->set('pitch_image', '');
        $row->store();
    }
}
