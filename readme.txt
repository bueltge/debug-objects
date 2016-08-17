=== Debug Objects ===
Contributors: Bueltge, inpsyde
Donate link: http://bueltge.de/wunschliste/
Tags: debug, sql, analyse, tuning, performance, database, queries, query, php, cron, cache
Requires at least: 3.3
Tested up to: 4.5
Stable tag: 2.3.1

The Plugin Debug Objects provides a large number of information: query, cache, cron, constants, hooks, functions and many more.

== Description ==
The Plugin Debug Objects provides the user, which has the appropriate rights, normally the administrator, a large number of information: query, cache, cron, constants, hooks, functions and many many more. Values and content get displayed at the frontend and backend of the blog, to analyze errors but also to better understand and develop with/for WordPress.

= The Plugin provides in various tabs information to: =

* PHP
* Memory usage
* Load Time
* Included Files
* Operating System
* Server
* WordPress Version
* Language
* Very extensive definitions of various constants
* Cookie definitions
* Separate user and usermeta tables
* FTP and SSH definitions
* Detailed Query information
* Query information about the active plugins, nice to identifier the longrunners on the plugins
* Query information about all queries from `wp-content`-directory
* Conditional tags; value of the tag
* Roles and his capabilities
* Theme information
* HTML Inspector is a code quality tool to check markup. Any errors will be reported to the console of the browser. This works only on front end. use [HTML Inspector](https://github.com/philipwalton/html-inspector)
* Translation debugging helper
* Template Information
* Cron content and his functions to an cron
* Cache content
* Hooks and filters
* All options from table, for single and multisite installation
* Time values for inspect Permalink Rules
* Rewrites, a list of cached rewrites and the rule
* Current screen information to find the right backend page and hook
* List Custom Post Type Arguments
* Functions, which respond on hooks and filters
* Contents of arrays to hooks and filters
* All defined constants
* All classes
* All shortcodes
* List transients
* Post Meta data
* See data from `$_POST`; `$_GET` and debug backtrace before rewrite; usefull for forms in backend
* Run WordPress in default mode via url-param
* Add alternative PHP Error reporting: [PHP Error](http://phperror.net/)
* Include Logging in Chrome Console: [ChromeLogger](http://chromelogger.com/)
* Support (WP Fields API)[https://github.com/sc0ttkclark/wordpress-fields-api]
* and many more ...


The plugin does not filter values and should only be used for information and optimization, I don't recommended to use it on a live blog. For developers it can rapidly deliver data, which is useful in a development environment.
There are no data in the database and there are no settings. Therefore, the installation is pretty simple: Just upload the Plugin in the Plugin directory or use the automatic installation of the backend to install and activate the Plugin. In the footer of the frontend of the blog, you can see the information.

= Bugs, technical hints or contribute =
Please give us feedback, contribute and file technical bugs on [GitHub Repo](https://github.com/bueltge/Debug-Objects).

**Made by [Inpsyde](http://inpsyde.com) &middot; We love WordPress**

Have a look at the premium plugins in our [market](http://marketpress.com).

== Installation ==
= Requirements =
* WordPress (also Multisite) version 3.3 and later (tested at 3.3)
* PHP 5.2.4, Tested with PHP 5.4

= Installation =
1. Unpack the download-package
2. Upload the file to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to *Tools* -> *Debug Objects* and change settings or read instructions for use with url params
5. Ready


== Screenshots ==
1. Example Screenshot (WordPress 3.3)
2. Another Example with the current hooks of this back end page (WordPress 3.8-alpha)

== Other Notes ==
= Older versions =
You find older version on this repo, [here](http://wordpress.org/plugins/debug-objects/developers/) But only to version 2.1.14
After this version you find the other verison on the [github repo](https://github.com/bueltge/Debug-Objects)

= License =
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](http://bueltge.de/wunschliste/ "Wishliste and Donate") for the time I've spent writing and supporting this plugin. And I really don't want to know how many hours of my life this plugin has already eaten ;)

== Changelog ==
= 2.3.1 (2016-03-31) =
* Fix small issue for Inspector and his records.

= 2.3.0 (2016-03-24) =
* Add tab to analyze each role and his capabilities.

= 2.2.2 (2016-02-15) =
* Fix for update settings in Single Site Install.

= 2.2.1 (2016-02-01) =
* Support now [WP Fiels API](https://github.com/sc0ttkclark/wordpress-fields-api).
* Fix for deprecated `mysql_query`.
* Fix for transient view.
* Add verison string for script/style in enqueue tab.

= 2.2.0 (2015-01-22) =
* Add new tab for check timeline of Permalink rules
* Add more visibility for php errors, warnings, notice
* More css rules for output view that it works on more themes
* Add php error backtrace alternative
* Add composer support
* Add cron logger, save in transients with string `debug_objects_http_<transport><time>`, [issue 36](https://github.com/bueltge/Debug-Objects/issues/36)
* Add Transients view
* More information on Page Hooks

= 2.1.18 (05/19/2014) =
* Solve SVN Bug, now with all files also inside the SVN, not only the git

= 2.1.17 (05/15/2014) =
* Add [HTML Inspector](https://github.com/philipwalton/html-inspector) for check in front end
* Different changes on formatting output
* Format SQL Statements in Query and Plugin Query Tab
* Add new tab to list all options, options from single and multisite installations
* More comfort on read tables, all tables now sortable
* Add option to filter all classes, functions, hooks from this plugin Debug Objects
* Update, enhance Stylesheet
* Different changes in the core
* Add possibility to search inside the tables, easy to use and very fast to find values
* Different php note fixes

= 2.1.16 (11/14/2013) =
* Mark important globals for better view [#28](https://github.com/bueltge/Debug-Objects/issues/28)
* Fix problem on view for enqueue stuff [#29](https://github.com/bueltge/Debug-Objects/pull/29)
* Update style for better view of code-tags
* Remove php notice [#31](https://github.com/bueltge/Debug-Objects/issues/31)
* Add new tab for see backend current page, meta data [#27](https://github.com/bueltge/Debug-Objects/issues/27)
* Persist options on deactivation, drop only on uninstall [#26](https://github.com/bueltge/Debug-Objects/pull/26)

= 2.1.15 (08/09/2013) =
* Enhancement to see the queries of plugins and identfier the problems
* Sort queries on load time
* Fix on empty arrays on shortcodes
* More UI on tabs like default WordPress, also a difference from classic to fresh backend
* Remove Translation files, to old and the source use often only english - enough for debugging
* Filter for includes files, now without `wp-admin` and `wp-includes` folders
* Fix cookie function, to cache last active tab

= 2.1.14 (08/15/2013) =
* Small fixes for php strict warnings [Forum Thread](http://wordpress.org/support/topic/strict-warnings-fix)
* Add function `debug_to_console( $data )` for easy to use debug informations in the console from browser, see settings page for hints

= 2.1.13 (06/18/2013) =
* Add new output for current hooks
* Add list of all shortcodes and his function to get the output
* Fix Admin Bar Button on single install
* Small changes on the hints on the settings to the information about ChromeLogger

= 2.1.12 (02/01/2013) =
* Add to see data from `$_POST`; `$_GET` and debig backtrace before rewrite; usefull for forms in backend, see [Support Forum Discussion](http://wordpress.org/support/topic/feature-suggestion-to-debug-pre-redirect)
* Change init of ChromePHP to load very early
* Small changes on code
* Remove Super Var Dump, ChromePHP is more useful

= 2.1.11 (01/08/2013) =
* Add possibility to run WP in default mode; Add the url-param 'default', like '?debug&default' for run WordPress in a safe mode. Plugins are not loaed and set the default theme as active theme, is it available.
* Add logging in chrome Webinspector via [ChromePHP](http://www.chromephp.com/)
* Add [PHPError](http://phperror.net/), alternative PHP Error reporting

= 2.1.10 (11/19/2012) =
* Mninor Fixes, PHP Warnings and Notice
* Add "Super Var Dump" project

= 2.1.9 =
* Fix for save settings in WP multisite 3.4*
* Add list of all registered IDs on tab 'Theme'
* Add new tab for inspect the domain for different values

= 2.1.8 =
* Change/add options for stack trace on query list
* Change output on query, faster, lighter
* Add item in Admin Bar for faster go to settings
* Small changes on source

= 2.1.7 =
* Update for [issue #2](https://github.com/bueltge/Debug-Objects/issues/2)
* Markup Fix on Settings page for WP 3.4 

= 2.1.6 =
* Fix on activation for add the custom table
* Add content of cron
* ToDo: remove i18n possibility; to slow for faster debuggging

= 2.1.5 =
* Add Tab for all deaclared classes and subclasses
* Add Tab for all defined functions
* Small change on style

= 2.1.4 =
* Full compatible to PHP 5.3 ([PHP 5.3] The use of function ereg_replace() is discouraged; use preg_replace() instead)
* Fix direct view via settings
* Small changes on source

= 2.1.3 =
* Fix, enhanced items for global php variables on php tab

= 2.1.2 =
* Fix for use an private method
* Fix for cache and debug mode
* Change load time, if dont view items; many faster now

= 2.1.1 =
* Fix check for PHP version

= 2.1.0 =
* Add tab for theme and template informations
* small fix with externel plugin-folder

= 2.0.2 =
* fix settings on use in Multisite
* Add php check on activate
* change init for all class to use the plugin also on PHP smaller 5.3

= 2.0.1 =
* Add Memory informations, Load Time, included Files
* Change query output with small changes; view queries bigger 0.5 and 1.0 ms in other color for fast identification
* Add fix on warp to include unknown functions for PHP smaller 5.3 (hope)
* Further complement the phpdoc

= 2.0.0 =
* Rewrite the plugin
* Add settings page
* Cookie for view output
* Different classes for different 
* Params for control output tasks

= v1.1.0 (12/04/2011) =
* Add Hooks of current page
* Add list of all enqueued scripts and stylesheets
* different changes on source
* add more globals on first tab
* test in WP 3.3RC1
 
= v1.0.3 (03/23/2011) =
* changes for the plugin Debug Queries
* small changes fpr WP Codex and notice of WP 3.1
* Add bulgarian translation

= v1.0.2 (03/06/2011)) =
* small fix on 2 php notice
* change the description of plugins
* add new language file for german users

= v1.0.1 (11/12/2010) =
* Bugfix: check for vars for no php warnings from WP Errors

= v1.0.0 (11/06/2010) =
* Bugfix: set vars for no php warnings
* Feature: add param for only debug via get-params; see description

= v0.3 (02/05/2010) =
* Small fix for search plugin Debug Queries

= v0.2 (17/12/2009) =
* also view all contens in backend of WordPress
* small bugfixes on html-markup
* 2 new constants for hook on frontend and backend; see the php-file

= v0.1 (30/06/2009) =
* Write a Plugin based on my ideas and my many help files
