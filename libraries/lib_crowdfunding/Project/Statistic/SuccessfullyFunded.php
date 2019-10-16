<?php
/**
 * @package      Crowdfunding\Project
 * @subpackage   Statistic
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project\Statistic;

use Joomla\Utilities\ArrayHelper;
use Prism\Constants;

defined('JPATH_PLATFORM') or die;

/**
 * This class loads successfully funded projects.
 *
 * @package      Crowdfunding\Project
 * @subpackage   Statistic
 */
class SuccessfullyFunded extends Base
{
    /**
     * Load successfully completed projects.
     *
     * <code>
     * $options = array(
     *     'limit' => 10,
     *     'order_by' => 'funding_start',
     *     'order_direction' => 'DESC'
     * );
     *
     * $projects = new Crowdfunding\Project\Statistic\SuccessfullyFunded(\JFactory::getDbo());
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
        $today      = new \JDate();
        $todayDate  = $today->toSql();

        $query = $this->getQuery();

        $query
            ->where('a.published = ' . Constants::PUBLISHED)
            ->where('a.approved = ' . Constants::APPROVED)
            ->where('a.funded > a.goal')
            ->where('a.funding_end < ' . $this->db->quote($todayDate));

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
