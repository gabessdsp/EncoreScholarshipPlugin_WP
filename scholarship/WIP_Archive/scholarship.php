<?php
/**
 * Php version 7.4
 * Plugin Name:    Scholarship Applications
 * Version:        1.0.0
 * License:        BSD 2-clause

 * Copyright (c) 2015-2018, Sam Heybey
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:

 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 * @category Scholarship
 * @package  Scholarship
 * @author   McKenna Interactive <info@mckennastudios.com>
 * @license  N/A https://mckennastudios.com
 * @link     https://mckennastudios.com
 */

/* Edits made by Gabriel McKenna January 2018:
- Version to 0.4
- Updated Copyright
- Added class "needhelp" to make the text larger and adding the link in more
areas on the application page, counselor transcript page and the recommendation page.
- Updated staff member text to reflect they need to be musical staff members
with examples.
- added specific text about sesdrams.org accounts being unable to use along
with other internal school system e-mails.

*/

namespace Scholarship;

defined("ABSPATH") || die("this file requires wordpress core");
require_once ABSPATH . "wp-admin/includes/file.php";

/**
 * URL Class
 * 
 * @category Scholarship
 * @package  Scholarship
 * @author   McKenna Interactive <info@mckennastudios.com>
 * @license  N/A https://mckennastudios.com
 * @link     https://mckennastudios.com
 */
class URL
{
    /**
     * Get function
     * 
     * @param path $path path of the something...
     * 
     * @return the url of the plugin
     */
    public static function get($path)
    {
        return plugins_url($path, __FILE__);
    }
}

/**
 * Autoload Class
 * 
 * @category Scholarship
 * @package  Scholarship
 * @author   McKenna Interactive <info@mckennastudios.com>
 * @license  N/A https://mckennastudios.com
 * @link     https://mckennastudios.com
 */
class Autoload
{
    private static $_basepath;
    /**
     * Init function
     * 
     * @param basepath $_basepath path of the something...
     * 
     * @return nothing
     */
    public static function init($_basepath)
    {
        self::$_basepath = $_basepath;
    }

    /**
     * LoadClass function
     * 
     * @param class $class path of the something...
     * 
     * @return nothing
     */
    public static function loadClass($class)
    {
        $class = array_slice(explode("\\", $class), -1);
        $filename = self::$_basepath . $class[0] . ".php";
        if (file_exists($filename)) {
            include_once $filename;
        }
    }
}

Autoload::init(plugin_dir_path(__FILE__) . "includes/");
spl_autoload_register("\Scholarship\Autoload::loadClass", true);

add_action("init", "\Scholarship\Util::init");

add_filter("register", "\Scholarship\Util::register_link");
add_action("register_form", "\Scholarship\LoginForm::fields");
add_filter("registration_errors", "\Scholarship\LoginForm::errors", 10, 2);
add_action("user_register", "\Scholarship\Student::register");

add_filter("login_redirect", "\Scholarship\Admin::login_redirect", 10, 3);
add_filter("login_message", "\Scholarship\Admin::login_message");

add_action("edit_user_profile", "\Scholarship\ProfileForm::fields");
add_action("show_user_profile", "\Scholarship\ProfileForm::fields");
add_action(
    "user_profile_update_errors",
    "\Scholarship\LoginForm::errors",
    10,
    3
);
add_action("profile_update", "\Scholarship\Student::update");

add_action("admin_menu", "\Scholarship\Admin::menu");
add_action("admin_init", "\Scholarship\Options::add_settings");
add_action("admin_post_sch-apply", "\Scholarship\Application::post");
add_action("admin_post_sch-recommend", "\Scholarship\Recommendation::post");
add_action("admin_post_sch-upload", "\Scholarship\Counselor::upload_post");
add_action(
    "admin_post_sch-delete-application",
    "\Scholarship\Admin::delete_application_post"
);
add_action(
    "admin_post_sch-delete-application-all",
    "\Scholarship\Admin::deleteApplicationAllPost"
);

add_action("admin_enqueue_scripts", "\Scholarship\Admin::application_script");
add_action("add_meta_boxes", "\Scholarship\Application::meta_boxes");
add_action(
    "load-toplevel_page_sch-application",
    "\Scholarship\Application::help"
);
add_action(
    "wp_ajax_sch_application_search_staff",
    "\Scholarship\Admin::handle_search_staff"
);
add_action(
    "wp_ajax_sch_application_search_counselors",
    "\Scholarship\Admin::handle_search_counselors"
);

add_action("delete_user", "\Scholarship\Util::delete_image");

register_activation_hook(__FILE__, "\Scholarship\Util::activate");
register_deactivation_hook(__FILE__, "\Scholarship\Util::deactivate");