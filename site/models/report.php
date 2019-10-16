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

class CrowdfundingModelReport extends JModelForm
{
    protected $items = array();

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableReport|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Report', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     * @throws \Exception
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load the object state.
        $id = $app->input->getInt('id');
        $this->setState($this->getName() . '.id', $id);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
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
     * @return    JForm|bool    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.report', 'report', array('control' => 'cfreport', 'load_data' => $loadData));
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

        $data = $app->getUserState($this->option . '.edit.report.data', array());
        if (!$data) {
            $itemId = (int)$this->getState($this->getName() . '.id');
            $data = $this->getItem($itemId);
        }

        return $data;
    }

    /**
     * Method to get an object.
     *
     * @param    integer $id The id of the object to get.
     *
     * @return    array  Object on success, false on failure.
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function getItem($id = 0)
    {
        $id     = $id ?: (int)$this->getState($this->getName() . '.id');

        if (!array_key_exists($id, $this->items)) {
            $this->items[$id] = null;

            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('a.id, a.title')
                ->from($db->quoteName('#__crowdf_projects', 'a'))
                ->where('a.id = ' . (int)$id)
                ->where('a.published = 1')
                ->where('a.approved  = 1');

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();

            // Attempt to load the row.
            if (!$result) {
                $result = null;
            }

            $this->items[$id] = $result;
        }

        return $this->items[$id];
    }

    /**
     * Method to save the form data.
     *
     * @param    array $data The form data.
     *
     * @return    mixed        The record id on success, null on failure.
     * @since    1.6
     *
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     */
    public function save($data)
    {
        $projectId   = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'int');
        $subject     = Joomla\Utilities\ArrayHelper::getValue($data, 'subject');
        $description = Joomla\Utilities\ArrayHelper::getValue($data, 'description');
        $email       = Joomla\Utilities\ArrayHelper::getValue($data, 'email');
        $userId      = Joomla\Utilities\ArrayHelper::getValue($data, 'user_id', 0, 'int');

        if (!$projectId) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'));
        }

        if (!$email) {
            $email = null;
        }
        if (!$description) {
            $description = null;
        }

        // Load a record from the database
        $row = $this->getTable();
        /** @var $row CrowdfundingTableReport */

        $row->set('subject', $subject);
        $row->set('description', $description);
        $row->set('email', $email);
        $row->set('user_id', $userId);
        $row->set('project_id', $projectId);

        $row->store(true);

        // Trigger the event onContentAfterSave.
        $this->triggerEventAfterReport($row);

        return $row->get('id');
    }

    /**
     * This method executes the event onContentAfterSave.
     *
     * @param CrowdfundingTableReport $table
     *
     * @throws RuntimeException
     */
    protected function triggerEventAfterReport($table)
    {
        // Get properties
        $report = $table->getProperties();
        $report = Joomla\Utilities\ArrayHelper::toObject($report);

        // Generate context
        $context = $this->option . '.report';

        // Include the content plugins for the change of state event.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        // Trigger the onContentAfterSave event.
        $results = $dispatcher->trigger('onContentAfterReport', array($context, &$report));

        if (in_array(false, $results, true)) {
            throw new RuntimeException(JText::_('COM_CROWDFUNDING_ERROR_DURING_REPORTING_PROCESS'));
        }
    }
}
