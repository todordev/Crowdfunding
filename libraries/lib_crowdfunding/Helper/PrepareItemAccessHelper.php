<?php
/**
 * @package      Crowdfunding
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfunding\Helper;

use Prism\Helper\HelperInterface;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare an item access.
 *
 * @package      Crowdfunding
 * @subpackage   Helpers
 */
class PrepareItemAccessHelper implements HelperInterface
{
    /**
     * User object.
     *
     * @var \JUser
     */
    protected $user;

    /**
     * Initialize the object.
     *
     * @param \JUser $user
     */
    public function __construct(\JUser $user)
    {
        $this->user = $user;
    }

    /**
     * Prepare an item access level.
     *
     * @param \stdClass $data
     * @param array $options
     */
    public function handle(&$data, array $options = array())
    {
        if (is_object($data)) {
            $userId     = (int)$this->user->get('id');
            $guest      = $this->user->get('guest');
            $viewGroups = $this->user->getAuthorisedViewLevels();

            // Compute the asset access permissions.
            // Technically guest could edit an article, but lets not check that to improve performance a little.
            if (!$guest) {
                $asset = 'com_crowdfunding.item.' . $data->id;

                // Check general edit permission first.
                if ($userId > 0 && $this->user->authorise('core.edit', $asset)) {
                    $data->params->set('access-edit', true);
                }

                // Now check if edit.own is available.
                if ($userId > 0 && $this->user->authorise('core.edit.own', $asset)) {
                    // Check for a valid user and that they are the owner.
                    if ($userId === (int)$data->user_id) {
                        $data->params->set('access-edit', true);
                    }
                }
            }

            // If no access filter is set, the layout takes some responsibility for display of limited information.
            if (!$data->catid || !$data->category_access) {
                $data->params->set('access-view', in_array((int)$data->access, $viewGroups, true));
            } else {
                $data->params->set('access-view', in_array((int)$data->access, $viewGroups, true) && in_array((int)$data->category_access, $viewGroups, true));
            }
        }
    }
}
