<?php
/**
 * @package      Crowdfunding
 * @subpackage   Constants
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding;

defined('JPATH_PLATFORM') or die;

/**
 * Crowdfunding constants
 *
 * @package      Crowdfunding
 * @subpackage   Constants
 */
class Constants
{
    // Session contexts
    const PAYMENT_SESSION_CONTEXT = 'payment_session_project';
    const CROPPED_IMAGES_CONTEXT  = 'cropped_images_project';
    const TEMPORARY_IMAGE_CONTEXT = 'temporary_image_project';

    // Funding Types
    const FUNDING_TYPE_FIXED    = 'FIXED';
    const FUNDING_TYPE_FLEXIBLE = 'FLEXIBLE';

    // Filtering
    const FILTER_STARTED_SOON = 'ssoon';
    const FILTER_ENDING_SOON = 'esoon';
    const FILTER_SUCCESSFULLY_COMPLETED = 'sfunded';

    // Ordering
    const ORDER_BY_ORDERING = 0;
    const ORDER_BY_NAME = 1;
    const ORDER_BY_CREATED_DATE = 2;
    const ORDER_BY_START_DATE = 3;
    const ORDER_BY_END_DATE = 4;
    const ORDER_BY_POPULARITY = 5;
    const ORDER_BY_FUNDING = 6;
    const ORDER_BY_FANS = 7;
    const ORDER_BY_LOCATION_NAME = 10;
    const ORDER_BY_NUMBER_OF_PROJECTS = 20;
    
    // Caching
    const CACHE_COUNTRIES_CODES = 'countries_codes';
    const CACHE_COUNTRIES_IDS = 'countries_ids';
    const CACHE_CATEGORIES = 'categories';
    const CACHE_PROJECT_TYPES = 'project_types';
    const CACHE_TOWNS = 'towns';
    const CACHE_STATISTIC_VALUES = 'statistic_values';
    const CACHE_USER_PROJECTS = 'user_projects';
    const CACHE_USER_PLEDGED_PROJECTS = 'user_pledged_projects';

    // Container
    const CONTAINER_CURRENCY = 'com_crowdfunding.currency';
    const CONTAINER_PROJECT  = 'com_crowdfunding.project';
    const CONTAINER_REWARD  = 'com_crowdfunding.reward';
    const CONTAINER_PROFILE  = 'com_crowdfunding.profile';
    const CONTAINER_PROOF_PROFILE  = 'com_crowdfunding.profile.proof';
    const CONTAINER_FORMATTER_MONEY = 'com_crowdfunding.money.formatter';
    const CONTAINER_PARSER_MONEY = 'com_crowdfunding.money.parser';
    const CONTAINER_FORMATTER_NUMBER = 'com_crowdfunding.number';
}
