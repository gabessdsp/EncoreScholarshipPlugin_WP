<?php
namespace Scholarship;
defined( 'ABSPATH' ) || die( 'this file requires wordpress core' );

class Options {
    private static $data = array();
    private static $defaults = array(
        'sch_enabled' => array(
            'student' => true,
            'staff' => true,
            'counselor' => true,
            'judge' => true,
        ),
        'sch_disabled_message' => 'Applications are closed for the year.',
        'sch_mail_notify_admin' => array(
            'subject' => '[{{site}}] {{student}} invited a {{role}}',
            'body' => 
                "New {{role}} invited to {{site}} by {{student}}:\n\nUsername: {{username}}\nE-mail: {{email}}",
        ),
        'sch_mail_invite_staff' => array(
            'subject' => '[{{site}}] {{student}} tagged you in their application',
            'body' =>
                "Hello! {{student}} tagged you in their scholarship application on {{site}} as a staff member.\n\nWe've set up an account for you to review their application and submit a recommendation.\n\nYour username is {{username}}. Please visit the following address to set your password:\n{{url}}\n\nOnce you're set up, log in to see the application you were tagged in:\n{{tagurl}}",
        ),
        'sch_mail_invite_counselor' => array(
            'subject' => '[{{site}}] {{student}} tagged you in their application',
            'body' => 
                "Hello! {{student}} tagged you in their scholarship application on {{site}} as their guidance counselor.\n\nWe've set up an account for you to upload their transcript once they've made their official request.\n\nYour username is {{username}}. Please visit the following address to set your password:\n{{url}}\n\nOnce you're set up, log in to see the student who tagged you and upload their transcript:\n{{tagurl}}",
        ),
        'sch_mail_tag_staff' => array(
            'subject' => '[{{site}}] {{student}} tagged you in their application',
            'body' =>
                "Hello, {{name}}! {{student}} tagged you in their scholarship application on {{site}}.\n\nPlease take a moment to review their application and provide your recommendation.\n{{tagurl}}",
        ),
        'sch_mail_tag_counselor' => array(
            'subject' => '[{{site}}] {{student}} tagged you in their application',
            'body' =>
                "Hello, {{name}}! {{student}} tagged you in their scholarship application on {{site}}.\n\nPlease take a moment to log in and upload their high school transcript.\n{{tagurl}}",
        ),
    );

    public static function get_default( $name, $index = false ) {
        if ( false !== $index ) {
            return self::$defaults[ $name ][ $index ];
        }
        return self::$defaults[ $name ];
    }
    public static function get( $name, $index = false ) {
        if ( ! isset(self::$data[ $name ] ) ) {
            self::$data[ $name ] = get_option( $name );
            if ( self::$data[ $name ] === false ) {
                self::$data[ $name ] = self::get_default( $name );
            }
        }

        if ( false !== $index ) {
            if ( isset( self::$data[ $name ][ $index ] ) ) {
                return self::$data[ $name ][ $index ];
            } else {
                return '';
            }
        }
        return self::$data[ $name ];
    }
    public static function set( $name, $value ) {
        self::$data[ $name ] = $value;
        return update_option( $name, $value );
    }

