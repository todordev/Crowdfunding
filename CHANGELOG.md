Crowdfunding Platform Changelog
==========================

###v2.7.2
* Fixed fatal error when there is no currency selected.

###v2.7.1
* Fixed category views.

###v2.7
* Improved Project Manager on front-end.
* Improved payment process.
* Fixed issue with payment session if you use several payment plugins.

###v2.6.5
* Fixed an issue with fees during the payment process.
* Fixed the layout that shows extra payment data.

###v2.6.4
* Fixed project wizard. It did not show Intro Article.
* Changed the message that a user receives when he tries to make payment but he has no permissions. The message was changed from "You have no permissions..." to "Please, sign in to the website to make a payment."
* Improved PayPal Standard and its IPN listener.

###v2.6.3
* Fixed an issue during the process of importing locations and regions if the database server is not MariaDB.

###v2.6.2
* Improved image cropping.
* Improved the payment process.
* Fixed some issues.

###v2.6.1
* Fixed an issue with payment process.

###v2.6
* Improved functionality for cropping images on project wizard.
* Added crop functionality on step Story.
* Added checks for changed fields in the forms of the project wizard. The system will show you a warning message if you try to leave the page but changes have not been submitted.
* Added options for minimum and maximum amount that could be made in one transaction.
* Implemented Joomla! ACL on front-end project wizard (Edit and Create campaign).
* Implemented Access View option for campaigns.

###v2.5.3
* It was made compatible with PHP 5.5.

###v2.5.2
* It was made compatible with Prism Library v1.17.
* Changed functionality of resizing pictures uploaded by users. Now, the system will not convert pictures to PNG. It will be stored in their file format, uploaded by the user.
* Added option for image quality. You can use this option to reduce the file size of the pictures that users uploads.
* Added options in the plugin Content - Crowdfunding Validator for restricting changes of the goal amount and campaign duration.

###v2.5.1
* Fixed issue "0 It is missing currency ID".

