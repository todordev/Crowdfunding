<?php
/**
 * @package      Crowdfunding\Category\Helper\Gateway
 * @subpackage   Joomla
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Category\Helper\Gateway\Joomla;

use Prism\Constants;
use Prism\Database\JoomlaDatabase;
use Crowdfunding\Category\Helper\Gateway\ProjectCounterGateway;

/**
 * Joomla database gateway.
 *
 * @package      Crowdfunding\Category\Helper\Gateway
 * @subpackage   Joomla
 */
class ProjectCounter extends JoomlaDatabase implements ProjectCounterGateway
{
    /**
     * Return the number of projects.
     *
     * @param array  $ids
     * @param int  $state
     * @param int  $approved
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return array
     */
    public function count(array $ids, $state = Constants::PUBLISHED, $approved = Constants::APPROVED)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.catid, COUNT(*) as number')
            ->from($this->db->quoteName('#__crowdf_projects', 'a'))
            ->group('a.catid')
            ->where('a.catid IN (' . implode(',', $ids) . ')');

        // Filter by state.
        if (is_numeric($state)) {
            $query->where('a.published = ' . (int)$state);
        } else {
            $query->where('a.published IN (0,1)');
        }

        // Filter by approve state.
        if (is_numeric($approved)) {
            $query->where('a.approved = ' . (int)$approved);
        } else {
            $query->where('a.approved IN (0, 1)');
        }

        $this->db->setQuery($query);

        return (array)$this->db->loadAssocList('catid');
    }
}
