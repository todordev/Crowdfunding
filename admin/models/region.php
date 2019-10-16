<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class CrowdfundingModelRegion extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableCountry|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Region', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|bool   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.region', 'region', array('control' => 'jform', 'load_data' => $loadData));
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
        $app  = JFactory::getApplication();

        // Check the session for previously entered form data.
        $data = $app->getUserState($this->option . '.edit.region.data', array());

        if (!$data) {
            /** @var stdClass $data */
            $data = $this->getItem();

            if (is_object($data) && !$data->id) {
                $data->country_code = $app->input->getCmd('code');
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
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     *
     * @return  int   Item ID
     */
    public function save($data)
    {
        $id        = Joomla\Utilities\ArrayHelper::getValue($data, 'id');
        $name      = Joomla\Utilities\ArrayHelper::getValue($data, 'name');
        $code      = Joomla\Utilities\ArrayHelper::getValue($data, 'country_code');
        $adminId   = Joomla\Utilities\ArrayHelper::getValue($data, 'admincode_id');

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set('name', $name);
        $row->set('country_code', $code);
        $row->set('admincode_id', $adminId);

        $row->store();

        return $row->get('id');
    }
}
