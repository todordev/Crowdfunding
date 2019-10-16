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
use Prism\Utilities\StringHelper;
use Identityproof\User;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare and inject Proof of Identity objects in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
trait ProofHelper
{
    /**
     * Prepare profile object from Proof of Identity and inject it in the container.
     *
     * <code>
     * $userId = 1;
     *
     * $this->prepareProofProfile($container, $userId);
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
     * @return User
     */
    protected function prepareProofProfile($container, $userId)
    {
        $userId    = (int)abs($userId);
        $hash      = StringHelper::generateMd5Hash(Constants::CONTAINER_PROOF_PROFILE, $userId);

        if (!$container->exists($hash) and $userId > 0) {
            $proof = new User(\JFactory::getDbo());
            $proof->load($userId);

            if (!$proof->getId()) {
                $proof = null;
            }

            $container->set($hash, $proof);
        }
    }

    /**
     * Return profile object from Proof of Identity.
     *
     * <code>
     * $userId = 1;
     *
     * $this->prepareProfile($container, $userId);
     * $profile = $this->getProfile($container, $userId);
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
     * @return User
     */
    protected function getProofProfile($container, $userId)
    {
        $userId    = (int)abs($userId);
        $hash      = StringHelper::generateMd5Hash(Constants::CONTAINER_PROOF_PROFILE, $userId);

        if (!$container->exists($hash) and $userId > 0) {
            $this->prepareProofProfile($container, $userId);
        }

        return $container->get($hash);
    }
}
