=== WPO365 | SAMESITE ===
Contributors: wpo365
Tags: cookies, SameSite, Teams, Microsoft Teams
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.4
Requires PHP: 5.6.40
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Plugin for WordPress websites that require a user to sign in (e.g. with Microsoft using the [WPO365](https://wordpress.org/plugins/wpo365-login/) plugin) and that are loaded inside an iframe (e.g. inside a Microsoft Teams App / Tab or similar). The plugin overrides the pluggable WordPress function **wp_set_auth_cookie** to *always* set **SameSite=None** to enable third-party usage of cookies.

= Prerequisites =

- The **SameSite=None** flag is only respected by browsers such as Chrome when the cookie's Secure flag is set. Therefore the website must use SSL for the plugin to effectively enable browser support for 3rd party cookies.

= Support =

I will go to great length trying to support you if the plugin doesn't work as expected. Go to our [Support Page](https://www.wpo365.com/how-to-get-support/) to get in touch. I haven't been able to test our plugin in all endless possible Wordpress configurations and versions so I am keen to hear from you and happy to learn!

= Feedback =

I am keen to hear from you so share your feedback with me on [Twitter](https://twitter.com/WPO365) and help me get better!

= Open Source =

When you’re a developer and interested in the code you should have a look at the corresponding gist at [github](https://gist.github.com/wpo365/b0a1c3c8c5612fd0012de2e2f65c09c4).

== Installation ==

Perform the following steps to install the plugin:

- Go to **WP Admin > Plugins > Add new** and search for **WPO365**.
- Click **Install** to install the plugin.
- Click **Activate** to activate the plugin.

== Frequently Asked Questions ==

== Screenshots ==

== Upgrade Notice ==

== Changelog ===

= v1.4 =
* Fix: Tested with latest versions of WordPress and PHP.

= v1.3 =
* Fix: Tested with latest versions of WordPress and PHP.

= v1.2 =
* Fix: Added support for PHP 8.

= v1.1 =
* Fix: The plugin would end up in an infinitely loop when using PHP 7.2 or older. This has been fixed by implementing a work-around that abuses the "path" or "domain" parameter of PHP's "setcookie" function to sneak in the SameSite attribute because PHP does not escape semicolons.

= v1.0 =
* Initial version
