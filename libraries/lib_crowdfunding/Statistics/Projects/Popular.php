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
 * This class loads statistics about popular projects.
 *
 * @package      Crowdfunding\Statistics
 * @subpackage   Projects
 */
class Popular extends Base
{
    /**
     * Load data about the most funded projects.
     *
     * <code>
     * $popular = new Crowdfunding\Statistics\Projects\Popular(JFactory::getDbo());
     * $popular->load(['limit' => 5]);
     *
     * foreach ($popular as $project) {
     *      echo $project["title"];
     *      echo $project["hits"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        $limit = ArrayHelper::getValue($options, 'limit', 5, 'int');

        // Get current date
        jimport('joomla.date.date');
        $date  = new \JDate();
        $today = $date->toSql();

        $query = $this->getQuery();

        $query
            ->where('( a.published = 1 AND a.approved = 1 )')
            ->where('( a.funding_start <= ' . $this->db->quote($today) . ' AND a.funding_end >= ' . $this->db->quote($today) . ' )')
            ->order('a.hits DESC');

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }
}
