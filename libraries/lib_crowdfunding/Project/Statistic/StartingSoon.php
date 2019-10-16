<?php
/**
 * @package      Crowdfunding\Project
 * @subpackage   Statistic
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project\Statistic;

use Prism\Constants;

defined('JPATH_PLATFORM') or die;

/**
 * This class loads started soon projects.
 *
 * @package      Crowdfunding\Project
 * @subpackage   Statistic
 */
class StartingSoon extends Base
{
    /**
     * Load starting soon projects.
     *
     * <code>
     * $options = array(
     *     'limit' => 10,
     *     'order_by' => 'funding_start',
     *     'order_direction' => 'DESC',
     *     'period' = 3
     * );
     *
     * $projects = new Crowdfunding\Project\Statistic\StartingSoon(\JFactory::getDbo());
     * $projects->load($options);
     *
     * foreach ($projects as $project) {
     *      echo $project["title"];
     *      echo $project["funding_start"];
     * }
     * </code>
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function load(array $options = array())
    {
        $period = (array_key_exists('period', $options) and (int)$options['period'] > 0) ? (int)$options['period'] : 3;

        $date   = new \JDate();
        $today  = $date->toSql();

        $date->add(new \DateInterval('P'.$period.'D'));

        $query = $this->getQuery();
        $query
            ->where('a.published = ' . Constants::PUBLISHED)
            ->where('a.approved = ' . Constants::APPROVED)
            ->where('a.funding_start >= ' . $this->db->quote($today) . ' AND a.funding_start <= '. $this->db->quote($date->toSql()));

        $limit      = $this->getOptionLimit($options);
        $orderBy    = $this->getOptionOrderColumn($options);
        if ($orderBy !== '') {
            $orderDirection = $this->getOptionOrderDirection($options, 'ASC');
            $query->order($this->db->quote($orderBy).' ' .$orderDirection);
        }

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }
}
