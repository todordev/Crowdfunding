<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Crowdfunding\Facade\Joomla as JoomlaFacade;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewProjects extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $items;

    protected $currency;
    protected $moneyFormatter;

    protected $dateFormat;

    protected $layoutData;

    protected $option;

    protected $pageclass_sfx;

    /**
     * @var JApplicationSite
     */
    protected $app;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');

        $userId = JFactory::getUser()->get('id');
        if (!$userId) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), 'notice');
            $this->app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
            return;
        }

        $this->state  = $this->get('State');
        $this->params = $this->state->get('params');

        $mapper     = new \Crowdfunding\Project\Mapper(new \Crowdfunding\Project\Gateway\JoomlaGateway(JFactory::getDbo()));
        $repository = new \Crowdfunding\Project\Repository($mapper);

        $databaseRequest = new \Prism\Database\Request\Request();
        $databaseRequest
            ->addConditions(['user_id' => $userId])
            ->addOrderCondition(new \Prism\Database\Request\Order(['column' => 'funding_end', 'direction' => 'DESC']));

        $this->items = $repository->fetchCollection($databaseRequest);

        // Fetch projects.
        $this->layoutData                 = new stdClass();
        $this->layoutData->dateFormat     = $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3'));
        $this->layoutData->item           = null;
        $this->layoutData->currency       = null;
        $this->layoutData->moneyFormatter = null;
        $this->layoutData->mediaFolder    = $this->params->get('images_directory', 'images/crowdfunding');
        $this->layoutData->imageWidth     = $this->params->get('image_small_width', 100);
        $this->layoutData->imageHeight    = $this->params->get('image_small_height', 100);

        // Create money formatter.
        if (count($this->items) > 0) {
            $this->layoutData->currency       = JoomlaFacade::getCurrency();
            $this->layoutData->moneyFormatter = JoomlaFacade::getMoneyFormatter();
        }

        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareDocument()
    {
        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        // Meta Description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        // Meta keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        // Scripts
        JHtml::_('behavior.core');
        JHtml::_('bootstrap.tooltip');
    }

    private function preparePageHeading()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $this->app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_PROJECTS_DEFAULT_PAGE_TITLE'));
        }
    }

    private function preparePageTitle()
    {
        // Prepare page title
        $title = $this->params->get('page_title', '');

        // Add title before or after Site Name
        if (!$title) {
            $title = $this->app->get('sitename');
        } elseif ((int)$this->app->get('sitename_pagetitles', 0) === 1) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        } elseif ((int)$this->app->get('sitename_pagetitles', 0) === 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
        }

        $this->document->setTitle($title);
    }
}
