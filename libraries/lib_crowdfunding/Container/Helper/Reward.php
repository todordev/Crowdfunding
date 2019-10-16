<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Container\Helper;

use Joomla\DI\Container;
use Prism\Utilities\StringHelper;
use Prism\Database\Request\Request;
use Prism\Database\Request\Condition;
use Crowdfunding\Reward\Mapper;
use Crowdfunding\Reward\Repository;
use Crowdfunding\Reward\Reward as ProjectReward;
use Crowdfunding\Reward\Gateway\JoomlaGateway as RewardGateway;

/**
 * Container helper for preparing and injecting Reward object in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
final class Reward
{
    const CONTAINER_REWARD = 'com_crowdfunding.reward';

    /**
     * @var Container
     */
    private $container;

    /**
     * Helper constructor.
     *
     * @param Container  $container DI container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Fetch Reward object from container and return it.
     * If there is no Reward object in the container, it will create it and inject it.
     *
     * <code>
     * $rewardId  = 1;
     * $projectId = 2;
     *
     * $rewardHelper = new Helper\Reward($container);
     * $reward       = $this->getReward($rewardId, new JoomlaGateway(JFactory::getDbo()), $projectId);
     * </code>
     *
     * @param int $rewardId
     * @param RewardGateway $gateway
     * @param int $projectId
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \OutOfBoundsException
     *
     * @return ProjectReward
     */
    public function getReward($rewardId, RewardGateway $gateway, $projectId = 0)
    {
        $rewardId   = (int)abs($rewardId);
        $projectId  = (int)abs($projectId);

        $ids        = $projectId ? [$projectId, $rewardId] : $rewardId;
        $hash       = StringHelper::generateMd5Hash(self::CONTAINER_REWARD, $ids);

        $reward = null;
        if ($rewardId > 0) {
            if (!$this->container->exists($hash)) {
                $databaseRequest = new Request();
                $databaseRequest->addCondition(new Condition(['column' => 'id', 'value' => $rewardId]));
                if ($projectId > 0) {
                    $databaseRequest->addCondition(new Condition(['column' => 'project_id', 'value' => $projectId]));
                }

                $repository = new Repository(new Mapper($gateway));
                $reward     = $repository->fetch($databaseRequest);

                if (!$reward->getId()) {
                    $reward = null;
                }

                $this->container->set($hash, $reward);
            } else {
                $reward = $this->container->get($hash);
            }
        }

        return $reward;
    }
}
