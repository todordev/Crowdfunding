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
use Crowdfunding\Project\Mapper;
use Crowdfunding\Project\Repository;
use Crowdfunding\Project\Gateway\ProjectGateway;
use Crowdfunding\Project\Project as UserProject;

/**
 * This class provides functionality to prepare and inject Project object in the container.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
final class Project
{
    const CONTAINER_PROJECT = 'com_crowdfunding.project';

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
     * Fetch a project by its ID.
     *
     * <code>
     * $projectId = 1;
     * $helper    = new Helper\Project($container);
     * $project   = $helper->getProject($projectId, new JoomlaGateway(\JFactory::getDbo()));
     * </code>
     *
     * @param int $projectId
     * @param ProjectGateway $gateway
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \OutOfBoundsException
     *
     * @return UserProject|null
     */
    public function getProject($projectId, ProjectGateway $gateway)
    {
        $projectId = (int)abs($projectId);
        $hash      = StringHelper::generateMd5Hash(self::CONTAINER_PROJECT, $projectId);

        $project   = null;
        if ($projectId > 0) {
            if ($this->container->exists($hash)) {
                $project = $this->container->get($hash);
            } else {
                $mapper     = new Mapper($gateway);
                $repository = new Repository($mapper);
                $project    = $repository->fetchById($projectId);

                if (!$project->getId()) {
                    $project = null;
                }

                $this->container->set($hash, $project);
            }
        }

        return $project;
    }
}