    public static function add_settings() {
        add_settings_section(
            'sch_options_general',
            'General',
            '\Scholarship\Util::noop',
            'sch_options'
        );
        add_settings_section(
            'sch_options_mail',
            'Mail Templates',
            '\Scholarship\Options::mail_help',
            'sch_options'
        );

        register_setting( 'sch_options', 'sch_enabled',
            '\Scholarship\Options::sanitize_enabled' );
        register_setting( 'sch_options', 'sch_disabled_message',
            '\Scholarship\Options::sanitize_disabled_message' );

        register_setting( 'sch_options', 'sch_mail_notify_admin',
            '\Scholarship\Options::sanitize_notify_admin' );
        register_setting( 'sch_options', 'sch_mail_invite_staff',
            '\Scholarship\Options::sanitize_invite_staff' );
        register_setting( 'sch_options', 'sch_mail_invite_counselor',
            '\Scholarship\Options::sanitize_invite_counselor' );
        register_setting( 'sch_options', 'sch_mail_tag_staff',
            '\Scholarship\Options::sanitize_tag_staff' );
        register_setting( 'sch_options', 'sch_mail_tag_counselor',
            '\Scholarship\Options::sanitize_tag_counselor' );

        add_settings_field(
            'sch_enabled',
            'Enabled',
            '\Scholarship\Options::field_enabled',
            'sch_options',
            'sch_options_general'
        );
        add_settings_field(
            'sch_disabled_message',
            'Disabled Message',
            '\Scholarship\Options::field_disabled_message',
            'sch_options',
            'sch_options_general',
            array( 'label_for' => 'sch_disabled_message' )
        );

        add_settings_field(
            'sch_mail_notify_admin',
            'Admin Notification',
            '\Scholarship\Options::field_mail_notify_admin',
            'sch_options',
            'sch_options_mail'
        );
        add_settings_field(
            'sch_mail_invite_staff',
            'Staff Invitation',
            '\Scholarship\Options::field_mail_invite_staff',
            'sch_options',
            'sch_options_mail'
        );
        add_settings_field(
            'sch_mail_invite_counselor',
            'Counselor Invitation',
            '\Scholarship\Options::field_mail_invite_counselor',
            'sch_options',
            'sch_options_mail'
        );
        add_settings_field(
            'sch_mail_tag_staff',
            'Staff Tagged',
            '\Scholarship\Options::field_mail_tag_staff',
            'sch_options',
            'sch_options_mail'
        );
        add_settings_field(
            'sch_mail_tag_counselor',
            'Counselor Tagged',
            '\Scholarship\Options::field_mail_tag_counselor',
            'sch_options',
            'sch_options_mail'
        );
    }

    public static function sanitize_enabled( $value ) {
        if ( ! is_array( $value ) ) {
            $value = array();
        }
        $enabled = array();
        foreach ( array( 'student', 'staff', 'counselor', 'judge' ) as $role ) {
            if ( isset( $value[ $role ] )
                && 'true' === trim( $value[ $role ] ) ) {
                $enabled[ $role ] = true;
            } else {
                $enabled[ $role ] = false;
            }
        }
        return $enabled;
    }
    public static function sanitize_disabled_message( $value ) {
        $value = trim( $value );
        return empty( $value ) ? self::get_default( 'sch_disabled_message' ) : $value;
    }

    private static function sanitize_mail_option( $value, $name ) {
        if ( ! is_array( $value ) ) {
            $value = array();
        }

        foreach ( array( 'body', 'subject' ) as $part ) {
            if ( empty( $value[ $part ] ) ||
                ( '' === trim( $value[ $part ] ) ) ) {
                $value[ $part ] = self::get_default( 'sch_mail_' . $name, $part );
            }
        }
        $value['subject'] = trim( str_replace(
            "\n", '', $value['subject'] ) );
        $value['body'] = trim( $value['body'] );

        return $value;
    }

    public static function sanitize_notify_admin( $value ) {
        return self::sanitize_mail_option( $value, 'notify_admin' );
    }
    public static function sanitize_invite_staff( $value ) {
        return self::sanitize_mail_option( $value, 'invite_staff' );
    }
    public static function sanitize_invite_counselor( $value ) {
        return self::sanitize_mail_option( $value, 'invite_counselor' );
    }
    public static function sanitize_tag_staff( $value ) {
        return self::sanitize_mail_option( $value, 'tag_staff' );
    }
    public static function sanitize_tag_counselor( $value ) {
        return self::sanitize_mail_option( $value, 'tag_counselor' );
    }

