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
use Joomla\Registry\Registry;
use Crowdfunding\Currency\Currency;
use Crowdfunding\Project;
use Crowdfunding\Reward;
use Prism\Money\MoneyFormatter;
use Prism\Integration\Profile\ProfileInterface;
use Identityproof\User as ProofUser;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that returns objects from the container.
 * This class uses helper traits of the container to prepare and fetch the objects.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 *
 * @deprecated v2.6 used container helper for every namespace.
 * @todo Do container helpers for every namespace.
 */
class Helper
{
    use MoneyHelperDeprecated, NumberHelper, ProjectHelper, ProfileHelper, ProofHelper, RewardHelper;

    /**
     * Return currency object.
     *
     * <code>
     * $helper   = new Crowdfunding\Container\Helper();
     * $currency = $this->fetchCurrency($container, $params);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     *
     * @return Currency
     */
    public function fetchCurrency($container, $params)
    {
        return $this->getCurrency($container, $params);
    }

    /**
     * Return money formatter.
     *
     * <code>
     * $helper   = new Crowdfunding\Container\Helper();
     * $money    = $this->fetchMoneyFormatter($container, $params);
     * </code>
     *
     * @param Container $container
     * @param Registry $params
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     *
     * @return MoneyFormatter
     */
    public function fetchMoneyFormatter($container, $params)
    {
        return $this->getMoneyFormatter($container, $params);
    }

    /**
     * Return a project.
     *
     * <code>
     * $projectId = 1;
     *
     * $helper   = new Crowdfunding\Container\Helper();
     * $project  = $this->fetchProject($container, $projectId);
     * </code>
     *
     * @param Container $container
     * @param int $projectId
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \OutOfBoundsException
     *
     * @return Project
     */
    public function fetchProject($container, $projectId)
    {
        return $this->getProject($container, $projectId);
    }

    /**
     * Return user profile.
     *
     * <code>
     * $userId = 1;
     *
     * $helper   = new Crowdfunding\Container\Helper();
     * $profile  = $this->fetchProfile($container, $params, $userId);
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
     * @return ProfileInterface|null
     */
    public function fetchProfile($container, $params, $userId)
    {
        return $this->getProfile($container, $params, $userId);
    }

    /**
     * Return profile from Proof of Identity.
     *
     * <code>
     * $userId = 1;
     *
     * $helper   = new Crowdfunding\Container\Helper();
     * $profile  = $this->fetchProfile($container, $userId);
     * </code>
     *
     * @param Container $container
     * @param int $userId
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \OutOfBoundsException
     *
     * @return ProofUser|null
     */
    public function fetchProofProfile($container, $userId)
    {
        return $this->getProofProfile($container, $userId);
    }

    /**
     * Return reward object.
     *
     * <code>
     * $rewardId = 1;
     * $projectId = 1;
     *
     * $helper   = new Crowdfunding\Container\Helper();
     * $reward   = $this->fetchReward($container, $rewardId, $projectId);
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
     * @return Reward|null
     */
    public function fetchReward($container, $rewardId, $projectId)
    {
        return $this->getReward($container, $rewardId, $projectId);
    }
}
