# Debug Objects
The WordPress Plugin *Debug Objects* provides a large number of information: query, cache, crons, constants, hooks, functions and many more.

## Description
The Plugin Debug Objects provides the user, which has the appropriate rights, normally the administrator, a large number of information: query, cache, crons, constants, hooks, functions and many many more. Values and content get displayed at the frontend and backend of the blog, to analyze errors but also to better understand and develop with/for WordPress.

*The Plugin provides in various tabs information to:*

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
* File Permissions
* Separate user and usermeta tables
* FTP and SSH definitions
* Detailed Query information
* Query informaton about the active plugins, nice to identifier the longrunners on the plugins
* Conditional tags; value of the tag
* Theme information
* Translation debugging helper
* Template Information
* Cron content and his functions to an cron
* Cache content
* Hooks and filters
* Current screen information to find the right backend page and hook
* Functions, which respond on hooks and filters
* Contents of arrays to hooks and filters
* All defined constants
* All classes
* All shortcodes
* Post Meta data
* List Custom Post Type Arguments
* See data from `$_POST`; `$_GET` and debug backtrace before rewrite; usefull for forms in backend
* Run WordPress in default mode via url-param
* Add alternative PHP Error reporting: [PHP Error](http://phperror.net/)
* Include Logging in Chrome Console: [ChromeLogger](http://chromelogger.com/)
* and many more ...

The plugin does not filter values and should only be used for information and optimization, I don't recommended to use it on a live blog. For developers it can rapidly deliver data, which is useful in a development environment.
There are no data in the database and there are no settings. Therefore, the installation is pretty simple: Just upload the Plugin in the Plugin directory or use the automatic installation of the backend to install and activate the Plugin. In the footer of the frontend of the blog, you can see the information.


## Installation
### Requirements
* WordPress (also Multisite) version 3.3 and later (tested at 3.3)
* PHP 5.2.4; PHP 5.3 preferred

### Installation
1. Unpack the download-package
1. Upload the file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to *Tools* -> *Debug Objects* and change settings or read instructions for use with url params
1. Ready


## Screenshots
![Settings Screenshot in WordPress 3.6 alpha][screenshot]

[screenshot]: https://raw.github.com/bueltge/Debug-Objects/master/assets/screenshot-1.png "Settings in WordPress 3.6 alpha"

 * [See this example screenshot in WordPress 3.3](https://github.com/bueltge/Debug-Objects/blob/master/screenshot-1.png)


## Other Notes
### License
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](http://bueltge.de/wunschliste/ "Wishliste and Donate") for the time I've spent writing and supporting this plugin. And I really don't want to know how many hours of my life this plugin has already eaten ;)

### Contact & Feedback
The plugin is designed and developed by me [Frank Bültge](http://bueltge.de), [G+ Page](https://plus.google.com/111291152590065605567/about?rel=author)

Please let me know if you like the plugin or you hate it or whatever ... Please fork it, add an issue for ideas and bugs.

### Disclaimer
I'm German and my English might be gruesome here and there. So please be patient with me and let me know of typos or grammatical farts. Thanks

## Changelog

 * [see on the page](http://wordpress.org/extend/plugins/debug-objects/changelog/)
 * or see the [commits](https://github.com/bueltge/Debug-Objects/commits/master)
