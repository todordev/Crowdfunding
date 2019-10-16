<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JLoader::register('CrowdfundingModelCategory', CROWDFUNDING_PATH_COMPONENT_SITE.'/models/category.php');

class CrowdfundingModelDiscover extends CrowdfundingModelCategory
{

}
