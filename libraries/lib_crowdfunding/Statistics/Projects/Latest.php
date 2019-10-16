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
use Prism\Constants;

defined('JPATH_PLATFORM') or die;

\JLoader::register('Crowdfunding\\Statistics\\Projects\\Base', JPATH_LIBRARIES . '/crowdfunding/statistics/projects/base.php');

/**
 * This class loads statistics about projects.
 *
 * @package      Crowdfunding\Statistics
 * @subpackage   Projects
 */
class Latest extends Base
{
    /**
     * Load latest projects ordering by starting date of campaigns.
     *
     * <code>
     * $options = array(
     *     'limit' => 10,
     *     'order_by' => 'funding_start',
     *     'order_direction' => 'DESC'
     * );
     *
     * $latest = new Crowdfunding\Statistics\Projects\Latest(\JFactory::getDbo());
     * $latest->load($options);
     *
     * foreach ($latest as $project) {
     *      echo $project["title"];
     *      echo $project["funding_start"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        $limit   = ArrayHelper::getValue($options, 'limit', 5, 'int');
        $orderBy = ArrayHelper::getValue($options, 'order_by');

        $query = $this->getQuery();

        $query
            ->where('a.published = ' . Constants::PUBLISHED)
            ->where('a.approved = ' . Constants::APPROVED);

        if ($orderBy !== null and $orderBy !== '') {
            $orderDirection = \JString::trim(ArrayHelper::getValue($options, 'order_direction', 'DESC', 'cmd'));
            $orderDirection = (!in_array($orderDirection, array('ASC', 'DESC'), true)) ? 'DESC' : 'ASC';
            $query->order($this->db->quote('a.'.$orderBy).' ' . $orderDirection);
        }

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }
}
