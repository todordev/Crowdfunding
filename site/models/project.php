<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelProject extends JModelForm
{
    protected $formName = '';

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
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array   $data     An optional array of data for the form to interrogate.
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return   JForm|bool    A JForm object on success, false on failure
     * @since    1.6
     *
     * @throws \RuntimeException
     */
    public function getForm($data = array(), $loadData = true)
    {
        if (!$this->formName) {
            throw new RuntimeException('There is now form name.');
        }

        $form = $this->loadForm($this->option . '.'.$this->formName, $this->formName, array('control' => 'jform', 'load_data' => $loadData));

        return $form ?: false;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @throws \Exception
     */
    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get the pk of the record from the request.
        $itemId = $app->input->getUint('id');
        $this->setState($this->getName() . '.id', $itemId);

        // Load the parameters.
        $value = $app->getParams($this->option);
        $this->setState('params', $value);
    }

    /**
     * Method to get a single record.
     *
     * @param   integer $pk     The id of the primary key.
     * @param   integer $userId The id of the user.
     *
     * @return  stdClass  Object on success, false on failure.
     *
     * @throws  \Exception
     *
     * @since   11.1
     */
    public function getItem($pk, $userId)
    {
        // Initialise variables.
        $table = $this->getTable();

        if ($pk > 0 && $userId > 0) {
            $keys = array(
                'id'      => $pk,
                'user_id' => $userId
            );

            // Attempt to load the row.
            $return = $table->load($keys);

            // Check for a table object error.
            if ($return === false) {
                throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'));
            }
        }

        // Convert to the JObject before adding other data.
        $properties = $table->getProperties();
        $item       = ArrayHelper::toObject($properties);

        if (property_exists($item, 'params')) {
            $registry = new Registry;
            /** @var  $registry Registry */

            $registry->loadString($item->params);
            $item->params = $registry;
        }

        return $item;
    }

    /**
     * This method executes the event onContentAfterSave.
     *
     * @param CrowdfundingTableProject $table
     * @param string                   $step
     * @param bool                     $isNew
     *
     * @throws Exception
     */
    protected function triggerEventAfterSave($table, $step, $isNew = false)
    {
        // Get properties
        $project = $table->getProperties();
        $project = ArrayHelper::toObject($project);

        // Generate context
        $context = $this->option . '.' . $step;

        // Include the content plugins for the change of state event.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        // Trigger the onContentAfterSave event.
        $results = $dispatcher->trigger('onContentAfterSave', array($context, &$project, $isNew));

        if (in_array(false, $results, true)) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_DURING_PROJECT_CREATING_PROCESS'));
        }
    }

    /**
     * Remove the temporary images if a user upload or crop a picture,
     * but he does not store it or reload the page.
     *
     * @param JApplicationSite $app
     *
     * @throws \Exception
     */
    public function removeTemporaryImage($app)
    {
        // Remove old image if it exists.
        $oldImage = (string)$app->getUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT);
        if ($oldImage !== '') {
            $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT);
            $oldImage = JPath::clean($temporaryFolder .'/'. basename($oldImage), '/');
            if (JFile::exists($oldImage)) {
                JFile::delete($oldImage);
            }
        }

        // Set the name of the image in the session.
        $app->setUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT, null);
    }

    /**
     * Remove the temporary images if a user upload or crop a picture,
     * but he does not store it or reload the page.
     *
     * @param JApplicationSite $app
     *
     * @throws \Exception
     */
    public function removeCroppedImages($app)
    {
        // Remove the temporary cropped images if they exist.
        $temporaryImages = $app->getUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT);
        /** @var array $temporaryImages */

        if (is_array($temporaryImages) && count($temporaryImages) > 0) {
            $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder(JPATH_ROOT);

            foreach ($temporaryImages as $filename) {
                $filepath = $temporaryFolder . '/' . basename($filename);

                if (JFile::exists($filepath)) {
                    JFile::delete($filepath);
                }
            }
        }

        // Reset the temporary images.
        $app->setUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT, null);
    }
}
