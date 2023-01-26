<?php
/**
 * Php version 7.4
 * 
 * @category Scholarship
 * @package  Scholarship
 * @author   McKenna Interactive <info@mckennastudios.com>
 * @license  N/A https://mckennastudios.com
 * @link     https://mckennastudios.com
 */
namespace Scholarship;
defined('ABSPATH') || die('this file requires wordpress core');

class Mail {
    public static function mail_user($user, $subject, $body, $replace ) {
        if ( ! is_object($user )) { // allow passing in plaintext email for simplicity's sake
            $user = get_user_by('email', $user );
        }

        $replace = array_merge( array(
            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            'site' => wp_specialchars_decode( Options::get('blogname'), ENT_QUOTES ),
            'tagurl' => wp_login_url('wp-admin/admin.php?page=sch-tagged'),
            'transcripturl' => wp_login_url('wp-admin/admin.php?page=sch-transcript'),
        ), $replace );

        return wp_mail(
            $user->user_email,
            Util::format_string($subject, $replace ),
            Util::format_string($body, $replace )
        );
    }

    public static function invite_user($email, $role ) {
        // you cannot directly index an array returned from a function in old php
        $tmp = explode('@', $email );
        $username = sanitize_user($tmp[0], true );
        if ( false !== get_user_by('login', $username )) {
            $n = 0;
            do {
                $n += 1;
                $u = $username . '-' . $n;
            } while (false !== get_user_by('login', $u ));
            $username = $u;
        }

        $userid = (int) wp_insert_user( array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => wp_generate_password(),
            'role' => $role,
        ));

        $student = new Student( wp_get_current_user());
        $replace = array(
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'student' => $student->get('first_name') . ' '
                . $student->get('last_name'),
        );

        // this is basically wp_new_user_notification
        global $wpdb, $wp_hasher;
        $user = get_userdata($userid );

        Mail::mail_user(
            Options::get('admin_email'),
            Options::get('sch_mail_notify_admin', 'subject'),
            Options::get('sch_mail_notify_admin', 'body'),
            $replace
        );

        // Generate something random for a password reset key.
        $key = wp_generate_password( 20, false );

        /** This action is documented in wp-login.php */
        do_action('retrieve_password_key', $user->user_login, $key );

        // Now insert the key, hashed, into the DB.
        if ( empty($wp_hasher )) {
            require_once ABSPATH . WPINC . '/class-phpass.php';
            $wp_hasher = new PasswordHash( 8, true );
        }
        $hashed = time() . ':' . $wp_hasher->HashPassword($key );
        $wpdb->update(
            $wpdb->users,
            array('user_activation_key' => $hashed ),
            array('user_login' => $user->user_login )
        );

        $replace['url'] = network_site_url('wp-login.php?action=rp&key='
            . $key . '&login=' . rawurlencode($user->user_login ), 'login');

        Mail::mail_user(
            $user,
            Options::get('sch_mail_invite_' . $role, 'subject'),
            Options::get('sch_mail_invite_' . $role, 'body'),
            $replace
        );

        return $user;
    }
}
