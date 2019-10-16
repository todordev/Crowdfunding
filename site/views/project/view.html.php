<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Prism\Money\Money;
use Crowdfunding\Facade\Joomla as JoomlaFacade;

// no direct access
defined('_JEXEC') or die;

class CrowdfundingViewProject extends JViewLegacy
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

    protected $form;
    protected $item;
    protected $items;

    protected $moneyFormatter;
    protected $currency;
    protected $userId;
    protected $disabledButton;
    protected $layout;
    protected $debugMode;
    protected $rewardsEnabled = true;
    protected $rewardsEnabledViaType = true;
    protected $article;
    protected $pathwayName;
    protected $numberOfTypes;
    protected $isNew;
    protected $imageFolder;
    protected $minAmount;
    protected $maxAmount;
    protected $minDays;
    protected $maxDays;
    protected $checkedDate;
    protected $checkedDays;
    protected $pitchImage;
    protected $pWidth;
    protected $pHeight;
    protected $imageSmall;
    protected $fundingDuration;
    protected $dateFormat;
    protected $dateFormatCalendar;
    protected $rewardsImagesEnabled;
    protected $rewardsImagesUri;
    protected $projectId;
    protected $images;
    protected $rewards;
    protected $types;
    protected $goal;
    protected $raised;
    protected $showStatistics = false;
    protected $options = array();

    protected $imageWidth;
    protected $imageHeight;
    protected $titleLength;
    protected $descriptionLength;
    protected $returnUrl;
    protected $isImageExists = false;
    protected $imagePath;
    protected $displayRemoveButton = 'none';
    protected $imageStyleDisplay = 'none';

    protected $wizardType;
    protected $layoutData = array();
    protected $sixSteps = false;
    protected $statistics = array();
    protected $isValidStartingDate;

    // Variables used on step Basic.
    protected $maxFilesize;
    protected $authorised = false;
    protected $itemId;

    /**
     * @var CrowdfundingModelProjectItem
     */
    protected $model;

    protected $option;

    protected $pageclass_sfx;

    /**
     * @var JApplicationSite
     */
    protected $app;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->getCmd('option');

        $user         = JFactory::getUser();

        // Show Intro Article.
        $this->userId = (int)$user->get('id');
        if (!$this->userId) {
            $this->setLayout('intro');
            $this->prepareIntro();
            $this->prepareDocument();
            parent::display($tpl);
            return;
        }

        if (!$user->authorise('core.create', 'com_crowdfunding')) {
            $loginUrl  = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode(CrowdfundingHelperRoute::getFormRoute()), false);

            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_NO_PERMISSIONS_TO_DO_ACTION'), 'notice');
            $this->app->redirect($loginUrl);
            return;
        }

        $this->layout  = $this->getLayout();

        // Get the model.
        $this->model = JModelLegacy::getInstance('ProjectItem', 'CrowdfundingModel', ['ignore_request' => false]);

        // Get state
        $this->state  = $this->model->getState();
        /** @var  $this->state Joomla\Registry\Registry */

        // Get component params.
        $this->params  = $this->state->get('params');

        $this->itemId  = $this->app->input->getUint('id');

        $this->item    = $this->model->getItem($this->itemId, $this->userId);
        if ($this->item !== null && $this->item->id > 0) { // Check if it is a new record.
            $this->authorised = $this->item->params->get('access-edit');
        } else {
            $this->authorised = $user->authorise('core.create', 'com_crowdfunding');
        }

        // Redirect the user to login form if he is not authorized.
        if (!$this->authorised) {
            $loginUrl  = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode(CrowdfundingHelperRoute::getFormRoute()), false);
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_NO_PERMISSIONS_TO_DO_ACTION'), 'notice');
            $this->app->redirect($loginUrl);
            return;
        }

        // Get date format.
        $this->dateFormat     = $this->params->get('date_format_views', JText::_('DATE_FORMAT_LC3'));

        $this->rewardsEnabled = (bool)$this->params->get('rewards_enabled', Prism\Constants::ENABLED);
        $this->disabledButton = '';

        switch ($this->layout) {
            case 'funding':
                $this->prepareFunding();
                break;

            case 'story':
                $this->prepareStory();
                break;

            case 'rewards':
                $this->prepareRewards();
                break;

            case 'extras':
                $this->prepareExtras();
                break;

            case 'manager':
                $this->prepareManager();
                break;

            default: // Basic data for project
                $this->prepareBasic();
                break;
        }

        // Get wizard type
        $this->wizardType     = $this->params->get('project_wizard_type', 'five_steps');
        $this->sixSteps       = (strcmp('six_steps', $this->wizardType) === 0);

        $this->layoutData = array(
            'layout'  => $this->layout,
            'item_id' => ($this->item !== null and (int)$this->item->id > 0) ? (int)$this->item->id : 0,
            'rewards_enabled' => $this->rewardsEnabled
        );

        $this->prepareDebugMode();
        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Check the system for debug mode
     */
    protected function prepareDebugMode()
    {
        // Check for maintenance (debug) state
        $params          = $this->state->get('params');
        $this->debugMode = $params->get('debug_project_adding_disabled', 0);
        if ($this->debugMode) {
            $msg = Joomla\String\StringHelper::trim($params->get('debug_disabled_functionality_msg'));
            if (!$msg) {
                $msg = JText::_('COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG');
            }
            $this->app->enqueueMessage($msg, 'notice');

            $this->disabledButton = 'disabled="disabled"';
        }
    }

    /**
     * Check the system for debug mode.
     */
    protected function prepareProjectType()
    {
        $this->rewardsEnabledViaType = true;

        if ($this->item->type_id > 0) {
            $type = new Crowdfunding\Type(JFactory::getDbo());
            $type->load($this->item->type_id);

            if ($type->getId() && !$type->isRewardsEnabled()) {
                $this->rewardsEnabledViaType = false;
                $this->disabledButton = 'disabled="disabled"';
            }
        }
    }

    protected function prepareIntro()
    {
        $model = JModelLegacy::getInstance('Intro', 'CrowdfundingModel', ['ignore_request' => true]);
        /** @var CrowdfundingModelIntro $model */

        $this->params      = JComponentHelper::getParams('com_crowdfunding');

        $articleId         = $this->params->get('project_intro_article', 0);
        $this->article     = $model->getItem($articleId);

        $this->pathwayName = JText::_('COM_CROWDFUNDING_START_PROJECT_BREADCRUMB');
    }

    protected function prepareBasic()
    {
        $model = JModelLegacy::getInstance('Projectbasic', 'CrowdfundingModel', ['ignore_request' => false]);
        /** @var CrowdfundingModelProjectbasic $model */

        // Set a flag that describes the item as new.
        $this->isNew = false;
        if (!(int)$this->item->id) {
            $this->isNew = true;
        }

        $this->form          = $model->getForm();

        // Get types
        $this->types         = Crowdfunding\Types::getInstance(JFactory::getDbo());
        $this->numberOfTypes = count($this->types);

        // Prepare images
        $this->imageFolder   = $this->params->get('images_directory', 'images/crowdfunding');

        if (!$this->item->image) {
            $this->imagePath     = 'media/com_crowdfunding/images/no_image.png';
            $this->displayRemoveButton = 'none';
        } else {
            $this->imagePath     = $this->imageFolder.'/'.$this->item->image;
            $this->displayRemoveButton = 'inline-block';
        }

        $mediaParams        = JComponentHelper::getParams('com_media');

        $this->imageWidth   = $this->params->get('image_width', 200);
        $this->imageHeight  = $this->params->get('image_height', 200);
        $this->maxFilesize  = Prism\Utilities\FileHelper::getMaximumFileSize($mediaParams->get('upload_maxsize', 10), 'MB');

        $this->pathwayName = JText::_('COM_CROWDFUNDING_STEP_BASIC');

        // Remove the temporary pictures if they exists.
        $model->removeTemporaryImage($this->app);
        $model->removeCroppedImages($this->app);
    }

    protected function prepareFunding()
    {
        // Check for valid project.
        if (!$this->item->id) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getFormRoute()));
            return;
        }

        $model = JModelLegacy::getInstance('Funding', 'CrowdfundingModel', ['ignore_request' => false]);
        /** @var CrowdfundingModelFunding $model */

        $this->form = $model->getForm();

        // Get money formatter.
        $this->currency        = JoomlaFacade::getCurrency();
        $this->moneyFormatter  = JoomlaFacade::getMoneyFormatter();

        // Set minimum values - days, amount,...
        $this->minAmount = (float)$this->params->get('project_amount_minimum');
        $this->maxAmount = (float)$this->params->get('project_amount_maximum');

        $this->minDays = (int)$this->params->get('project_days_minimum', 30);
        $this->maxDays = (int)$this->params->get('project_days_maximum');

        // Prepare funding duration type
        $this->prepareFundingDurationType();

        $startingDateValidator     = new Prism\Validator\Date($this->item->funding_start);
        $this->isValidStartingDate = $startingDateValidator->isValid();

        $this->pathwayName = JText::_('COM_CROWDFUNDING_STEP_FUNDING');
    }

    protected function prepareFundingDurationType()
    {
        $this->fundingDuration = $this->params->get('project_funding_duration');

        switch ($this->fundingDuration) {
            case 'days': // Only days type is enabled
                $this->checkedDays = 'checked="checked"';
                break;

            case 'date': // Only date type is enabled
                $this->checkedDate = 'checked="checked"';
                break;

            default: // Both ( days and date ) types are enabled
                $this->checkedDays = 0;
                $this->checkedDate = '';

                $dateValidator = new Prism\Validator\Date($this->item->funding_end);

                if ($this->item->funding_days > 0) {
                    $this->checkedDays = 'checked="checked"';
                    $this->checkedDate = '';
                } elseif ($dateValidator->isValid()) {
                    $this->checkedDays = '';
                    $this->checkedDate = 'checked="checked"';
                }

                // If missing both, select days.
                if (!$this->checkedDays && !$this->checkedDate) {
                    $this->checkedDays = 'checked="checked"';
                }
                break;
        }
    }

    protected function prepareStory()
    {
        // Check for valid project.
        if (!$this->item->id) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getFormRoute()));
            return;
        }

        $model = JModelLegacy::getInstance('Story', 'CrowdfundingModel', ['ignore_request' => false]);
        /** @var CrowdfundingModelStory $model */

        $this->form = $model->getForm();

        $this->imageFolder = $this->params->get('images_directory', 'images/crowdfunding');
        if ($this->item->pitch_image) {
            $this->pitchImage          = $this->imageFolder . '/' . $this->item->pitch_image;
            $this->displayRemoveButton = 'inline-block';
            $this->imageStyleDisplay   = 'block';
        }

        $this->pWidth  = $this->params->get('pitch_image_width', 600);
        $this->pHeight = $this->params->get('pitch_image_height', 400);

        $this->pathwayName = JText::_('COM_CROWDFUNDING_STEP_STORY');

        // Remove the temporary pictures if they exists.
        $model->removeTemporaryImage($this->app);
    }

    protected function prepareRewards()
    {
        // Check for valid project.
        if (!$this->item->id) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getFormRoute()));
            return;
        }

        $model    = JModelLegacy::getInstance('Rewards', 'CrowdfundingModel', $config = array('ignore_request' => false));
        /** @var CrowdfundingModelRewards $model */

        // Check if rewards are enabled.
        if (!$this->rewardsEnabled) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_REWARDS_DISABLED'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getFormRoute($this->itemId, 'manager'), false));
            return;
        }

        $this->items     = $model->getItems($this->itemId);

        // Get money formatter.
        $this->currency         = JoomlaFacade::getCurrency();
        $this->moneyFormatter   = JoomlaFacade::getMoneyFormatter();

        // Get calendar date format.
        $this->dateFormatCalendar = $this->params->get('date_format_calendar', JText::_('DATE_FORMAT_LC4'));

        $language    = JFactory::getLanguage();
        $languageTag = $language->getTag();

        $js = '
            // Rewards calendar date format.
            var projectWizard = {
                dateFormat: "' . Prism\Utilities\DateHelper::formatCalendarDate($this->dateFormatCalendar) . '",
                locale: "'. substr($languageTag, 0, 2) .'"
            };
        ';
        $this->document->addScriptDeclaration($js);

        // Prepare rewards images.
        $this->rewardsImagesEnabled = (bool)$this->params->get('rewards_images', 0);
        $this->rewardsImagesUri     = CrowdfundingHelper::getImagesFolderUri($this->userId);

        $this->options['column_left']  = (!$this->rewardsImagesEnabled or count($this->items) === 0) ? 12 : 8;
        $this->options['column_right'] = (!$this->rewardsImagesEnabled or count($this->items) === 0) ? 0 : 4;
        
        $this->prepareProjectType();

        $this->pathwayName = JText::_('COM_CROWDFUNDING_STEP_REWARDS');
    }

    protected function prepareManager()
    {
        // Check for valid project.
        if (!$this->item->id) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getFormRoute()));
            return;
        }

        $this->imageWidth        = $this->params->get('image_width', 200);
        $this->imageHeight       = $this->params->get('image_height', 200);
        $this->titleLength       = $this->params->get('discover_title_length', 0);
        $this->descriptionLength = $this->params->get('discover_description_length', 0);

        // Get the folder with images
        $this->imageFolder       = $this->params->get('images_directory', 'images/crowdfunding');

        // Filter the URL.
        $uri             = JUri::getInstance();

        $filter          = JFilterInput::getInstance();
        $this->returnUrl = $filter->clean($uri->toString());

        // Get money formatter.
        $this->currency         = JoomlaFacade::getCurrency();
        $this->moneyFormatter   = JoomlaFacade::getMoneyFormatter();

        $statistics = new Crowdfunding\Statistics\Project(JFactory::getDbo(), $this->item->id);
        $this->statistics = array(
            'updates'  => $statistics->getUpdatesNumber(),
            'comments' => $statistics->getCommentsNumber(),
            'funders'  => $statistics->getTransactionsNumber(),
        );

        $this->goal    = $this->moneyFormatter->formatCurrency(new Money($this->item->goal, $this->currency));
        $this->raised  = $this->moneyFormatter->formatCurrency(new Money($this->item->funded, $this->currency));

        $campaignValidator = new Crowdfunding\Validator\Project\LaunchedCampaign($this->item->funding_start, $this->item->funding_end);
        if ($campaignValidator->isValid()) {
            $this->showStatistics = true;
        }

        // Get rewards
        $this->rewards = new Crowdfunding\Rewards(JFactory::getDbo());
        $this->rewards->load(array('project_id' => $this->item->id));

        $this->pathwayName = JText::_('COM_CROWDFUNDING_STEP_MANAGER');
    }

    protected function prepareExtras()
    {
        // Check for valid project.
        if (!$this->item->id) {
            $this->app->enqueueMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), 'notice');
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getFormRoute()));
            return;
        }

        $this->pathwayName = JText::_('COM_CROWDFUNDING_STEP_EXTRAS');

        // Events
        JPluginHelper::importPlugin('crowdfunding');
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onExtrasDisplay', array('com_crowdfunding.project.extras', &$this->item, &$this->params));

        $this->item->event                   = new stdClass();
        $this->item->event->onExtrasDisplay = trim(implode("\n", $results));
    }

    protected function prepareDocument()
    {
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        $menus = $this->app->getMenu();

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $menu->title);
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_RAISE_DEFAULT_PAGE_TITLE'));
        }

        // Prepare page title
        $title = $menu->title;
        if (!$title) {
            $title = $this->app->get('sitename');

        // Set site name if it is necessary ( the option 'sitename' = 1 )
        } elseif ($this->app->get('sitename_pagetitles', 0)) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);

        // Item title to the browser title.
        } else {
            if ($this->item !== null) {
                $title .= ' | ' . $this->escape($this->item->title);
            }
        }

        $this->document->setTitle($title);

        // Meta Description
        $this->document->setDescription($this->params->get('menu-meta_description'));

        // Meta keywords
        $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));

        // Add current layout into breadcrumbs.
        $pathway = $this->app->getPathway();
        $pathway->addItem($this->pathwayName);

        JHtml::_('jquery.framework');

        // Scripts
        if ((int)$this->userId > 0) {
            JHtml::_('behavior.core');
            JHtml::_('behavior.keepalive');

            if ((bool)$this->params->get('enable_chosen', 1)) {
                JHtml::_('formbehavior.chosen', '.cf-advanced-select');
            }
        }

        $version = new Crowdfunding\Version();

        switch ($this->layout) {
            case 'rewards':
                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_REWARD');
                JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE');
                JText::script('COM_CROWDFUNDING_PICK_IMAGE');

                // Scripts

                if ($this->params->get('rewards_images', 0)) {
                    JHtml::_('Prism.ui.bootstrap3Fileinput');
                }

                JHtml::_('Prism.ui.pnotify');
                JHtml::_('Prism.ui.joomlaHelper');
                $this->document->addScript('media/' . $this->option . '/js/site/project_rewards.js?v='.$version->getShortVersion());

                break;

            case 'funding':
                JHtml::_('Prism.ui.parsley');
                JHtml::_('Prism.ui.bootstrap3Datepicker');

                $this->document->addScript('media/' . $this->option . '/js/site/project_funding.js?v='.$version->getShortVersion());

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_THIS_VALUE_IS_REQUIRED');

                break;

            case 'story':
                JHtml::_('Prism.ui.remodal');
                JHtml::_('Prism.ui.cropper');
                JHtml::_('Prism.ui.fileupload');
                JHtml::_('Prism.ui.pnotify');
                JHtml::_('Prism.ui.joomlaHelper');

                // Include translation of the confirmation question for image removing.
                JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE');
                JText::script('COM_CROWDFUNDING_PICK_IMAGE');
                JText::script('COM_CROWDFUNDING_REMOVE');

                // Provide image size.
                $js = '
                    var projectWizardBasic = {
                        imageWidth: '.$this->params->get('pitch_image_width', 600).',
                        imageHeight: '.$this->params->get('pitch_image_height', 400).',
                        aspectRatio: ' . $this->params->get('image_aspect_ratio', '""') . '
                    };
                ';

                $this->document->addScriptDeclaration($js);

                $this->document->addScript('media/' . $this->option . '/js/site/project_story.js?v='.$version->getShortVersion());
                break;

            case 'manager':
                JHtml::_('Prism.ui.chartjs');
                $this->document->addScript('media/' . $this->option . '/js/site/project_manager.js?v='.$version->getShortVersion());

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_QUESTION_LAUNCH_PROJECT');
                JText::script('COM_CROWDFUNDING_QUESTION_STOP_PROJECT');
                JText::script('COM_CROWDFUNDING_DAILY_FUNDS');

                $js = '
                    var crowdfundingOptions = {
                        projectId: ' . $this->item->id .',
                        token: "'.JSession::getFormToken().'"
                    }';

                $this->document->addScriptDeclaration($js);

                break;

            case 'extras':
                JHtml::_('Prism.ui.serializeJson');
                break;

            default: // Basic
                if ($this->userId) {
                    JHtml::_('Prism.ui.bootstrapMaxLength');
                    JHtml::_('Prism.ui.jQueryAutoComplete');
                    JHtml::_('Prism.ui.remodal');
                    JHtml::_('Prism.ui.parsley');
                    JHtml::_('Prism.ui.cropper');
                    JHtml::_('Prism.ui.fileupload');
                    JHtml::_('Prism.ui.pnotify');
                    JHtml::_('Prism.ui.joomlaHelper');

                    $this->document->addScript('media/' . $this->option . '/js/site/project_basic.js?v='.$version->getShortVersion());

                    // Load language string in JavaScript
                    JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE');

                    $js = '
                    var crowdfundingOptions = {
                        imageWidth:  ' . $this->params->get('image_width', 200) . ',
                        imageHeight: ' . $this->params->get('image_width', 200) . ',
                        aspectRatio: ' . $this->params->get('image_aspect_ratio', '""') . '
                    }';

                    $this->document->addScriptDeclaration($js);
                }
                break;
        }
    }
}
