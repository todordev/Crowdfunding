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

class CrowdfundingModelTools extends JModelLegacy
{
    /**
     * Import users to AcyMailing list.
     *
     * @param $id
     *
     * @return array
     * @throws \RuntimeException
     */
    public function importFunders($id)
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseMySQLi */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.name, a.registerDate'
            )
        );
        
        $query->from($db->quoteName('#__users', 'a'));
        $query->where('a.id = ' . (int)$id);

        $db->setQuery($query);

        return $db->loadObject();
    }

    /**
     * Return crowdfunding projects.
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getProjects()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select(
                $this->getState(
                    'list.select',
                    'a.id, a.title'
                )
            )
            ->from($db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.published = '. Prism\Constants::PUBLISHED)
            ->where('a.approved  = '. Prism\Constants::APPROVED);

        $db->setQuery($query);

        return (array)$db->loadAssocList();
    }

    /**
     * Return AcyMailing lists.
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getAcyLists()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select(
                $this->getState(
                    'list.select',
                    'a.listid AS id, a.name'
                )
            )
            ->from($db->quoteName('#__acymailing_list', 'a'));

        $db->setQuery($query);

        return (array)$db->loadAssocList();
    }

    /**
     * Return AcyMailing lists.
     *
     * @return array
     * @throws \RuntimeException
     */
    public function addSubscribers()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Check for registered users, which are not subscribers.
        $query    = $db->getQuery(true);
        $subQuery = $db->getQuery(true);

        $subQuery
            ->select('b.userid')
            ->from($db->quoteName('#__acymailing_subscriber', 'b'))
            ->where('b.userid > 0');

        $query
            ->select('a.investor_id')
            ->from($db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.investor_id > 0')
            ->where('a.investor_id NOT IN ('. $subQuery . ')')
            ->group('investor_id');

        $db->setQuery($query);
        $ids = (array)$db->loadColumn();

        $results = array();
        if (count($ids) !== 0) {
            $query    = $db->getQuery(true);
            $query
                ->select('a.id, a.name, a.email')
                ->from($db->quoteName('#__users', 'a'))
                ->where('a.id IN (' . implode(',', $ids) . ')');

            $db->setQuery($query);
            $results = (array)$db->loadObjectList();
        }

        // Get data from Crowdfunding Data.
        $results2 = array();
        if (JComponentHelper::isInstalled('com_crowdfundingdata')) {
            $query    = $db->getQuery(true);
            $subQuery = $db->getQuery(true);

            $subQuery
                ->select('b.email')
                ->from($db->quoteName('#__acymailing_subscriber', 'b'))
                ->where('b.userid = 0');

            $query
                ->select('a.name, a.email')
                ->from($db->quoteName('#__cfdata_records', 'a'))
                ->where('a.transaction_id > 0')
                ->where('a.email != ""')
                ->where('a.email NOT IN (' .$subQuery. ')')
                ->group('email');

            $db->setQuery($query);

            $results2 = (array)$db->loadObjectList();
        }

        // Merge both data.
        if (count($results2) !== 0) {
            $results = array_merge($results, $results2);
        }

        // Add new subscribers.
        if (count($results) !== 0) {
            include_once(JPath::clean(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'));

            $component = JComponentHelper::getComponent('com_crowdfunding');

            foreach ($results as $result) {
                $userClass = acymailing_get('class.subscriber');
                /** @var subscriberClass $userClass */

                $user              = new stdClass();
                $user->email       = $result->email;
                $user->name        = $result->name;
                $user->userid      = (!isset($result->id)) ? 0 : (int)$result->id;
                $user->confirmed   = (!isset($result->id)) ? 0 : 1;
                $user->enabled     = 1;
                $user->accept      = 1;
                $user->html        = 1;
                $user->source      = 'component_'. $component->id;

                $userClass->save($user);
            }
        }
    }

    /**
     * Return AcyMailing lists.
     *
     * @param int $projectId
     * @param int $listId
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getAcyStats($projectId, $listId)
    {
        $result = array(
            'total' => 0,
            'for_importing' => 0
        );

        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Get total results.
        $query = $db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__crowdf_transactions', 'a'))
            ->where('a.project_id = '. (int)$projectId);

        $db->setQuery($query);

        $result['total'] = (int)$db->loadResult();

        // Get the number of items that have to be imported.
        $subscribersIds = $this->getSubscribersIds($projectId);
        if (count($subscribersIds) !== 0) {
            $query = $db->getQuery(true);
            $query
                ->select('COUNT(a.subid)')
                ->from($db->quoteName('#__acymailing_listsub', 'a'))
                ->where('a.listid = '. (int)$listId)
                ->where('a.subid IN ('. implode(',', $subscribersIds) . ')');

            $db->setQuery($query);
            $activeSubscribers = (int)$db->loadResult();

            $result['for_importing'] = count($subscribersIds) - $activeSubscribers;
        }

        return $result;
    }

    /**
     * Add funders to Acy Mailing list.
     *
     * @param int $projectId
     * @param int $listId
     *
     * @return int
     * @throws \RuntimeException
     */
    public function addFundersToAcyList($projectId, $listId)
    {
        $numberOfAddedSubscribers = 0;

        // Get the number of items that have to be imported.
        $subscribersIds = $this->getSubscribersIds($projectId);

        if (count($subscribersIds) !== 0) {
            $db = $this->getDbo();
            /** @var $db JDatabaseDriver */

            foreach ($subscribersIds as $id) {
                $query = $db->getQuery(true);

                $query
                    ->select('a.subid')
                    ->from($db->quoteName('#__acymailing_listsub', 'a'))
                    ->where('a.listid = '. (int)$listId)
                    ->where('a.subid = '. (int)$id);

                $db->setQuery($query);
                $subscriberId = (int)$db->loadResult();

                if (!$subscriberId) {
                    $query   = $db->getQuery(true);
                    $query
                        ->insert($db->quoteName('#__acymailing_listsub'))
                        ->set($db->quoteName('listid') .' = '. (int)$listId)
                        ->set($db->quoteName('subid') .' = '. (int)$id)
                        ->set($db->quoteName('subdate') .' = '. $db->quote(time()))
                        ->set($db->quoteName('status') .' = 1');

                    $db->setQuery($query);
                    $db->execute();

                    $numberOfAddedSubscribers++;
                }
            }
        }

        return $numberOfAddedSubscribers;
    }

    protected function getSubscribersIds($projectId)
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        $subscribersIds = array();

        // Get the number of items that have to be imported.
        $query = $db->getQuery(true);
        $query
            ->select('a.email')
            ->from($db->quoteName('#__users', 'a'))
            ->rightJoin($db->quoteName('#__crowdf_transactions', 'b') .' ON b.investor_id = a.id')
            ->where('b.project_id = '. (int)$projectId)
            ->where('b.investor_id > 0')
            ->group('email');

        $db->setQuery($query);
        $emails = (array)$db->loadColumn();

        $emails2 = array();
        if (JComponentHelper::isInstalled('com_crowdfundingdata')) {
            $query = $db->getQuery(true);
            $query
                ->select('a.email')
                ->from($db->quoteName('#__cfdata_records', 'a'))
                ->rightJoin($db->quoteName('#__crowdf_transactions', 'b') . ' ON b.project_id = a.project_id')
                ->where('a.project_id = ' . (int)$projectId)
                ->where('a.transaction_id > 0')
                ->group('email');

            $db->setQuery($query);
            $emails2 = (array)$db->loadColumn();
        }

        if (count($emails2) !== 0) {
            $emails = array_merge($emails, $emails2);
            $emails = array_unique($emails);
        }

        if (count($emails) !== 0) {
            foreach ($emails as $key => $email) {
                $emails[$key] = $db->quote($email);
            }

            $query = $db->getQuery(true);
            $query
                ->select('a.subid')
                ->from($db->quoteName('#__acymailing_subscriber', 'a'))
                ->where('a.email IN (' . implode(',', $emails) . ')');

            $db->setQuery($query);
            $subscribersIds = $db->loadColumn();
            $subscribersIds = Joomla\Utilities\ArrayHelper::toInteger($subscribersIds);
        }

        return $subscribersIds;
    }
}
