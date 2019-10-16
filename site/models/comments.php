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
 * Get a list of items
 */
class CrowdfundingModelComments extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'record_date', 'a.record_date'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get project ID
        $value = $app->input->get('id', 0, 'uint');
        $this->setState($this->getName() . '.id', $value);

        // Load the component parameters.
        $params = $app->getParams($this->option);
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.record_date', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string $id A prefix for the store id.
     *
     * @return  string      A store id.
     * @since   1.6
     * @throws \Exception
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState($this->getName() . '.id');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     * @throws \Exception
     * @throws \RuntimeException
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */
        
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.comment, a.record_date, a.user_id, a.published, ' .
                'b.name AS author'
            )
        );
        $query->from($db->quoteName('#__crowdf_comments', 'a'));
        $query->innerJoin($db->quoteName('#__users', 'b') . ' ON a.user_id = b.id');

        // Project filter
        $projectId = $this->getState($this->getName() . '.id');
        $query->where('a.project_id = ' . (int)$projectId);

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState('list.ordering');
        $orderDirn = $this->getState('list.direction');

        return $orderCol . ' ' . $orderDirn;
    }

    public function getForm()
    {
        $name   = $this->option . '.comment';
        $source = 'comment';

        $options = array(
            'control'   => 'jform',
            'load_data' => false
        );

        $data = array('project_id' => $this->state->get($this->getName() . '.id'));

        // Get the form.
        JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
        JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

        $form = JForm::getInstance($name, $source, $options, false);

        // Load the data into the form after the plugins have operated.
        $form->bind($data);

        return $form;
    }
}