###v2.5
* Implemented functionality to update project funded amount when create transaction record manually.
* Improved dates formatting. Added options for formatting dates in the datepicker (calendar) and dates displayed on views.
* It was changed default value of dates to 1001-01-01. Now, it is compatible with MySQL 5.7.
* It was improved amounts formatting. Now, the system uses [PHP Intl](http://php.net/manual/en/book.intl.php) for all formatting.
* Fixed an issue when add a country.
* Improved the performance. Now, the system makes fewer database calls and works faster.
* Fixed and improved all modules, plug-ins and components that work with the crowdfunding platform.
* It was improved the transaction process. The payment plugins work faster, use less memory and make fewer database calls.
* Payment plugins use observable object to process transaction. 
* It was added **TransactionManager** that provides events handled by observable objects - **_onBeforeProcessTransaction_** and **_onAfterProcessTransaction_**.
* Added individual project options.
* Improved the module Crowdfunding Info. It was added new layout, options and statistical data.
* It was added new event **_onAfterPaymentNotify_**. The functionality for sending confirmation mails was moved to this event.
* It was added functionality to close payment session by event **_onAfterPayment_**.

###v2.4.1
* Added option to display alternative grid layout on "Discover" page.
* Fixed an issue in PayPal IPN validation. Paypal just recently only accepts TLS 1.2 connection and now the crowdfunding platform works with TLS 1.2 connection.
* Fixed an issue with JomSocial integration and its locations.

###v2.4.0
* Added new section for rewards to component options.
* Added option aspectRatio to the tool that crops the project images.
* Added functionality that resets starting date, when a user launches a project. The starting date will be reset if the project has not been approved and there are no registered transactions.
* Added functionality to create transaction records.
* Added option to the module Crowdfunding Info. You can use them to change button label, hide funding type and information for fundraising process.
* Added tips and tricks on dashboard.
* Improved functionality to change the state of reward, selected during payment process. The reward is assigned to transaction.
* The text in the payment process was altered to sound more universal.
* Improved usability and user experience.

###v2.3.3
* [HIGH] The system does not display rewards if there is no created types.

###v2.3.2
* [LOW] Fixed a bug with location selection.
* [HIGH] Wrong location for file storing when cropping an image.

###v2.3.1
* [HIGH] It is not possible to create a project.

###v2.3
* Added functionality for rewards ordering.
* Fixed the calendar on rewards view (project wizard).
* Fixed selection of locations ( typeaheads ).
* Added section "Tools".

###v2.2
* Added information about the license.
* Replaced where it was used Joomla\String\String with JString.
* Fixed bug with improper use of Joomla\Utilities\ArrayHelper::toInteger.
* Fixed the button "clear" on back-end views.
* Fixed the view that display a widget from embed code.
* Fixed a bug with routing subcategories.
* Added option, for managing pagination, to the views that display list of results.
* Added option to display category description on the view "Category".

###v2.1
* Added option to disable rewards.
* Added server for upgrading the crowdfunding platform via Joomla! updater.
* Added {REWARD_TITLE} and {DELIVERY_DATE} placeholders to the payment plugins.
* Fixed some issues.

###v2.0
* Improved Crowdfunding library.
* Improved code quality.
* Removed Email templates section. You have to use the new extension [Email Templates] (http://itprism.com/free-joomla-extensions/others/email-templates-manager).
* Integrated with Easy Profile.
* Built over [Twitter Bootstrap 3] (http://getbootstrap.com/).
* Renamed the plugin "Content - CrowdFunding Share" to "Content - CrowdFunding Social Share".
* Removed extra images. This functionality will be implemented as third-party extension.
* Added functionality users to subscribe to campaigns following them.

###v1.11.3
* Fixed some issues.

###v1.11.2
* Fixed a bug with wrong redirection after launch.
* Improved ordering.
* Fixed some typos.
* Resolved conflict between jQuery and MooTools when cropping images.

###v1.11.1
* Fixed URL to an image when it is not enabled URL rewrite.

###v1.11
* Added functionality users to report projects.
* Now, the featured projects will be displayed first on the discover page and on the category view.
* Changed the method for image resizing. Method for resizing will be used for pitch_image, extra images and reward images.
* Added new scale method "Fit" for image resizing.
* Added functionality the project owner to crop project picture on the first step of the project wizard.
* Improved categories options. It was added options for sorting results.

###v1.10.1
* Fixed bugs on views "Projects" and "Transactions".
* Improved view "Categories".
* Added new placeholders to the e-mail templates - {PAYER_NAME} and {PAYER_EMAIL}.

###v1.10
* Renamed the plugin event onPaymentDisplay to onPaymentExtras.
* Fixed a bug with payment wizard in four steps when terms of use are enabled.
* Added a new view "Category".
* It was improved the URI routing. Now, the projects are not assigned to "Discover" page. They can be part from "Discover", "Categories", "Category" or "Details" view. You have to assign the [specific modules] (http://itprism.com/help/95-crowdfunding-documentation-faq#specific_modules) to one of those menu items.
* Added a new event "onBeforePaymentAuthorize".
* It was removed the options of the plugin "System - Crowdfunding Modules". Now, all specific modules will be managed by default.
* Improved usability.

###v1.9.1
* Fixed an issue with the router and old category class.
* Improved amount formatting.
* Added a badge element.

###v1.9
* Added option project owners to provide payout information - PayPal account, IBAN and Bank Account,...
* Added functionality project owners to receive their amounts to their PayPal accounts instantly.
* It was done the PayPal Adaptive payment plugin.
* It was fixed the plugins "Content - Crowdfunding - User Mail" and "Content - Crowdfunding - Admin Mail".
* Added option to display number of funders, delivery date and claimed rewards on the module "Crowdfunding Rewards".
* Added a view "Categories".
* Improved
* Fixed some issues.

###v1.8.1
* Fixed import functionality for currencies, countries, locations and states.

###v1.8
* The video on details page can be responsive.
* Improved payment wizard. Now, the content of step 2, when use wizard in four steps, is displayed by a plugin.
* Added option to disable comments.
* Improved the page with funders.
* Added additional step to project wizard, which will be used for third-party extensions. That will provide extra functionality and better customization.
* Added a new step "Manager" to project wizard.
* Added functionality to create campaigns from administration.
* Added options for disabling chosen and selecting administrator.
* Improved the rewards manager.
* Fixed some issues.

###v1.7
* Added options that are used on views Discover and Featured.
    * custom CSS styles ( Now you can include styles for project states "completed successfully", "completed unsuccessfully", "new", "ending soon", "featured" ).
    * title and description length.
* Added option for amount formatting.
* Improved "Content - Crowdfunding Share".
    * Added new option to Facebook Like. That is a button type "Button".
    * Replaced "Send" button with "Share".
    * Added languages to the LinkedIn button.
* Added ability for overwriting component styles by template ones.
* Added title to email templates.
* Added event "onContentValidate" to wizard steps Basic, Funding, Story. Now data validation is handled by plugins.
* Added event "onContentValidateAfterSave" to step "Story".
* Added event "onContentAfterSave" to steps Basic, Funding, Story of the wizard.
* Added method "notifyAjax". Now, the plugin BankTransfer send a request to that method to process transaction.
* Move functionality that validates wizard steps to a plugin "Content - Crowdfunding Validator".
* It was written and generated [Crowdfunding Library documentation] (http://cdn.itprism.com/api/crowdfunding/index.html).
* Improved code quality.

###v1.6.1

* Fixed countries.
* Fixed locations.
* Fixed export resources functionality.
* Fixed many strict error notifications.
* Fixed funding duration validation.
* Added option to be displayed default English PayPal button.
* Added option for importing locations based on minimum population.
* Improved.

###v1.6

* Improved PayPal payment plugin. 
    * Added options for selecting locale and button type - "buy now", "pay now" and donate.
    * Added event "onTransactionChangeState". 
* Added statistical information.
* Added images to rewards.
* Added new data to the countries.
* Improved payments.
    * Added new events for managing payments.
    * Added event "onTransactionChangeState" to all payment plugins.
* Fixed some issues.

###v1.5

* Added section where the administrator will be able to prepare emails for sending in specific cases.
* Added new options
    * date format
    * display creator
* Added functionality for downloading log files.
* Added CSS classes "cf-project-active" and "cf-project-completed" on views Discover and Featured. They are based on current state (days left) of the project. Designers can use them to customize projects easily, on those pages.
* Added project types.  
* Integrated with EasySocial.
* Improved

###v1.4.4

* Added Logs Manager.
* Added functionality for deleting projects if they have not been funded.
* Improved Locations. Now, you can export states.
* Fixed some issues.

###v1.4.3

* Added wizard type for the payment process - "Three Steps" and "Four Steps".
* Fixed an issue when a project is saved without category. 
* Improved some language strings.

###v1.4.2

* Fixed collation of some columns in table "countries". 

###v1.4.1

* Fixed some issues.
* Improved

###v1.4

* Added a new event "onTransactionChangeStatus" to plugins with type CrowdfundingPayment. Now, when the administrator change the status, that event will be triggered.
* Improved the payment process. Some payment plugins were improved too. 
* It was added feature, anonymous users to be able to donate.
* Added ability for uploading many images to projects.
* CrowdfundingCurrency class was refactored. It was implemented [The NumberFormatter class] (http://www.php.net/manual/en/class.numberformatter.php).
* Fixed some issues.
* Improved

###v1.3.1

* Fixed database query.

###v1.3

* Integrated with JomSocial and Kunena.
* Improved integration.
    * Added option for avatar size.
    * Added option for default avatar picture.
* Added countries and states
* Improved import and export functionality.
* Fixed loading locations lag.
* Now, rewards are optional. You are able to publish project without rewards.
* Improved usability of the wizard used for project creating.
* Fixed [issue #29] (https://github.com/ITPrism/Crowdfunding/issues/29). Now, rewards are set as trashed, it they are part of transaction.
* Removed some plugins from the package. The plugins are "Search - Crowdfunding", "Content - Crowdfunding - Manager" and "Crowdfunding Info".
* Developed some modules - "Crowdfunding Search", "Crowdfunding Latest", "Crowdfunding Popular".
* Developed some payment plugins
    * Bank Transfer
    * Mollie iDEAL

NOTE: All new extensions and the removed from the package are available for downloading on [Crowdfunding extension page] (http://itprism.com/free-joomla-extensions/ecommerce-gamification/crowdfunding-collective-raising-capital).

###v1.2.1

* Added to the package the plugins "Content - Crowdfunding - User Mail" and "Content - Crowdfunding - Admin Mail".
* Fixed some issues.

###v1.2

* Added some new options. Now you are able to manage those features better.
    * maximum amount
    * maximum days
    * duration type
    * funding type
* The box with project state information moved to module "Crowdfunding Info"
* The box with rewards moved to module "Crowdfunding Rewards"
* The box with project details moved to module "Crowdfunding Details"
* Added option to set number of project in row.
* Improved responsive design
* [[#12]](https://github.com/ITPrism/Crowdfunding/issues/12 "Date display issue") Fixed the date issue
* Added plugins used for sending mails to administrator and user
    * Content - Crowdfunding - User Mail ( It sends notification mail to the administrator when a user creates or publishes a project. )
    * Content - Crowdfunding - Admin Mail ( It sends notification mail to a user when the administrator approves a project. )
    * Content - Crowdfunding - Manager ( It adds functionality for managing project on details page. It also display statistical information about it. )
* It was added some plugin events
    * onContentAfterSave
    * onContentChangeState
* Added option for Terms Of Use on the page, where user create a project.
* Added rewards manager
* Added filters on backend
* Added filters on discover page
* Now, the owner of the projects can review them even they are not approved.
* Improved the plugin "Content - Crowdfunding - Share"
* Added view "Featured"
* Moved "Discover" options from component config to menu item options.
* Fixed some issues

###v1.1.3

* Fixed date
* Fixed issue with white spaces in payment plugins.
* Added default picture, which will be displayed if missing one.

###v1.1.2

* Added a SEO option for project title.
* Fixed an issue with routing inner categories.
* Fixed some issues

###v1.1.1

* Fixed an issue [[#11]](https://github.com/ITPrism/Crowdfunding/pull/11 "small change to use title for project alias")
* Added option to search plugin that enables or disables searching in area.

###v1.1

* Fixed issue with routing of multilevel categories
* Added option for selection category when adding a menu item.
* Now it works with ITPrism Library 1.2.
* Improved integration. Now it works with Gravatar too.
* Improved routers.
* Improved plugins.
* Improved backend - transactions, projects,...
* Included search plugin ( plg_search_crowdfunding ).
* Included plugin that gives additiona information ( plg_content_crowdfundinginfo ).
* Added avatars to comments, updates and funders list.
* Now, you are able to use Vimeo video.
* Added "Send to Friend" form.
* Fixed some issues.
