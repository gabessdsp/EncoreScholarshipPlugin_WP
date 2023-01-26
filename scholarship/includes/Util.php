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

class Util {
    public static $data = array();
    public static function noop() {}
    public static function normalize_tel($tel ) {
        return preg_replace('/[^0-9]/', '', $tel );
    }
    public static function format_tel($tel ) {
        return sprintf('(%s) %s-%s', substr($tel, 0, 3 ),
            substr($tel, 3, 3 ), substr($tel, 6 ));
    }
    public static function parse_date($date ) {
        $a = explode('-', $date );
        if ( count($a ) !== 3 ) {
            return false;
        }

        list($year, $month, $day ) = $a;
        if ( strlen($year ) !== 4 || strlen($month ) !== 2 ||
            strlen($day ) !== 2 ) {
            return false;
        }

        $year = (int) $year;
        $month = (int) $month;
        $day = (int) $day;
        if ( 1900 > $year || $year > (int) date('Y') ||
            1 > $month || $month > 12 || 1 > $day || $day > 31 ) {
            return false;
        }

        return $date;
    }
    public static $state_array = array(
        'al' => 'Alabama',
        'ak' => 'Alaska',
        'az' => 'Arizona',
        'ar' => 'Arkansas',
        'ca' => 'California',
        'co' => 'Colorado',
        'ct' => 'Connecticut',
        'de' => 'Delaware',
        'fl' => 'Florida',
        'ga' => 'Georgia',
        'hi' => 'Hawaii',
        'id' => 'Idaho',
        'il' => 'Illinois',
        'in' => 'Indiana',
        'ia' => 'Iowa',
        'ks' => 'Kansas',
        'ky' => 'Kentucky',
        'la' => 'Louisiana',
        'me' => 'Maine',
        'md' => 'Maryland',
        'ma' => 'Massachusetts',
        'mi' => 'Michigan',
        'mn' => 'Minnesota',
        'ms' => 'Mississippi',
        'mo' => 'Missouri',
        'mt' => 'Montana',
        'ne' => 'Nebraska',
        'nv' => 'Nevada',
        'nh' => 'New Hampshire',
        'nj' => 'New Jersey',
        'nm' => 'New Mexico',
        'ny' => 'New York',
        'nc' => 'North Carolina',
        'nd' => 'North Dakota',
        'oh' => 'Ohio',
        'ok' => 'Oklahoma',
        'or' => 'Oregon',
        'pa' => 'Pennsylvania',
        'ri' => 'Rhode Island',
        'sc' => 'South Carolina',
        'sd' => 'South Dakota',
        'tn' => 'Tennessee',
        'tx' => 'Texas',
        'ut' => 'Utah',
        'vt' => 'Vermont',
        'va' => 'Virginia',
        'wa' => 'Washington',
        'wv' => 'West Virginia',
        'wi' => 'Wisconsin',
        'wy' => 'Wyoming',
    );
    public static function state_select($name, $id,
        $class = null, $state = null ) {
    ?>

    <select name="<?php echo $name; ?>" id="<?php echo $id; ?>"<?php
    if ( null !== $class ) { 
        ?>class="<?php echo $class; ?>"<?php
    }
    ?>>
        <?php
        foreach ( self::$state_array as $code => $statename ) {
        ?>
            <option value="<?php echo $code; ?>"<?php
            if ($state === $code ) {
                ?> selected<?php
            }
            ?>><?php echo $statename; ?></option>
        <?php
        }
        ?>
    </select>
    <?php }

    public static function format_string($string, $format ) {
        foreach ($format as $needle => $repl ) {
            $string = str_replace('{{' . $needle . '}}', $repl, $string );
        }
        return $string;
    }

    public static function validate_zip($input ) {
        $input = trim($input );
        if ( preg_match('/^[0-9]{5}(-[0-9]{4})?$/', $input )) {
            return substr($input, 0, 5 );
        }
        return false;
    }

