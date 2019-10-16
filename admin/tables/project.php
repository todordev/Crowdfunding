<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class CrowdfundingTableProject extends JTable
{
    public $id;
    public $title;
    public $alias;
    public $image;
    public $image_small;
    public $image_square;
    public $goal;
    public $funded;
    public $funding_start;
    public $funding_end;
    public $funding_days;
    public $catid;

    protected $fundedPercent = 0;
    protected $daysLeft = 0;
    protected $slug = '';
    protected $catslug = '';
    protected $category_access = 0;
    protected $location_preview = '';

    /**
     * @param JDatabaseDriver $db
     */
    public function __construct($db)
    {
        parent::__construct('#__crowdf_projects', 'id', $db);
    }

    /**
     * Method to load a row from the database by primary key and bind the fields
     * to the JTable instance properties.
     *
     * @param   mixed   $keys  An optional primary key value to load the row by, or an array of fields to match.  If not
     *                         set the instance property value is used.
     * @param   boolean $reset True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
     *
     * @link    http://docs.joomla.org/JTable/load
     * @since   11.1
     */
    public function load($keys = null, $reset = true)
    {
        $result = parent::load($keys, $reset);

        $this->prepareData();
        $this->prepareCategoryData();

        return $result;
    }

    /**
     * Prepare some specific data.
     */
    public function prepareData()
    {
        $this->slug    = $this->id . ':' . $this->alias;
        $this->catslug = $this->getCatSlug();

        // Calculate funded percent
        if (!$this->goal) {
            $this->fundedPercent = 0;
        } else {
            $this->fundedPercent = (string)Prism\Utilities\MathHelper::calculatePercentage($this->funded, $this->goal, 0);
        }

        // Calculate end date
        if ($this->funding_days > 0) {
            $fundingStartDateValidator = new Prism\Validator\Date($this->funding_start);
            if (!$fundingStartDateValidator->isValid()) {
                $this->funding_end = '1000-01-01';
            } else {
                $fundingStartDate  = new Crowdfunding\Date($this->funding_start);
                $fundingEndDate    = $fundingStartDate->calculateEndDate($this->funding_days);
                $this->funding_end = $fundingEndDate->format(Prism\Constants::DATE_FORMAT_SQL_DATETIME);
            }
        }

        // Calculate days left
        $today          = new Crowdfunding\Date();
        $this->daysLeft = $today->calculateDaysLeft($this->funding_days, $this->funding_start, $this->funding_end);
    }

    /**
     * Return percentage of funded amount.
     *
     * @return int
     */
    public function getFundedPercent()
    {
        return $this->fundedPercent;
    }

    public function setFundedPercent($percent)
    {
        $this->fundedPercent = $percent;
    }

    /**
     * Return the days that left to the end of campaign.
     *
     * @return int $daysLeft
     */
    public function getDaysLeft()
    {
        return $this->daysLeft;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getCatSlug()
    {
        return $this->catslug;
    }

    public function prepareCategoryData()
    {
        if ((int)$this->catid > 0) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select(
                    'a.access AS category_access, ' .
                    $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS catslug'
                )
                ->from($db->quoteName('#__categories', 'a'))
                ->where('a.id = ' . (int)$this->catid);

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();

            if ($result !== null) {
                $this->catslug         = $result->catslug ?: (int)$this->catid;
                $this->category_access = $result->category_access ?: 0;
            }
        }
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since   11.1
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_crowdfunding.item.' . (int)$this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   11.1
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        // We will retrieve the parent-asset from the Asset-table
        $assetParent = JTable::getInstance('Asset');
        /** @var JTableAsset $assetParentId */

        // Default: if no asset-parent can be found we take the global asset
        $assetParentId = $assetParent->getRootId();

        // Find the parent-asset
        if ($this->catid && !empty($this->catid)) {
            // The item has a category as asset-parent
            $assetParent->loadByName('com_crowdfunding.category.' . (int)$this->catid);
        } else {
            // The item has the component as asset-parent
            $assetParent->loadByName('com_crowdfunding');
        }
        // Return the found asset-parent-id
        if ($assetParent->id) {
            $assetParentId = $assetParent->id;
        }

        return $assetParentId;
    }
}
