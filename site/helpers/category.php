<?php
/**
 * @package      Crowdfunding
 * @subpackage   Categories
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

/**
 * This class provides functionality that manage categories.
 *
 * @package      Crowdfunding
 * @subpackage   Categories
 *
 * @deprecated v2.7 Replace this with Crowdfunding\Category\Categories.
 */
class CrowdfundingCategories extends \JCategories
{
    /**
     * The property that contains categories.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    public function __construct($options = array())
    {
        $options['table']     = '#__crowdf_projects';
        $options['extension'] = 'com_crowdfunding';

        parent::__construct($options);
    }
}