    public static function validate_state($input ) {
        $input = trim($input );
        if ( isset( self::$state_array[ $input ] )) {
            return $input;
        }
        return false;
    }

    public static function register_link($link ) {
        if ( ! Options::get('sch_enabled', 'student')) {
            if ( ! is_user_logged_in()) {
                // disable the registration link if applications are disabled
                return '';
            }
        }
        return $link;
    }

    public static function activate() {
        add_role(
            'student',
            'Student',
            array(
                'read' => true,
                'read_sch_applications' => true,
                'edit_sch_applications' => true,
            )
        );
        add_role(
            'staff',
            'Staff',
            array(
                'read' => true,
                'read_sch_recommendations' => true,
                'edit_sch_recommendations' => true,
                'read_sch_applications' => true,
            )
        );
        add_role(
            'judge',
            'Judge',
            array(
                'read' => true,
                'read_sch_applications' => true,
                'read_private_sch_applications' => true,
                'read_sch_recommendations' => true,
                'read_private_sch_recommendations' => true,
            )
        );
        add_role(
            'counselor',
            'Guidance Counselor',
            array(
                'read' => true,
                'upload_transcripts' => true,
            )
        );
    }
    public static function deactivate() {
        remove_role('student');
        remove_role('staff');
        remove_role('judge');
        remove_role('counselor');
    }

    public static function init() {
        register_post_type('sch_application', array(
            'label' => 'Application',
            'pubic' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'capability_type' => array(
                'sch_application',
                'sch_applications',
            ),
            // author is not supported by default
            'supports' => array('editor', 'author'),
        ));
        register_post_type('sch_recommendation', array(
            'label' => 'Recommendation',
            'public' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'capability_type' => array(
                'sch_recommendation',
                'sch_recommendations',
            ),
            'supports' => array('editor', 'author'),
        ));

        // add_shortcode('sch-application', '\Scholarship\Shortcode::shortcode');
    }

    public static function delete_image($id ) {
        $user = new Student( new \WP_User($id ));
        if ( ! $user->has_cap('student')) {
            return;
        }
        $pic = $user->get_meta('picture');
        @unlink($pic['file'] );
    }

    public static function get($array, $key, $default = null ) {
        if ( isset($array[ $key ] )) {
            $default = $array[ $key ];
        }
        return $default;
    }

    public static function table_input($type, $name, $label, $value ) {
        if ('range' === $type ) {
            ?>
            <tr>
                <th><?php echo $label; ?></th>
                <?php foreach ( range( 1, 5 ) as $i ) { ?>
                    <td><input type="radio"
                        name="<?php echo esc_attr($name ); ?>"
                        value="<?php echo $i; ?>"<?php
                        if ( intval($value ) === $i ) {
                            echo ' checked';
                        }
                    ?>>
                </td>
                <?php } ?>
            </tr>
            <?php
        } elseif ('checkbox' === $type ) {
            ?>
            <tr>
                <th><label for="<?php echo esc_attr($name ); ?>"><?php echo $label; ?></label></th>
                <td><input type="checkbox"
                    name="<?php echo esc_attr($name ); ?>"
                    id="<?php echo esc_attr($name ); ?>"
                    value="yes"<?php
                        if ( true === $value ) {
                            echo ' checked';
                        }
                    ?>>
                </td>
            </tr>
            <?php
        } else {
            ?>
            <tr>
                <th><label for="<?php echo esc_attr($name ); ?>"><?php echo $label; ?></label></th>
                <td><input type="text" class="regular-text"
                    name="<?php echo esc_attr($name ); ?>"
                    id="<?php echo esc_attr($name ); ?>"<?php
                        if ( null !== $value ) {
                            echo ' value="';
                            echo esc_attr($value );
                            echo '"';
                        }
                    ?>>
                </td>
            </tr>
            <?php
        }
    }

}
