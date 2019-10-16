<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

/**
 * @var Joomla\CMS\Application\CMSApplication $app
 * @var Joomla\Registry\Registry $params
 */

defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

$option = $app->input->get('option');
$view   = $app->input->get('view');

// If option is not 'com_crowdfunding' and view is not 'details',
// do not display anything.
if ((strcmp($option, 'com_crowdfunding') !== 0) || (strcmp($view, 'details') !== 0)) {
    echo JText::_('MOD_CROWDFUNDINGINFO_ERROR_INVALID_VIEW');
    return;
}

$projectId = $app->input->getInt('id');
if (!$projectId) {
    echo JText::_('MOD_CROWDFUNDINGINFO_ERROR_INVALID_PROJECT');
    return;
}

$componentParams = JComponentHelper::getParams('com_crowdfunding');
/** @var  $componentParams Joomla\Registry\Registry */

// Get Project object from container.
$project        = Crowdfunding\Facade\Joomla::getProject($projectId);

$currency       = Crowdfunding\Facade\Joomla::getCurrency();
$moneyFormatter = Crowdfunding\Facade\Joomla::getMoneyFormatter();
$goal           = $moneyFormatter->formatCurrency(new \Prism\Money\Money($project->getGoal(), $currency));
$funded         = $moneyFormatter->formatCurrency(new \Prism\Money\Money($project->getFunded(), $currency));

// Validate end date.
$dateValidator    = new Prism\Validator\Date($project->getFundingEnd());
$isValidEndDate   = $dateValidator->isValid();

require JModuleHelper::getLayoutPath('mod_crowdfundinginfo', $params->get('layout', 'default'));
