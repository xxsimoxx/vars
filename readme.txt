=== vars ===
Plugin Name:        vars
Description:        Vars in shortcodes
Version:            1.3.2
Text Domain:        vars
Domain Path:        /languages
Requires PHP:       5.6
Requires:           1.0.0
Tested:             4.9.99
Author:             Gieffe edizioni
Author URI:         https://www.gieffeedizioni.it
Plugin URI:         https://software.gieffeedizioni.it
Download link:      https://github.com/xxsimoxx/vars/releases/download/v1.3.2/vars.zip
License:            GPLv2
License URI:        https://www.gnu.org/licenses/gpl-2.0.html

With vars you can define name-value associations from the admin.
== Description ==

With vars you can define name-value associations from the admin.

Then, in your content you can insert
`[vars]name[/vars]`
and get _value_ displayed.

Useful if you have a value (a phone number, number of employees) in several pages that can change, so you can change this once from the admin.

It **integrates into TinyMCE** by adding a menu.

You can choose which users can manage vars.

== Screenshots ==

1. Editing vars in backend
2. Security settings
3. TinyMCE button

== Changelog ==

= 1.3.2 =
Added
* Auto update plugin
* Screenshot and many plugin information
Deprecated
* `function cpv_do ( $var )` better use `vars_do`
* `[cpv]` better use `[vars]`
Fixes
* Fixed bug with MCE button with html code in var
Removed
* Self-checking for new releases (old method)

= 1.3.0 =
Plugin renamed from cpvars to vars
When you upgrade from a previous version to 1.3.0, please deactivate and reactivate the plugin to be sure that the migration is complete.
* Renamed everything
* Compatibility with older version settings
Fixes
* Longer preview in TinyMCE button
* Fixed tools menu slug

= 1.2.3 =
Fixes
* All occurrencies of CPvars replaced with cpvars
* PHP 7.4 compatibility

= 1.2.2 =
Added
* Icon on TinyMCE menu and setting page
Bugfix
* Transient for updates
* Correct the slug for security menu

= 1.2.1 =
Added
* Support for GitHub Updater (https://github.com/afragen/github-updater)
* Self-checking for new releases
Changed
* Links in plugins page now have just an icon

= 1.2.0 =
Added
* Now admin can choose who can add/change vars based on capability
* Added a check for new version in GitHub
* Security menu page
Changed
* Links in plugins page now have just an icon

= 1.1.2 =
Fixed
* Fixed a warn in certain conditions

= 1.1.1 =
Added
* Settings in plugin page
Fixed
* Code and translations cleaning

= 1.1.0 =
Added
* Filters to output
* cpv_do() function
Removed
* Option to exec PHP code.
Other
* Started using SEMVer

= 1.0.1 =
Added
* Option to exec PHP code.
* CHANGELOG.md
Removed
* Old debug code
