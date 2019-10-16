<?php
/**
 * @package         Crowdfunding\Project
 * @subpackage      Gateway
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Project\Gateway;

use Prism\Domain\RichFetcher;
use Crowdfunding\Project\Project;

/**
 * Contract between database drivers and gateway objects.
 *
 * @package         Crowdfunding\Project
 * @subpackage      Gateway
 */
interface ProjectGateway extends RichFetcher
{
    /**
     * Insert a record to database.
     *
     * @param Project $object
     *
     * @return mixed
     */
    public function insertObject(Project $object);

    /**
     * Update a record in database.
     *
     * @param Project $object
     *
     * @return mixed
     */
    public function updateObject(Project $object);
}
