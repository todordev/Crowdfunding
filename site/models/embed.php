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

class CrowdfundingModelEmbed extends JModelItem
{
    protected $item = array();

    /**
     * Model context string.
     *
     * @var    string
     * @since  11.1
     */
    protected $context = 'com_crowdfunding.embed';

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
        $app    = JFactory::getApplication();
        /** @var $app JApplicationSite */
        $params = $app->getParams();

        // Load the object state.
        $id = $app->input->getInt('id');
        $this->setState($this->context . '.id', $id);

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Method to get an object.
     *
     * @param    integer $id The id of the object to get.
     *
     * @return    mixed    Object on success, false on failure.
     * @throws \RuntimeException
     */
    public function getItem($id = 0)
    {
        if ((int)$id === 0) {
            $id = $this->getState($this->context . '.id');
        }
        $storedId = $this->getStoreId($id);

        if (!array_key_exists($storedId, $this->item)) {
            $this->item[$storedId] = null;

            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select(
                    'a.id, a.title, a.short_desc, a.image,  ' .
                    'a.funded, a.goal, a.user_id, a.funding_type, ' .
                    'a.funding_start, a.funding_end, a.funding_days,  ' .
                    $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug, ' .
                    'b.name AS user_name, ' .
                    $query->concatenate(array('c.id', 'c.alias'), ':') . ' AS catslug '
                )
                ->from($db->quoteName('#__crowdf_projects', 'a'))
                ->innerJoin($db->quoteName('#__users', 'b') . ' ON a.user_id = b.id')
                ->innerJoin($db->quoteName('#__categories', 'c') .' ON a.catid = c.id')
                ->where('a.id = ' . (int)$id)
                ->where('a.published = 1')
                ->where('a.approved  = 1');

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();

            // Attempt to load the row.
            if ($result !== null && is_object($result)) {
                // Calculate funded percentage.
                $result->funded_percents = Prism\Utilities\MathHelper::calculatePercentage($result->funded, $result->goal, 0);

                // Calculate days left
                $today = new Crowdfunding\Date();
                $result->days_left       = $today->calculateDaysLeft($result->funding_days, $result->funding_start, $result->funding_end);

                $this->item[$storedId]   = $result;
            }
        }

        return $this->item[$storedId];
    }
}
