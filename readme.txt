=== Plugin Name ===
Contributors:  opalsupport
Tags: support, site help
Requires at least: 4.4
Tested up to: 5.4
Stable tag: 1.0.2
Requires PHP: 5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
This plug-in allows you to scan your site to detect common issues which may be causing your site to run slowly or have missing content such as white screens, database errors, and other problems with your WordPress site.
An easy readable scoring system shows non-technical users how your site is doing for Security, Maintenance, Speed and stability.
The plug-in allows you to send the report to yourself and also to our analysis team for a no-obligation assessment and advice regarding how you can repair, secure and speed up your WordPress site.

== Installation ==

1. Install the plug-in through the WordPress plug-ins screen of your wp-admin, or download the plugin from the Worpdress.org repository and upload via the "add new plugin" option.
2. Activate the plug-in through the 'Plug-ins' screen in WordPress
3. Run your first scan either from the 'Perform  Scan' link in the Plug-ins page list, or from the Dashboard Widget.
4. Visit the scan results page to see the scan.
5. You may choose to send the scan to our team for no-obligation assessment and advice.

== Frequently Asked Questions ==
= How are the scores generated =
This plugin profiles your web server, your wordpress installation,the plugins, themes and other components and scores each item from 0 to 100. The scores for "Security", "Maintenance" and "Speed & Stability" are derived from those elements. For example Security relies on an up to date WP core, Plugins which have not been abandoned by their authors, A valid security certificate, a patched version of your server software.

= What do I do if the plugin will not scan my site? =
This plugin has been tested with very old versions of WordPress and old versions of server software PHP, but there are still situations where your site may have issues which prevent the scan from functioning. Your web host my be inhibiting the scan, or another plugin may be preventing it. If so please contact us via  problems@opalsupport.com

= Is this a security plugin? =
No, this plugin profiles your web server, your wordpress installation and the plugins and themes you have installed and gives a natural language summary and a detailied report in order to assist you in improving your site. It is not a security plugin

= Does this tool detect malware? =
No, this plugin profiles themes, plugins and server components to see how up-to-date they are and scores them on that. It is not a malware scanner.

== Screenshots ==

1. After scanning your site you will see an overall score and a breakdown for Security, Maintenance, Stability and Speed. These are aggregated scores from the detailed report.

2. This is the breakdown of what elements are used to calculate the Security score, as shown in the Full Report tab

3. Here we look at some of the elements which relate to the current WordPress and Server environment. Theses details are useful for technicians to trouble shoot your issues and if you click Send Report they will be passed to us.

== Changelog ==

= 1.0.0 =
* Preliminary submission

= 1.0.1 =
* removed unnecessary POST / REQUEST variables, user input isnt required

