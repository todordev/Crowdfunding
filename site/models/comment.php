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

class CrowdfundingModelComment extends JModelForm
{
    protected $item;

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableComment  A database object
     * @since   1.6
     */
    public function getTable($type = 'Comment', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @since    1.6
     * @throws \Exception
     */
    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get the pk of the record from the request.
        $value = $app->input->getInt('id');
        $this->setState($this->getName() . '.id', $value);

        $value = $app->input->getInt('project_id');
        $this->setState('project_id', $value);

        // Load the parameters.
        $value = $app->getParams($this->option);
        $this->setState('params', $value);
    }

    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array   $data     An optional array of data for the form to interogate.
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return    JForm|bool    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.comment', 'comment', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     * @throws \Exception
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $data = $app->getUserState($this->option . '.edit.comment.data', array());
        if (!$data) {
            $itemId = (int)$this->getState($this->getName() . '.id');
            $userId = JFactory::getUser()->id;

            $data = $this->getItem($itemId, $userId);
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer $pk     The id of the primary key.
     * @param   integer $userId The user Id
     *
     * @throws Exception
     * @return stdClass
     *
     * @since   11.1
     */
    public function getItem($pk, $userId)
    {
        if ($this->item) {
            return $this->item;
        }

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
                throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
            }
        }

        // Convert to the JObject before adding other data.
        $properties = $table->getProperties();
        $this->item = Joomla\Utilities\ArrayHelper::toObject($properties, 'JObject');

        return $this->item;
    }

    /**
     * Method to save the form data.
     *
     * @param    array $data The form data.
     *
     * @return   mixed    The record id on success, null on failure.
     * @since    1.6
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function save($data)
    {
        $id        = Joomla\Utilities\ArrayHelper::getValue($data, 'id');
        $comment   = Joomla\Utilities\ArrayHelper::getValue($data, 'comment');
        $projectId = Joomla\Utilities\ArrayHelper::getValue($data, 'project_id');

        $userId = JFactory::getUser()->get('id');

        $keys = array(
            'id'      => $id,
            'user_id' => $userId
        );

        // Load a record from the database
        $row = $this->getTable();
        $row->load($keys);

        $row->set('comment', $comment);

        if (!$row->get('user_id')) {
            $params    = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $published = (!$params->get('comments_adding_state', 0)) ? Prism\Constants::UNPUBLISHED : Prism\Constants::PUBLISHED;

            $row->set('record_date', null);
            $row->set('project_id', $projectId);
            $row->set('user_id', $userId);
            $row->set('published', $published);
        }

        $row->store();

        return $row->get('id');
    }
}
