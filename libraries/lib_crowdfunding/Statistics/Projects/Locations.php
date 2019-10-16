<?php
/**
 * @package      Crowdfunding\Statistics
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Statistics\Projects;

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

\JLoader::register('Crowdfunding\\Statistics\\Projects\\Base', JPATH_LIBRARIES . '/crowdfunding/statistics/projects/base.php');

/**
 * This class loads statistics about projects in locations.
 *
 * @package      Crowdfunding\Statistics
 * @subpackage   Locations
 */
class Locations extends Base
{
    /**
     * Load latest projects ordering by starting date of campaigns.
     *
     * <code>
     * $options = array(
     *     "limit"    => 20,
     *     "state"    => Prism\Constants::PUBLISHED,
     *     "approved" => Prism\Constants::APPROVED,
     *     "order"    => Crowdfunding\Constants::ORDER_BY_NAME,
     *     "order_dir"    => "DESC",
     *     "having"   => 5
     * );
     *
     * $locations = new Crowdfunding\Statistics\Locations\Projects(\JFactory::getDbo());
     * $locations->load($limit);
     *
     * foreach ($locations as $location) {
     *      echo $project["location_id"];
     *      echo $project["location_name"];
     *      echo $project["project_number"];
     * }
     * </code>
     *
     * @param array $options Some options that can be used to filter the result.
     */
    public function load(array $options = array())
    {
        $query = $this->getQuery();

        $query->select('a.location_id, COUNT(a.id) as project_number');
        $query->select('l.name as location_name');

        $query->innerJoin($this->db->quoteName('#__crowdf_locations', 'l') . ' ON a.location_id = l.id');
        $query->group('a.location_id');

        $this->prepareFilters($query, $options);
        $this->prepareOrder($query, $options);

        // Filter by number of projects in the results.
        if (!empty($options['having'])) {
            $query->having('project_number >= ' . (int)$options['having']);
        }

        // Get the limit of results.
        $limit   = ArrayHelper::getValue($options, 'limit', 10, 'int');

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }
}
