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

class CrowdfundingModelUser extends JModelItem
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'User', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getItem($id)
    {
        $app = JFactory::getApplication();
        $secret = $app->get('secret');

        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.name, a.registerDate, ' .
                'DES_DECRYPT(b.passport_id, '.$db->quote($secret).') AS passport_id'
            )
        );
        $query->from($db->quoteName('#__users', 'a'));
        $query->leftJoin($db->quoteName('#__crowdf_users', 'b') . ' ON a.id = b.user_id');
        $query->where('a.id = '. (int)$id);

        $db->setQuery($query);

        return $db->loadObject();
    }
}
