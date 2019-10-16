<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Helper;

use Prism\Constants;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to interact with project data.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
abstract class ProjectHelper
{
    /**
     * Return number formatter.
     *
     * <code>
     * $projectId   = 2;
     *
     * $slug        = $this->getSlug($projectId);
     * </code>
     *
     * @param int $id
     *
     * @throws \RuntimeException
     * @return string
     */
    public static function getSlug($id)
    {
        $db    = \JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select($query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug')
            ->from($db->quoteName('#__crowdf_projects', 'a'))
            ->where('a.id = ' . (int)$id)
            ->where('a.published = ' . Constants::PUBLISHED)
            ->where('a.approved = ' . Constants::APPROVED);

        $db->setQuery($query, 0, 1);
        return (string)$db->loadResult();
    }
}