    public static function field_enabled() {
        foreach ( array( 'student', 'staff', 'counselor', 'judge' ) as $role ) {
            ?>
            <label><input type="checkbox" id="sch_enabled_<?php echo $role; ?>"
                name="sch_enabled[<?php echo $role; ?>]" value="true"<?php
            if ( Options::get( 'sch_enabled', $role ) ) {
                echo ' checked';
            }
            ?>> <?php echo ucfirst( $role ); ?></label><br>
            <?php
        }
        ?>
        <p class="description">Allow or deny site access to certain roles. If
        students are disabled, new registrations are disallowed.</p>
        <?php
    }
    public static function field_disabled_message() {
        $disabled_message = esc_attr( self::get( 'sch_disabled_message' ) );
        ?>
        <input class="regular-text" type="text" id="sch_disabled_message"
            name="sch_disabled_message" title="Leave blank to restore default"
            value="<?php echo $disabled_message; ?>">
        <p class="description">This message is shown to users who try to log
        in while their role is disabled.</p>
        <?php
    }

    private static function field_mail_template( $message ) {
        ?><p><label>Subject:<br>
        <input class="regular-text" type="text" id="sch_mail_<?php echo $message; ?>_subject"
            name="sch_mail_<?php echo $message; ?>[subject]"
            title="Leave blank to restore default"
            value="<?php echo esc_attr( self::get( 'sch_mail_' . $message, 'subject' ) ); ?>">
        </label></p>
        <p><label>Body:<br>
        <textarea class="large-text" rows="20"
            title="Leave blank to restore default"
            id="sch_mail_<?php echo $message; ?>_body"
            name="sch_mail_<?php echo $message; ?>[body]"><?php
            echo esc_html( self::get( 'sch_mail_' . $message, 'body' ) );
        ?></textarea></label></p>
        <?php
    }

    public static function field_mail_notify_admin() {
        self::field_mail_template( 'notify_admin' );
    }
    public static function field_mail_invite_staff() {
        self::field_mail_template( 'invite_staff' );
    }
    public static function field_mail_invite_counselor() {
        self::field_mail_template( 'invite_counselor' );
    }
    public static function field_mail_tag_staff() {
        self::field_mail_template( 'tag_staff' );
    }
    public static function field_mail_tag_counselor() {
        self::field_mail_template( 'tag_counselor' );
    }

    public static function mail_help() {
        ?>
        <p>The following tokens can be used to put context-sensitive information in email templates:</p>
        <p>For all templates:</p>
        <table class="form-table"><tbody>
            <tr>
                <th>{{student}}</th>
                <td>Student's full name</td>
            </tr>
            <tr>
                <th>{{site}}</th>
                <td>Site name</td>
            </tr>
        </table>
        <p>For invitations:</p>
        <table class="form-table"><tbody>
            <tr>
                <th>{{url}}</th>
                <td>URL to set password</td>
            </tr>
            <tr>
                <th>{{tagurl}}</th>
                <td>URL to view tagged applications</td>
            </tr>
            <tr>
                <th>{{username}}</th>
                <td>WordPress username</td>
            </tr>
        </table>
        <p>For tags of existing users:</p>
        <table class="form-table"><tbody>
            <tr>
                <th>{{name}}</th>
                <td>User's full name</td>
            </tr>
            <tr>
                <th>{{tagurl}}</th>
                <td>URL to view tagged applications</td>
            </tr>
        </table>
        <p>For the admin notification:</p>
        <table class="form-table"><tbody>
            <tr>
                <th>{{role}}</th>
                <td>Role of invited user</td>
            </tr>
            <tr>
                <th>{{username}}</th>
                <td>Username of invited user</td>
            </tr>
            <tr>
                <th>{{email}}</th>
                <td>Email address of invited user</td>
            </tr>
        </table>
        <?php
    }

    public static function options_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
     
        settings_errors( 'sch_messages' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields
                settings_fields( 'sch_options' );

                // output setting sections and their fields
                do_settings_sections( 'sch_options' );

                submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }
}
