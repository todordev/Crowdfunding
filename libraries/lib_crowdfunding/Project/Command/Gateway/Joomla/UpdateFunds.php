<?php
/**
 * @package      Crowdfunding\Project\Command\Gateway
 * @subpackage   Joomla
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project\Command\Gateway\Joomla;

use Crowdfunding\Project\Project;
use Prism\Database\JoomlaDatabase;
use Crowdfunding\Project\Command\Gateway\UpdateFundsGateway;

/**
 * Joomla database gateway.
 *
 * @package      Crowdfunding\Project\Command\Gateway
 * @subpackage   Joomla
 */
class UpdateFunds extends JoomlaDatabase implements UpdateFundsGateway
{
    /**
     * Update project funds.
     *
     * @param Project  $project
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     */
    public function update(Project $project)
    {
        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName('#__crowdf_projects'))
            ->set($this->db->quoteName('funded') . '=' . $this->db->quote($project->getFunded()))
            ->where($this->db->quoteName('id') . '=' . (int)$project->getId());

        $this->db->setQuery($query);
        $this->db->execute();
    }
}
