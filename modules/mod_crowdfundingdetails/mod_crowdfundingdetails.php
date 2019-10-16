<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

$option = $app->input->get('option');
$view   = $app->input->get('view');

$allowedViews = array('backing', 'embed', 'report', 'friendmail');

// If option is not 'com_crowdfunding' and view is not one of allowed,
// do not display anything.
if ((strcmp($option, 'com_crowdfunding') !== 0) or (!in_array($view, $allowedViews, true))) {
    echo JText::_('MOD_CROWDFUNDINGDETAILS_ERROR_INVALID_VIEW');
    return;
}

$projectId = $app->input->getInt('id');
if (!$projectId) {
    return;
}

$container  = Prism\Container::getContainer();
/** @var  $container Joomla\DI\Container */

$containerHelper = new Crowdfunding\Container\Helper();

// Get Project object from the container.
$project     = $containerHelper->fetchProject($container, $projectId);
if (!$project->getId()) {
    return;
}

// Get component params
$componentParams = JComponentHelper::getParams('com_crowdfunding');
/** @var  $componentParams Joomla\Registry\Registry */

$money           = $containerHelper->fetchMoneyFormatter($container, $componentParams);

$socialPlatform  = $componentParams->get('integration_social_platform');
$imageFolder     = $componentParams->get('images_directory', 'images/crowdfunding');
$imageWidth      = $componentParams->get('image_width', 200);
$imageHeight     = $componentParams->get('image_height', 200);

// Get social platform and a link to the profile
$config = new Joomla\Registry\Registry(
    array(
        'platform' => $socialPlatform,
        'user_id' => $project->getUserId()
    )
);
$socialBuilder     = new Prism\Integration\Profile\Factory($config);
$socialProfile     = $socialBuilder->create();
$socialProfileLink = (!$socialProfile) ? null : $socialProfile->getLink();

// Get amounts
$fundedAmount = $money->setAmount($project->getGoal())->formatCurrency();
$raised       = $money->setAmount($project->getFunded())->formatCurrency();

// Prepare the value that I am going to display
$fundedPercents = JHtml::_('crowdfunding.funded', $project->getFundedPercent());

$user = JFactory::getUser($project->getUserId());

require JModuleHelper::getLayoutPath('mod_crowdfundingdetails', $params->get('layout', 'default'));