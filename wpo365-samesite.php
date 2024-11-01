<?php

/**
 *  Plugin Name: WPO365 | SAMESITE
 *  Plugin URI: https://wordpress.org/plugins/wpo365-samesite/
 *  Description: Plugin for WordPress websites that require a user to sign in (e.g. with Microsoft) and that are loaded inside an iframe (e.g. inside a Microsoft Teams App / Tab or similar). The plugin overrides the pluggable WordPress function wp_set_auth_cookie to set SameSite=None to enable third-party usage.
 *  Version: 1.4
 *  Author: support@wpo365.com
 *  Author URI: https://www.wpo365.com/
 *  License: GPL2+
 */

// Prevent public access to this script
defined('ABSPATH') or die();

if (!function_exists('wp_set_auth_cookie')) :

    /**
     * Sets the authentication cookies based on user ID.
     *
     * The $remember parameter increases the time that the cookie will be kept. The
     * default the cookie is kept without remembering is two days. When $remember is
     * set, the cookies will be kept for 14 days or two weeks.
     *
     * @since 2.5.0
     * @since 4.3.0 Added the `$token` parameter.
     *
     * @param int         $user_id  User ID.
     * @param bool        $remember Whether to remember the user.
     * @param bool|string $secure   Whether the auth cookie should only be sent over HTTPS. Default is an empty
     *                              string which means the value of `is_ssl()` will be used.
     * @param string      $token    Optional. User's session token to use for this cookie.
     */
    function wp_set_auth_cookie($user_id, $remember = false, $secure = '', $token = '')
    {
        if ($remember) {
            /**
             * Filters the duration of the authentication cookie expiration period.
             *
             * @since 2.8.0
             *
             * @param int  $length   Duration of the expiration period in seconds.
             * @param int  $user_id  User ID.
             * @param bool $remember Whether to remember the user login. Default false.
             */
            $expiration = time() + apply_filters('auth_cookie_expiration', 14 * DAY_IN_SECONDS, $user_id, $remember);

            /*
			 * Ensure the browser will continue to send the cookie after the expiration time is reached.
			 * Needed for the login grace period in wp_validate_auth_cookie().
			 */
            $expire = $expiration + (12 * HOUR_IN_SECONDS);
        } else {
            /** This filter is documented in wp-includes/pluggable.php */
            $expiration = time() + apply_filters('auth_cookie_expiration', 2 * DAY_IN_SECONDS, $user_id, $remember);
            $expire     = 0;
        }

        if ('' === $secure) {
            $secure = is_ssl();
        }

        // Front-end cookie is secure when the auth cookie is secure and the site's home URL uses HTTPS.
        $secure_logged_in_cookie = $secure && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME);

        /**
         * Filters whether the auth cookie should only be sent over HTTPS.
         *
         * @since 3.1.0
         *
         * @param bool $secure  Whether the cookie should only be sent over HTTPS.
         * @param int  $user_id User ID.
         */
        $secure = apply_filters('secure_auth_cookie', $secure, $user_id);

        /**
         * Filters whether the logged in cookie should only be sent over HTTPS.
         *
         * @since 3.1.0
         *
         * @param bool $secure_logged_in_cookie Whether the logged in cookie should only be sent over HTTPS.
         * @param int  $user_id                 User ID.
         * @param bool $secure                  Whether the auth cookie should only be sent over HTTPS.
         */
        $secure_logged_in_cookie = apply_filters('secure_logged_in_cookie', $secure_logged_in_cookie, $user_id, $secure);

        if ($secure) {
            $auth_cookie_name = SECURE_AUTH_COOKIE;
            $scheme           = 'secure_auth';
        } else {
            $auth_cookie_name = AUTH_COOKIE;
            $scheme           = 'auth';
        }

        if ('' === $token) {
            $manager = WP_Session_Tokens::get_instance($user_id);
            $token   = $manager->create($expiration);
        }

        $auth_cookie      = wp_generate_auth_cookie($user_id, $expiration, $scheme, $token);
        $logged_in_cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in', $token);

        /**
         * Fires immediately before the authentication cookie is set.
         *
         * @since 2.5.0
         * @since 4.9.0 The `$token` parameter was added.
         *
         * @param string $auth_cookie Authentication cookie value.
         * @param int    $expire      The time the login grace period expires as a UNIX timestamp.
         *                            Default is 12 hours past the cookie's expiration time.
         * @param int    $expiration  The time when the authentication cookie expires as a UNIX timestamp.
         *                            Default is 14 days from now.
         * @param int    $user_id     User ID.
         * @param string $scheme      Authentication scheme. Values include 'auth' or 'secure_auth'.
         * @param string $token       User's session token to use for this cookie.
         */
        do_action('set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme, $token);

        /**
         * Fires immediately before the logged-in authentication cookie is set.
         *
         * @since 2.6.0
         * @since 4.9.0 The `$token` parameter was added.
         *
         * @param string $logged_in_cookie The logged-in cookie value.
         * @param int    $expire           The time the login grace period expires as a UNIX timestamp.
         *                                 Default is 12 hours past the cookie's expiration time.
         * @param int    $expiration       The time when the logged-in authentication cookie expires as a UNIX timestamp.
         *                                 Default is 14 days from now.
         * @param int    $user_id          User ID.
         * @param string $scheme           Authentication scheme. Default 'logged_in'.
         * @param string $token            User's session token to use for this cookie.
         */
        do_action('set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in', $token);

        /**
         * Allows preventing auth cookies from actually being sent to the client.
         *
         * @since 4.7.4
         * @since 6.2.0 The `$expire`, `$expiration`, `$user_id`, `$scheme`, and `$token` parameters were added.
         *
         * @param bool   $send       Whether to send auth cookies to the client. Default true.
         * @param int    $expire     The time the login grace period expires as a UNIX timestamp.
         *                           Default is 12 hours past the cookie's expiration time. Zero when clearing cookies.
         * @param int    $expiration The time when the logged-in authentication cookie expires as a UNIX timestamp.
         *                           Default is 14 days from now. Zero when clearing cookies.
         * @param int    $user_id    User ID. Zero when clearing cookies.
         * @param string $scheme     Authentication scheme. Values include 'auth' or 'secure_auth'.
         *                           Empty string when clearing cookies.
         * @param string $token      User's session token to use for this cookie. Empty string when clearing cookies.
         */
        if (!apply_filters('send_auth_cookies', true, $expire, $expiration, $user_id, $scheme, $token)) {
            return;
        }

        $php_version = explode('.', \phpversion());

        $same_site_as_option = intval($php_version[0]) > 7                        // Support for PHP 8
            || intval($php_version[0]) >= 7 && intval($php_version[1]) >= 3;    // PHP > 7.3

        if ($same_site_as_option) {
            setcookie($auth_cookie_name, $auth_cookie, array("expires" => $expire, "path" => PLUGINS_COOKIE_PATH, "domain" => COOKIE_DOMAIN, "secure" => $secure, "httponly" => true, "SameSite" => "None"));
            setcookie($auth_cookie_name, $auth_cookie, array("expires" => $expire, "path" => ADMIN_COOKIE_PATH, "domain" => COOKIE_DOMAIN, "secure" => $secure, "httponly" => true, "SameSite" => "None"));
            setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, array("expires" => $expire, "path" => COOKIEPATH, "domain" => COOKIE_DOMAIN, "secure" => $secure, "httponly" => true, "SameSite" => "None"));

            if (COOKIEPATH != SITECOOKIEPATH) {
                setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, array("expires" => $expire, "path" => SITECOOKIEPATH, "domain" => COOKIE_DOMAIN, "secure" => $secure, "httponly" => true, "SameSite" => "None"));
            }
        } else {
            setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH . "; SameSite=None", COOKIE_DOMAIN, $secure, true);
            setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH . "; SameSite=None", COOKIE_DOMAIN, $secure, true);
            setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH . "; SameSite=None", COOKIE_DOMAIN, $secure, true);

            if (COOKIEPATH != SITECOOKIEPATH) {
                setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH . "; SameSite=None", COOKIE_DOMAIN, $secure, true);
            }
        }
    }

endif;
