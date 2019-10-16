<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding Social Share Plugin
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgContentCrowdfundingSocialShare extends JPlugin
{
    protected static $loaded = array();

    private $locale = 'en_US';
    private $fbLocale = 'en_US';
    private $plusLocale = 'en';
    private $gshareLocale = 'en';
    private $twitterLocale = 'en';
    private $currentView = '';
    private $currentTask = '';
    private $currentOption = '';

    public function onContentAfterDisplayMedia($context, &$article, &$params)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        if (strcmp('com_crowdfunding.details', $context) !== 0) {
            return null;
        }

        // Load language
        $this->loadLanguage();

        // Get request data
        $this->currentOption = $app->input->getCmd('option');
        $this->currentView   = $app->input->getCmd('view');
        $this->currentTask   = $app->input->getCmd('task');

        // Get locale code automatically
        if ($this->params->get('dynamicLocale', 0)) {
            $lang         = JFactory::getLanguage();
            $locale       = $lang->getTag();
            $this->locale = str_replace('-', '_', $locale);
        }

        if ($this->params->get('loadCss')) {
            $doc->addStyleSheet(JUri::root() . 'plugins/content/crowdfundingsocialshare/style.css');
        }

        // Generate content
        if ($this->params->get('display_title_details_view', 0)) {
            $content = '<h2>' . JText::_('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_SHARE_PROJECT') . '</h2>';
        } else {
            $content = '<div class="clearfix"></div>';
        }
        $content .= '<div class="crowdf-share btm-10px">';
        $content .= $this->getContent($article);
        $content .= '</div>';

        return $content;
    }

    public function onContentAfterDisplay($context, &$article, &$params)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        if (strcmp('com_crowdfunding.payment.share', $context) !== 0) {
            return null;
        }

        // Load language
        $this->loadLanguage();

        // Get request data
        $this->currentOption = $app->input->getCmd('option');
        $this->currentView   = $app->input->getCmd('view');
        $this->currentTask   = $app->input->getCmd('task');

        // Get locale code automatically
        if ($this->params->get('dynamicLocale', 0)) {
            $lang         = JFactory::getLanguage();
            $locale       = $lang->getTag();
            $this->locale = str_replace('-', '_', $locale);
        }

        if ($this->params->get('loadCss')) {
            $doc->addStyleSheet(JUri::root() . 'plugins/content/crowdfundingsocialshare/style.css');
        }

        // Generate content
        $content = '<div class="panel panel-default">';
        if ($this->params->get('display_title_payment_view', 0)) {
            $content .= '<div class="panel-heading">';
            $content .= '<h3>' . JText::_('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_SHARE_PROJECT') . '</h3>';
            $content .= '</div>';
        }
        $content .= '<div class="panel-body">';
        $content .= $this->getContent($article);
        $content .= '</div>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Generate content
     *
     * @param   stdClass   $item   The item object.
     *
     * @return  string      Returns html code or empty string.
     */
    private function getContent(&$item)
    {
        $url   = $item->link;
        $title = $item->title;
        $image = $item->link_image;

        // Convert the url to short one
        if ($this->params->get('shortener_service')) {
            $url = $this->getShortUrl($url);
        }

        $html = '';

        $html .= $this->getFacebookLike($this->params, $url);

        $html .= $this->getTwitter($this->params, $url, $title);
        $html .= $this->getStumbpleUpon($this->params, $url);
        $html .= $this->getTumblr($this->params);
        $html .= $this->getPinterest($this->params, $url, $title, $image);
        $html .= $this->getReddit($this->params, $url, $title);
        $html .= $this->getLinkedIn($this->params, $url);

        $html .= $this->getGooglePlusOne($this->params, $url);
        $html .= $this->getGoogleShare($this->params, $url);

        // Get extra buttons
        $html .= $this->getExtraButtons($title, $url, $this->params);

        $html .= $this->getEmbeded($item, $this->params, $url);

        return $html;
    }

    /**
     * @param stdClass $item
     * @param Joomla\Registry\Registry $params
     * @param string $url
     *
     * @return string
     */
    private function getEmbeded($item, $params, $url)
    {
        $html = '';

        if (!$params->get('display_embed_link', 1) and !$params->get('display_embed_button', 1) and !$params->get('display_embed_email', 1)) {
            return $html;
        }

        $html = '<div class="clearfix"></div>';
        $html .= '<div class="crowdf-embeded">';
        if ($params->get('display_embed_link', 1)) {
            $html .= '<input type="text" name="share_url" value="' . html_entity_decode($url, ENT_COMPAT, 'UTF-8') . '" class="crowdf-embeded-input" />';
        }
        
        if ($params->get('display_embed_button', 1)) {
            $link = JRoute::_(CrowdfundingHelperRoute::getEmbedRoute($item->slug, $item->catslug), false);
            $html .= '<a href="' . $link . '" class="btn btn-default" role="button"><span class="fa fa-th-large"></span> ' . JText::_('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_EMBED') . '</a>';
        }
        
        if ($params->get('display_embed_email', 1)) {
            $link = JRoute::_(CrowdfundingHelperRoute::getFriendmailRoute($item->slug), false);
            $html .= '<a class="btn btn-default" href="' . $link . '" role="button"><span class="fa fa-envelope"></span> ' . JText::_('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_EMAIL') . '</a>';
        }
        
        if ($params->get('display_follow', 0)) {
            $userId     = JFactory::getUser()->get('id');
            $state      = Prism\Constants::UNFOLLOWED;
            $projectId  = (int)$item->id;

            $text = JText::_('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_FOLLOW');
            $class = ' btn-default';

            // Load scripts.
            JHtml::_('jquery.framework');
            $doc = JFactory::getDocument();
            $doc->addScript(JUri::root() . 'plugins/content/crowdfundingsocialshare/script.js');

            if ($userId) {
                $user = new Crowdfunding\User\User(JFactory::getDbo());
                $user->setId($userId);
                $followed = $user->getFollowed();

                if (in_array($projectId, $followed, true)) {
                    $state = Prism\Constants::FOLLOWED;
                    $text  = JText::_('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_FOLLOWING');
                    $class = ' btn-primary';
                }

                JText::script('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_FOLLOW');
                JText::script('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_UNFOLLOW');
                JText::script('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_FOLLOWING');
            }

            $html .= '<a href="javascript: void(0);" class="btn '.$class.'" id="js-plgsocialshare-btn-follow" role="button" data-uid="'.(int)$userId.'" data-state="'.(int)$state.'" data-pid="'.$projectId.'">
            <span class="fa fa-heart"></span>
            <span id="js-plgsocialshare-btn-text">' . $text . '</span></a>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * A method that make a long url to short url
     *
     * @param string $link
     *
     * @return string
     */
    private function getShortUrl($link)
    {
        $options = array(
            'login'   => $this->params->get('shortener_login'),
            'api_key' => $this->params->get('shortener_api_key'),
            'service' => $this->params->get('shortener_service'),
        );

        $shortLink = '';

        try {
            $shortUrl  = new Prism\Utilities\ShortUrl($link, $options);
            $shortLink = $shortUrl->getUrl();

            // Get original link
            if (!$shortLink) {
                $shortLink = $link;
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::WARNING, 'com_crowdfunding');

            // Get original link
            if (!$shortLink) {
                $shortLink = $link;
            }
        }

        return $shortLink;
    }

    /**
     * Generate a code for the extra buttons.
     * Is also replace indicators {URL} and {TITLE} with that of the article.
     *
     * @param string $title  Article Title
     * @param string $url    Article URL
     * @param Joomla\Registry\Registry  $params Plugin parameters
     *
     * @return string
     */
    private function getExtraButtons($title, $url, $params)
    {
        $html = '';
        // Extra buttons
        for ($i = 1; $i < 6; $i++) {
            $btnName     = 'ebuttons' . $i;
            $extraButton = $params->get($btnName, '');
            if (JString::strlen($extraButton) > 0) {
                $extraButton = str_replace('{URL}', $url, $extraButton);
                $extraButton = str_replace('{TITLE}', $title, $extraButton);
                $html .= $extraButton;
            }
        }

        return $html;
    }

    /**
     * @param Joomla\Registry\Registry $params
     * @param string $url
     * @param string $title
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    private function getTwitter($params, $url, $title)
    {
        $html = '';
        if ($params->get('twitterButton')) {
            $title = htmlentities($title, ENT_QUOTES, 'UTF-8');

            // Get locale code
            if (!$params->get('dynamicLocale')) {
                $this->twitterLocale = $params->get('twitterLanguage', 'en');
            } else {
                $locales             = $this->getButtonsLocales($this->locale);
                $this->twitterLocale = Joomla\Utilities\ArrayHelper::getValue($locales, 'twitter', 'en');
            }

            $html = '
             	<div class="crowdf-share-tw">
                	<a href="https://twitter.com/share" class="twitter-share-button" data-url="' . rawurldecode(html_entity_decode($url, ENT_COMPAT, 'UTF-8')) . '"
                	data-text="' . $title . '" data-via="' . $params->get('twitterName') . '" data-lang="' . $this->twitterLocale . '"
                	data-size="' . $params->get('twitterSize') . '" data-related="' . $params->get('twitterRecommend') . '" data-hashtags="' . $params->get('twitterHashtag') . '"
                	data-count="' . $params->get('twitterCounter') . '">Tweet</a>';

            if ($params->get('load_twitter_library', 1)) {
                $html .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @param Joomla\Registry\Registry  $params
     * @param string $url
     *
     * @return string
     */
    private function getGooglePlusOne($params, $url)
    {
        $html = '';
        if ($params->get('plusButton')) {
            // Get locale code
            if (!$params->get('dynamicLocale')) {
                $this->plusLocale = $params->get('plusLocale', 'en');
            } else {
                $locales          = $this->getButtonsLocales($this->locale);
                $this->plusLocale = Joomla\Utilities\ArrayHelper::getValue($locales, 'google', 'en');
            }

            $html .= '<div class="crowdf-share-gone">';

            $annotation = '';
            if ($params->get('plusAnnotation')) {
                $annotation = ' data-annotation="' . $params->get('plusAnnotation') . '"';
            }

            $html .= '<div class="g-plusone" data-size="' . $params->get('plusType') . '" ' . $annotation . ' data-href="' . $url . '"></div>';

            // Load the JavaScript asynchronous
            if ($params->get('loadGoogleJsLib') and !array_key_exists('google', self::$loaded)) {
                $html .= '
<script src="https://apis.google.com/js/platform.js" async defer>
  {lang: "'.$this->plusLocale.'"}
</script>';
                self::$loaded['google'] = true;
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @param Joomla\Registry\Registry $params
     * @param string $url
     *
     * @return string
     */
    private function getFacebookLike($params, $url)
    {
        $html = '';
        if ($params->get('facebookLikeButton')) {
            // Get locale code
            if (!$params->get('dynamicLocale')) {
                $this->fbLocale = $params->get('fbLocale', 'en_US');
            } else {
                $locales        = $this->getButtonsLocales($this->locale);
                $this->fbLocale = Joomla\Utilities\ArrayHelper::getValue($locales, 'facebook', 'en_US');
            }

            // Faces
            $faces = (!$params->get('facebookLikeFaces')) ? 'false' : 'true';

            // Layout Styles
            $layout = $params->get('facebookLikeType', 'button_count');

            // Generate code
            $html = '<div class="crowdf-share-fbl">';

            if ($params->get('facebookRootDiv', 1)) {
                $html .= '<div id="fb-root"></div>';
            }

            if ($params->get('facebookLoadJsLib', 1)) {
                $appId = '';
                if ($params->get('facebookLikeAppId')) {
                    $appId = '&amp;appId=' . $params->get('facebookLikeAppId');
                }

                $html .= '
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/' . $this->fbLocale . '/sdk.js#xfbml=1&version=v2.5' . $appId . '";
  fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));</script>';
            }

            $html .= '
            <div
            class="fb-like"
            data-href="' . rawurldecode(html_entity_decode($url, ENT_COMPAT, 'UTF-8')) . '"
            data-share="' . $params->get('facebookLikeShare', 0) . '"
            data-layout="' . $layout . '"
            data-width="' . $params->get('facebookLikeWidth', '450') . '"
            data-show-faces="' . $faces . '"
            data-colorscheme="' . $params->get('facebookLikeColor', 'light') . '"
            data-action="' . $params->get('facebookLikeAction', 'like') . '"';

            if ($params->get('facebookLikeFont')) {
                $html .= ' data-font="' . $params->get('facebookLikeFont') . '" ';
            }

            if ($params->get('facebookKidDirectedSite')) {
                $html .= ' data-kid-directed-site="true"';
            }

            $html .= '></div>';

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @param Joomla\Registry\Registry $params
     * @param string $url
     *
     * @return string
     */
    private function getLinkedIn($params, $url)
    {
        $html = '';
        if ($params->get('linkedInButton')) {
            // Get locale code
            if (!$params->get('dynamicLocale')) {
                $locale  = $params->get('linkedInLocale', 'en_US');
            } else {
                $locale  = $this->locale;
            }

            $html = '<div class="crowdf-share-lin">';

            if ($params->get('load_linkedin_library', 1)) {
                $html .= '<script src="//platform.linkedin.com/in.js">lang: '.$locale.'</script>';
            }

            $html .= '<script type="IN/Share" data-url="' . rawurldecode(html_entity_decode($url, ENT_COMPAT, 'UTF-8')) . '" data-counter="' . $params->get('linkedInType', 'right') . '"></script>
            </div>
            ';
        }

        return $html;
    }

    /**
     * @param Joomla\Registry\Registry $params
     * @param string $url
     * @param string $title
     *
     * @return string
     */
    private function getReddit($params, $url, $title)
    {
        $html = '';
        if ($params->get('redditButton')) {
            $url   = rawurldecode(html_entity_decode($url, ENT_COMPAT, 'UTF-8'));
            $title = htmlentities($title, ENT_QUOTES, 'UTF-8');

            $html .= '<div class="crowdf-share-reddit">';
            $redditType = $params->get('redditType');

            $jsButtons = range(1, 9);

            if (in_array($redditType, $jsButtons, true)) {
                $html .= '<script>
  reddit_url = "' . $url . '";
  reddit_title = "' . $title . '";
  reddit_bgcolor = "' . $params->get('redditBgColor') . '";
  reddit_bordercolor = "' . $params->get('redditBorderColor') . '";
  reddit_newwindow = "' . $params->get('redditNewTab') . '";
</script>';
            }
            
            $redditLink = '<a href="http://www.reddit.com/submit" onclick="window.location = \'http://www.reddit.com/submit?url=' . $url . '\'; return false"> ';
            $redditText = JText::_('PLG_CONTENT_CROWDFUNDINGSOCIALSHARE_SUBMIT_REDDIT');
            
            switch ($redditType) {
                case 1:
                    $html .= '<script src="//www.reddit.com/static/button/button1.js"></script>';
                    break;
                case 2:
                    $html .= '<script src="//www.reddit.com/static/button/button2.js"></script>';
                    break;
                case 3:
                    $html .= '<script src="//www.reddit.com/static/button/button3.js"></script>';
                    break;
                case 4:
                    $html .= '<script src="//www.reddit.com/buttonlite.js?i=0"></script>';
                    break;
                case 5:
                    $html .= '<script src="//www.reddit.com/buttonlite.js?i=1"></script>';
                    break;
                case 6:
                    $html .= '<script src="//www.reddit.com/buttonlite.js?i=2"></script>';
                    break;
                case 7:
                    $html .= '<script src="//www.reddit.com/buttonlite.js?i=3"></script>';
                    break;
                case 8:
                    $html .= '<script src="//www.reddit.com/buttonlite.js?i=4"></script>';
                    break;
                case 9:
                    $html .= '<script src="//www.reddit.com/buttonlite.js?i=5"></script>';
                    break;
                case 10:
                    $html .= $redditLink. '<img src="//www.reddit.com/static/spreddit6.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 11:
                    $html .= $redditLink. '<img src="//www.reddit.com/static/spreddit1.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 12:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit2.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 13:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit3.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 14:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit4.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 15:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit5.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 16:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit8.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 17:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit9.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 18:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit10.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 19:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit11.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 20:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit12.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 21:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit13.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
                case 22:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit14.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;

                default:
                    $html .= $redditLink .'<img src="//www.reddit.com/static/spreddit7.gif" alt="' . $redditText . '" border="0" /> </a>';
                    break;
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @param Joomla\Registry\Registry $params
     *
     * @return string
     */
    private function getTumblr($params)
    {
        $html = '';
        if ($params->get('tumblrButton')) {
            $html .= '<div class="crowdf-share-tbr">';

            if ($params->get('loadTumblrJsLib')) {
                $html .= '<script>!function(d,s,id){var js,ajs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://secure.assets.tumblr.com/share-button.js";ajs.parentNode.insertBefore(js,ajs);}}(document, "script", "tumblr-js");</script>';
            }

            $html .= '<a class="tumblr-share-button" data-color="'.$params->get('tumblr_color', 'blue').'" data-notes="'.$params->get('tumblr_notes', 'right').'" href="https://embed.tumblr.com/share"></a>';

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @param Joomla\Registry\Registry  $params
     * @param string $url
     * @param string $title
     * @param string $image
     *
     * @return string
     */
    private function getPinterest($params, $url, $title, $image)
    {
        $html = '';
        if ($params->get('pinterestButton')) {
            $bubblePosition = $params->get('pinterestType', 'beside');

            $divClass = (strcmp('above', $bubblePosition) === 0) ? 'crowdf-share-pinterest-above' : 'crowdf-share-pinterest';

            $html .= '<div class="' . $divClass . '">';

            if (strcmp('one', $this->params->get('pinterestImages', 'one')) === 0) {
                $media = '';
                if (JString::strlen($image) > 0) {
                    $media = '&amp;media=' . rawurlencode($image);
                }

                $html .= '<a href="//pinterest.com/pin/create/button/?url=' . rawurldecode(html_entity_decode($url, ENT_COMPAT, 'UTF-8')) . $media . '&amp;description=' . rawurlencode($title) . '" data-pin-do="buttonPin" data-pin-config="' . $params->get('pinterestType', 'beside') . '"><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_20.png" /></a>';

            } else {
                $html .= '<a href="//pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" ><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>';
            }

            // Load the JS library
            if ($params->get('loadPinterestJsLib')) {
                $html .= '<script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>';
            }

            $html .= '</div>';
        }
        return $html;
    }

    /**
     * @param Joomla\Registry\Registry $params
     * @param string $url
     *
     * @return string
     */
    private function getStumbpleUpon($params, $url)
    {
        $html = '';
        if ($params->get('stumbleButton')) {
            $html = "
            <div class=\"crowdf-share-su\">
            <su:badge layout='" . $params->get('stumbleType', 1) . "' location='" . $url . "'></su:badge>
            </div>
            
            <script>
              (function() {
                var li = document.createElement('script'); li.type = 'text/javascript'; li.async = true;
                li.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + '//platform.stumbleupon.com/1/widgets.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(li, s);
              })();
            </script>
                ";
        }

        return $html;
    }

    /**
     * @param string locale
     *
     * @return array
     */
    private function getButtonsLocales($locale)
    {
        // Default locales
        $result = array(
            'twitter'  => 'en',
            'facebook' => 'en_US',
            'google'   => 'en'
        );

        // The locales map
        $locales = array(
            'en_US' => array(
                'twitter'  => 'en',
                'facebook' => 'en_US',
                'google'   => 'en'
            ),
            'en_GB' => array(
                'twitter'  => 'en',
                'facebook' => 'en_GB',
                'google'   => 'en_GB'
            ),
            'th_TH' => array(
                'twitter'  => 'th',
                'facebook' => 'th_TH',
                'google'   => 'th'
            ),
            'ms_MY' => array(
                'twitter'  => 'msa',
                'facebook' => 'ms_MY',
                'google'   => 'ms'
            ),
            'tr_TR' => array(
                'twitter'  => 'tr',
                'facebook' => 'tr_TR',
                'google'   => 'tr'
            ),
            'hi_IN' => array(
                'twitter'  => 'hi',
                'facebook' => 'hi_IN',
                'google'   => 'hi'
            ),
            'tl_PH' => array(
                'twitter'  => 'fil',
                'facebook' => 'tl_PH',
                'google'   => 'fil'
            ),
            'zh_CN' => array(
                'twitter'  => 'zh-cn',
                'facebook' => 'zh_CN',
                'google'   => 'zh'
            ),
            'ko_KR' => array(
                'twitter'  => 'ko',
                'facebook' => 'ko_KR',
                'google'   => 'ko'
            ),
            'it_IT' => array(
                'twitter'  => 'it',
                'facebook' => 'it_IT',
                'google'   => 'it'
            ),
            'da_DK' => array(
                'twitter'  => 'da',
                'facebook' => 'da_DK',
                'google'   => 'da'
            ),
            'fr_FR' => array(
                'twitter'  => 'fr',
                'facebook' => 'fr_FR',
                'google'   => 'fr'
            ),
            'pl_PL' => array(
                'twitter'  => 'pl',
                'facebook' => 'pl_PL',
                'google'   => 'pl'
            ),
            'nl_NL' => array(
                'twitter'  => 'nl',
                'facebook' => 'nl_NL',
                'google'   => 'nl'
            ),
            'id_ID' => array(
                'twitter'  => 'in',
                'facebook' => 'nl_NL',
                'google'   => 'in'
            ),
            'hu_HU' => array(
                'twitter'  => 'hu',
                'facebook' => 'hu_HU',
                'google'   => 'hu'
            ),
            'fi_FI' => array(
                'twitter'  => 'fi',
                'facebook' => 'fi_FI',
                'google'   => 'fi'
            ),
            'es_ES' => array(
                'twitter'  => 'es',
                'facebook' => 'es_ES',
                'google'   => 'es'
            ),
            'ja_JP' => array(
                'twitter'  => 'ja',
                'facebook' => 'ja_JP',
                'google'   => 'ja'
            ),
            'nn_NO' => array(
                'twitter'  => 'no',
                'facebook' => 'nn_NO',
                'google'   => 'no'
            ),
            'ru_RU' => array(
                'twitter'  => 'ru',
                'facebook' => 'ru_RU',
                'google'   => 'ru'
            ),
            'pt_PT' => array(
                'twitter'  => 'pt',
                'facebook' => 'pt_PT',
                'google'   => 'pt'
            ),
            'pt_BR' => array(
                'twitter'  => 'pt',
                'facebook' => 'pt_BR',
                'google'   => 'pt'
            ),
            'sv_SE' => array(
                'twitter'  => 'sv',
                'facebook' => 'sv_SE',
                'google'   => 'sv'
            ),
            'zh_HK' => array(
                'twitter'  => 'zh-tw',
                'facebook' => 'zh_HK',
                'google'   => 'zh_HK'
            ),
            'zh_TW' => array(
                'twitter'  => 'zh-tw',
                'facebook' => 'zh_TW',
                'google'   => 'zh_TW'
            ),
            'de_DE' => array(
                'twitter'  => 'de',
                'facebook' => 'de_DE',
                'google'   => 'de'
            ),
            'bg_BG' => array(
                'twitter'  => 'en',
                'facebook' => 'bg_BG',
                'google'   => 'bg'
            ),

        );

        if (array_key_exists($locale, $locales)) {
            $result = $locales[$locale];
        }

        return $result;
    }

    /**
     * @param Joomla\Registry\Registry  $params
     * @param string $url
     *
     * @return string
     */
    private function getGoogleShare($params, $url)
    {
        $html = '';
        if ($params->get('gsButton')) {
            // Get locale code
            if (!$params->get('dynamicLocale')) {
                $this->gshareLocale = $params->get('gsLocale', 'en');
            } else {
                $locales            = $this->getButtonsLocales($this->locale);
                $this->gshareLocale = Joomla\Utilities\ArrayHelper::getValue($locales, 'google', 'en');
            }

            $html .= '<div class="crowdf-share-gshare">';

            $annotation = '';
            if ($params->get('gsAnnotation')) {
                $annotation = ' data-annotation="' . $params->get('gsAnnotation') . '"';
            }

            $size = '';
            if ($params->get('gsAnnotation') !== 'vertical-bubble') {
                $size = ' data-height="' . $params->get('gsType') . '" ';
            }

            $html .= '<div class="g-plus" data-action="share" ' . $annotation . $size . ' data-href="' . $url . '"></div>';

            // Load the JavaScript asynchronous
            if ($params->get('loadGoogleJsLib') and !array_key_exists('google', self::$loaded)) {
                $html .= '
<script src="https://apis.google.com/js/platform.js" async defer>
  {lang: "'.$this->plusLocale.'"}
</script>';
                self::$loaded['google'] = true;
            }

            $html .= '</div>';
        }

        return $html;
    }
}
