<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');

/**
 * Component Route Helper that help to find a menu item.
 * IMPORTANT: It help us to find right MENU ITEM.
 *
 * Use router ...BuildRoute to build a link
 *
 * @static
 * @package        ITPrism Components
 * @subpackage     Crowdfunding
 * @since          1.5
 */
abstract class CrowdfundingHelperRoute
{
    protected static $projects = array();
    protected static $projectsAliases = array();
    protected static $lookup;

    /**
     * This method route item in the view "details".
     *
     * @param    int    $id    The id of the item.
     * @param    int    $catid The id of the category.
     * @param    string $screen
     *
     * @return string
     */
    public static function getDetailsRoute($id, $catid, $screen = null)
    {
        /**
         *
         * # category
         * We will check for view category first. If find a menu item with view "category" and "id" equality of the key,
         * we will get that menu item ( Itemid ).
         *
         * # categories view
         * If miss a menu item with view "category" we continue with searching but now for view "categories".
         * It is assumed view "categories" will be in the first level of the menu.
         * The view "categories" won't contain category ID so it has to contain 0 for ID key.
         */
        $needles = array(
            'details' => array((int)$id),
        );

        $catid = (int)$catid;

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=details&id=' . $id;
        if ($catid > 1) {

            $options = array('published' => 2);

            $categories = Crowdfunding\Categories::getInstance('crowdfunding', $options);
            $category   = $categories->get($catid);

            if ($category) {
                $needles['category']   = array_reverse($category->getPath());
                $needles['categories'] = array(0);
                $needles['discover']   = array(0);
                $link .= '&catid=' . $catid;
            }
        }

        // Set a screen page
        if (null !== $screen) {
            $link .= '&screen=' . $screen;
        }

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * This method route item in the view "projects".
     */
    public static function getProjectsRoute()
    {
        /**
         *
         * # category
         * We will check for view category first. If find a menu item with view "category" and "id" eqallity of the key,
         * we will get that menu item ( Itemid ).
         *
         * # categories view
         * If miss a menu item with view "category" we continue with searchin but now for view "categories".
         * It is assumed view "categories" will be in the first level of the menu.
         * The view "categories" won't contain category ID so it has to contain 0 for ID key.
         */
        $needles = array(
            'projects' => array(0),
        );

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=projects';

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * This method route item in the view "transactions".
     */
    public static function getTransactionsRoute()
    {
        /**
         *
         * # category
         * We will check for view category first. If find a menu item with view "category" and "id" eqallity of the key,
         * we will get that menu item ( Itemid ).
         *
         * # categories view
         * If miss a menu item with view "category" we continue with searchin but now for view "categories".
         * It is assumed view "categories" will be in the first level of the menu.
         * The view "categories" won't contain category ID so it has to contain 0 for ID key.
         */
        $needles = array(
            'transactions' => array(0),
        );

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=transactions';

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * This method route item in the view "report".
     *
     * @param int $id
     *
     * @return string
     */
    public static function getReportRoute($id = 0)
    {
        $needles = array(
            'report' => array(0),
        );

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=report';

        if ($id) {
            $link .= '&id='.(int)$id;
        }

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * @param    int    $id    The id of the item.
     * @param    int    $catid The id of the category.
     * @param    string $layout
     * @param    int    $rewardId
     *
     * @return string
     */
    public static function getBackingRoute($id, $catid, $layout = null, $rewardId = null)
    {
        /**
         *
         * # category
         * We will check for view category first. If find a menu item with view "category" and "id" eqallity of the key,
         * we will get that menu item ( Itemid ).
         *
         * # categories view
         * If miss a menu item with view "category" we continue with searchin but now for view "categories".
         * It is assumed view "categories" will be in the first level of the menu.
         * The view "categories" won't contain category ID so it has to contain 0 for ID key.
         */
        $needles = array(
            'details' => array((int)$id)
        );

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=backing&id=' . $id;
        if ($catid > 1) {
            $categories = JCategories::getInstance('crowdfunding');
            $category   = $categories->get($catid);

            if ($category) {
                $needles['category']   = array_reverse($category->getPath());
                $needles['categories'] = array(0);
                $needles['discover']   = array(0);
                $link .= '&catid=' . $catid;
            }
        }

        if (null !== $layout) {
            $link .= '&layout=' . $layout;
        }

        if ((null !== $rewardId) and $rewardId > 0) {
            $link .= '&rid=' . (int)$rewardId;
        }

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * @param    int    $id    The id of the item.
     * @param    int    $catid The id of the category.
     *
     * @return   string
     */
    public static function getEmbedRoute($id, $catid)
    {
        /**
         *
         * # category
         * We will check for view category first. If find a menu item with view "category" and "id" eqallity of the key,
         * we will get that menu item ( Itemid ).
         *
         * # categories view
         * If miss a menu item with view "category" we continue with searchin but now for view "categories".
         * It is assumed view "categories" will be in the first level of the menu.
         * The view "categories" won't contain category ID so it has to contain 0 for ID key.
         */
        $needles = array(
            'details' => array((int)$id)
        );

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=embed&id=' . $id;

        if ($catid > 1) {
            $categories = JCategories::getInstance('crowdfunding');
            $category   = $categories->get($catid);

            if ($category) {
                $needles['category']   = array_reverse($category->getPath());
                $needles['categories'] = array(0);
                $needles['discover']   = array(0);
                $link .= '&catid=' . $catid;
            }
        }

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * @param    int    $id    The id of the item.
     *
     * @return   string
     */
    public static function getFriendmailRoute($id)
    {
        $needles = array(
            'friendmail' => array(0),
        );

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=friendmail';

        if ($id) {
            $link .= '&id='.$id;
        }

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * @param    int    $id     The id of the item.
     * @param    string $layout Layout name.
     *
     * @return string
     */
    public static function getFormRoute($id = 0, $layout = null)
    {
        $needles = array(
            'project' => array(0)
        );

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=project';

        if ((int)$id > 0) {
            $link .= '&id=' . (int)$id;
        }

        if ($layout !== null) {
            $link .= '&layout=' . $layout;
        }

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * Prepare a link to discover page.
     *
     * @param array $params Parameters that should be added to the URI.
     *
     * @return string
     */
    public static function getDiscoverRoute(array $params = array())
    {
        $needles = array(
            'discover' => array(0)
        );

        //Create the link
        $link = 'index.php?option=com_crowdfunding&view=discover';

        if (count($params) > 0) {
            $link .= CrowdfundingHelper::generateUrlParams($params);
        }

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * Prepare a link to a category on discover page.
     *
     * @param int $categoryId
     *
     * @return string
     */
    public static function getCategoryRoute($categoryId = 0)
    {
        if ($categoryId instanceof JCategoryNode) {
            $id       = $categoryId->id;
            $category = $categoryId;
        } else {
            $id       = (int) $categoryId;
            $category = JCategories::getInstance('Crowdfunding')->get($id);
        }

        if ($id < 1 or !($category instanceof JCategoryNode)) {
            $link = '';
        } else {
            //Create the link
            $link = 'index.php?option=com_crowdfunding&view=category&id='.(int)$id;

            $ids = array_reverse($category->getPath());

            $needles = array(
                'category'   => $ids,
                'categories' => $ids
            );

            // Looking for menu item (Itemid)
            if ($item    = self::findItem($needles)) {
                $link .= '&Itemid=' . $item;
            }
        }

        return $link;
    }

    protected static function findItem($needles = null)
    {
        $app   = JFactory::getApplication();
        $menus = $app->getMenu('site');

        // Prepare the reverse lookup array.
        // Collect all menu items and creat an array that contains
        // the ID from the query string of the menu item as a key,
        // and the menu item id (Itemid) as a value
        // Example:
        // array( "category" =>
        //     1(id) => 100 (Itemid),
        //     2(id) => 101 (Itemid)
        // );
        if (self::$lookup === null) {
            self::$lookup = array();

            $component = JComponentHelper::getComponent('com_crowdfunding');
            $items     = $menus->getItems('component_id', $component->id);

            if ($items) {
                foreach ($items as $item) {
                    if (isset($item->query) and !empty($item->query['view'])) {
                        $view = $item->query['view'];

                        if (!isset(self::$lookup[$view])) {
                            self::$lookup[$view] = array();
                        }

                        if (!empty($item->query['id'])) {
                            self::$lookup[$view][$item->query['id']] = $item->id;
                        } else { // If it is a root element that have no a request parameter ID ( categories, authors ), we set 0 for an key
                            self::$lookup[$view][0] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view => $ids) {
                if (isset(self::$lookup[$view])) {
                    foreach ($ids as $id) {
                        if (isset(self::$lookup[$view][(int)$id])) {
                            return self::$lookup[$view][(int)$id];
                        }
                    }

                }
            }

        } else {
            $active = $menus->getActive();
            if ($active) {
                return $active->id;
            }
        }

        return null;
    }

    /**
     * Prepare categories path to the segments.
     * We use this method in the router "CrowdfundingParseRoute".
     *
     * @param integer $categoryId Category Id
     * @param array   $segments
     * @param object $menuItem
     * @param bool $menuItemGiven
     *
     * @return array
     */
    public static function prepareCategoriesSegments($categoryId, $segments, $menuItem, $menuItemGiven)
    {
        $menuCategoryId = 0;
        if ($menuItemGiven and (isset($menuItem->query) and !empty($menuItem->query['id']))) {
            $menuCategoryId = (int)$menuItem->query['id'];
        }

        $categories = Crowdfunding\Categories::getInstance('Crowdfunding');
        $category   = $categories->get($categoryId);

        if (!$category) {
            // We couldn't find the category we were given.
            return $segments;
        }

        $path = array_reverse($category->getPath());

        $array = array();

        // If a category ID match with an ID in a menu item,
        // we cannot generate an array with subcategories (aliases).
        foreach ($path as $id) {
            // Is an ID match with an ID in a menu item?
            if ($menuCategoryId === (int)$id) {
                break;
            }

            $array[] = str_replace(':', '-', $id);
        }

        $array = array_reverse($array);

        $segments = array_merge($segments, $array);

        return $segments;
    }

    /**
     * Load data about project.
     * We use this method in the router "CrowdfundingParseRoute".
     *
     * @param int $id
     *
     * @return array
     */
    public static function getProject($id)
    {
        $result = array();
        $id     = (int)$id;

        // Check for valid ID.
        if (!$id) {
            return $result;
        }

        // Return cached data.
        if (isset(self::$projects[$id])) {
            return self::$projects[$id];
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.id, a.alias, a.catid,' . $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug')
            ->from($query->quoteName('#__crowdf_projects', 'a'))
            ->where('a.id = ' . (int)$id);

        $db->setQuery($query);
        $result = $db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        self::$projects[$id] = $result;

        return self::$projects[$id];
    }

    /**
     * Load the project alias from database.
     * We use this method in the router "CrowdfundingParseRoute".
     *
     * @param int $id
     *
     * @return array
     */
    public static function getProjectAlias($id)
    {
        $result = '';
        $id     = (int)$id;

        // Check for valid ID.
        if (!$id) {
            return $result;
        }

        // Return cached data.
        if (isset(self::$projectsAliases[$id])) {
            return self::$projectsAliases[$id];
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.alias')
            ->from($query->quoteName('#__crowdf_projects', 'a'))
            ->where('a.id = ' . (int)$id);

        $db->setQuery($query, 0, 1);
        $result = $db->loadResult();

        if (!$result) {
            $result = '';
        }

        self::$projectsAliases[$id] = $result;

        return self::$projectsAliases[$id];
    }

    /**
     * Check if the alias of a category or a project.
     *
     * @param $segments
     *
     * @return array
     */
    public static function prepareCategoryOrDetails($segments)
    {
        $vars = array();
        $lastSegment = end($segments);

        // Remove last segment, if it is "index.php".
        if (strcmp('index.php', $lastSegment) === 0) {
            array_pop($segments);
            $lastSegment = end($segments);
        }

        list($id, $alias) = explode(':', $lastSegment, 2);
        $alias = str_replace(':', '-', $alias);

        // first we check if it is a category
        $category = JCategories::getInstance('Crowdfunding')->get($id);

        if ($category and (strcmp($category->alias, $alias) === 0)) {
            $vars['view'] = 'category';
            $vars['id']   = $id;
        } else {
            $project = self::getProject($id);
            if ((0 !== count($project)) and ($project['alias'] === $alias)) {
                $vars['view']  = 'details';
                $vars['catid'] = (int)$project['catid'];
                $vars['id']    = (int)$id;
            }
        }

        return $vars;
    }
}
