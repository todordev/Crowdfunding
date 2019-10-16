<?php
/**
 * Prepares a minimalist framework for unit testing.
 *
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

define('PATH_JOOMLA_LIBRARIES', str_replace(DIRECTORY_SEPARATOR.'Crowdfunding'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'unit', DIRECTORY_SEPARATOR, __DIR__));
define('PATH_CROWDFUNDING_FOLDER', PATH_JOOMLA_LIBRARIES.'Crowdfunding'.DIRECTORY_SEPARATOR);
define('PATH_CROWDFUNDING_TESTS_FOLDER', PATH_CROWDFUNDING_FOLDER.'tests'.DIRECTORY_SEPARATOR);
define('PATH_CROWDFUNDING_TESTS_UNIT_FOLDER', PATH_CROWDFUNDING_TESTS_FOLDER . 'unit'. DIRECTORY_SEPARATOR);
define('PATH_CROWDFUNDING_TESTS_SCHEMA_FOLDER', PATH_CROWDFUNDING_TESTS_FOLDER . 'schema'. DIRECTORY_SEPARATOR);
define('PATH_CROWDFUNDING_TESTS_STUBS_DATA_FOLDER', PATH_CROWDFUNDING_TESTS_UNIT_FOLDER . 'stubs'. DIRECTORY_SEPARATOR. 'data'. DIRECTORY_SEPARATOR);
define('PATH_CROWDFUNDING_TESTS_STUBS_DATABASE_FOLDER', PATH_CROWDFUNDING_TESTS_UNIT_FOLDER . 'stubs'. DIRECTORY_SEPARATOR . 'database'. DIRECTORY_SEPARATOR);


define('PATH_JOOMLA_TESTS_UNIT_FOLDER', str_replace('libraries'.DIRECTORY_SEPARATOR.'Crowdfunding'.DIRECTORY_SEPARATOR, 'tests'.DIRECTORY_SEPARATOR.'unit'.DIRECTORY_SEPARATOR, PATH_CROWDFUNDING_FOLDER));

/**
 * Include the main bootstrap and config file.
 */
require_once PATH_JOOMLA_TESTS_UNIT_FOLDER . 'bootstrap.php';
//include_once CROWDFUNDING_TESTS_FOLDER_UNIT . 'config.php';

jimport('Prism.init');
jimport('Crowdfunding.init');

// Register the core Joomla test classes.
JLoader::registerPrefix('CrowdfundingTest', __DIR__ . '/core');
JLoader::register(CrowdfundingMock\Currency\Gateway\JoomlaGateway::class, __DIR__ . '/core/mock/Currency/Gateway/JoomlaGateway.php');
