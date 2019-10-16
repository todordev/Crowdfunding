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
use Crowdfunding\Project;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare and inject Project object in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 *
 * @deprecated v2.6
 */
trait ProjectHelper
{
    /**
     * Prepare project object and inject it in the container.
     *
     * <code>
     * $projectId = 1;
     *
     * $this->prepareProject($container, $projectId);
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
    protected function prepareProject($container, $projectId)
    {
        $projectId  = (int)abs($projectId);
        $hash       = StringHelper::generateMd5Hash(Constants::CONTAINER_PROJECT, $projectId);

        if (!$container->exists($hash) and $projectId > 0) {
            $project = new Project(\JFactory::getDbo());
            $project->load($projectId);

            if (!$project->getId()) {
                $project = null;
            }

            $container->set($hash, $project);
        }
    }

    /**
     * Return project.
     *
     * <code>
     * $projectId = 1;
     *
     * $this->prepareProject($container, $projectId);
     * $project = $this->getProject($container, $projectId);
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
    protected function getProject($container, $projectId)
    {
        $projectId = (int)abs($projectId);
        $hash      = StringHelper::generateMd5Hash(Constants::CONTAINER_PROJECT, $projectId);

        $project = null;
        if ($projectId > 0) {
            if ($container->exists($hash)) {
                $project = $container->get($hash);
            } else {
                $this->prepareProject($container, $projectId);
                $project = $container->get($hash);
            }
        }

        return $project;
    }
}
