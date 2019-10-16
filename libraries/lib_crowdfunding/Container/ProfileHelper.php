<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Container;

use Joomla\DI\Container;
use Crowdfunding\Constants;
use Joomla\Registry\Registry;
use Prism\Utilities\StringHelper;
use Prism\Integration\Profile\Factory;
use Crowdfunding\Project;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare and inject social profile object in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
trait ProfileHelper
{
    /**
     * Prepare profile object and inject it in the container.
     *
     * <code>
     * $userId = 1;
     *
     * $this->prepareProfile($container, $params, $userId);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     * @param int $userId
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \OutOfBoundsException
     *
     * @return Project
     */
    protected function prepareProfile($container, $params, $userId)
    {
        $userId    = (int)abs($userId);
        $options   = array(
            'platform' => $params->get('integration_social_platform'),
            'user_id'  => $userId
        );
        
        $hash      = StringHelper::generateMd5Hash(Constants::CONTAINER_PROFILE, $options);

        if (!$container->exists($hash) and $userId > 0) {
            $config = new Registry($options);

            $socialProfileBuilder  = new Factory($config);
            $socialProfile         = $socialProfileBuilder->create();

            $container->set($hash, $socialProfile);
        }
    }

    /**
     * Return user profile.
     *
     * <code>
     * $userId = 1;
     *
     * $this->prepareProfile($container, $params, $userId);
     * $profile = $this->getProfile($container, $params, $userId);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     * @param int $userId
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \OutOfBoundsException
     *
     * @return Project
     */
    protected function getProfile($container, $params, $userId)
    {
        $userId    = (int)abs($userId);
        $options   = array(
            'platform' => $params->get('integration_social_platform'),
            'user_id'  => $userId
        );

        $hash      = StringHelper::generateMd5Hash(Constants::CONTAINER_PROFILE, $options);

        if (!$container->exists($hash) and $userId > 0) {
            $this->prepareProfile($container, $params, $userId);
        }

        return $container->get($hash);
    }
}
