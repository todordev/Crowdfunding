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
use Crowdfunding\Reward;
use Prism\Utilities\StringHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare and inject Reward object in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 *
 * @deprecated v2.6
 */
trait RewardHelper
{
    /**
     * Prepare reward object and inject it in the container.
     *
     * <code>
     * $rewardId = 1;
     * $projectId = 2;
     *
     * $this->prepareReward($container, $rewardId, $projectId);
     * </code>
     *
     * @param Container $container
     * @param int $rewardId
     * @param int $projectId
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \OutOfBoundsException
     *
     * @return Reward
     */
    protected function prepareReward($container, $rewardId, $projectId)
    {
        $rewardId  = (int)abs($rewardId);
        $hash       = StringHelper::generateMd5Hash(Constants::CONTAINER_REWARD, array($rewardId, $projectId));

        if (!$container->exists($hash) and $rewardId > 0) {
            $reward = new Reward(\JFactory::getDbo());
            $reward->load(array('id' => $rewardId, 'project_id' => $projectId));

            if (!$reward->getId()) {
                $reward = null;
            }

            $container->set($hash, $reward);
        }
    }

    /**
     * Return reward object.
     *
     * <code>
     * $rewardId = 1;
     * $projectId = 2;
     *
     * $this->prepareReward($container, $rewardId, $projectId);
     * $reward = $this->getReward($container, $rewardId, $projectId);
     * </code>
     *
     * @param Container $container
     * @param int $rewardId
     * @param int $projectId
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \OutOfBoundsException
     *
     * @return Reward
     */
    protected function getReward($container, $rewardId, $projectId)
    {
        $rewardId = (int)abs($rewardId);
        $hash      = StringHelper::generateMd5Hash(Constants::CONTAINER_REWARD, array($rewardId, $projectId));

        if (!$container->exists($hash) and $rewardId > 0) {
            $this->prepareReward($container, $rewardId, $projectId);
        }

        return $container->get($hash);
    }
}
