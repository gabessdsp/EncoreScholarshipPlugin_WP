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

class Student extends UserWrapper {
    public static function register($student ) {
        if ('register' !== $_GET['action'] ) {
            return;
        }

        $first_name = trim(sanitize_text_field($_POST['user_first_name'] ));
        $last_name = trim(sanitize_text_field($_POST['user_last_name'] ));
        $tel = Util::normalize_tel($_POST['user_tel'] );
        $gender = trim(sanitize_text_field($_POST['user_gender'] ));
        $address = trim(sanitize_text_field($_POST['user_address'] ));
        $city = trim(sanitize_text_field($_POST['user_city'] ));
        $state = Util::validate_state($_POST['user_state'] );
        $zip = Util::validate_zip($_POST['user_zip'] );
        $dob = Util::parse_date(trim($_POST['user_dob'] ));

        $parent_email = trim(sanitize_email($_POST['user_parent_email'] ));
        $parent_name = trim(sanitize_text_field($_POST['user_parent_name'] ));
        $parent_tel = Util::normalize_tel($_POST['user_parent_tel'] );


        update_user_meta($student, 'first_name', $first_name, '');
        update_user_meta($student, 'last_name', $last_name, '');
        add_user_meta($student, 'tel', $tel, true );
        add_user_meta($student, 'address', $address, true );
        add_user_meta($student, 'city', $city, true );
        add_user_meta($student, 'state', $state, true );
        add_user_meta($student, 'zip', $zip, true );
        add_user_meta($student, 'dob', $dob, true );
        add_user_meta($student, 'gender', $gender, true );
        add_user_meta($student, 'parent_email', $parent_email, true );
        add_user_meta($student, 'parent_name', $parent_name, true );
        add_user_meta($student, 'parent_tel', $parent_tel, true );
        add_user_meta($student, 'picture', Util::$data['picture'], true );

        $user = new \WP_User($student );
        $user->set_role('student');
    }
    public static function update($student ) {
        if ('update' !== $_POST['action'] ) {
            return;
        }

        $user = new \WP_User($student );
        if (! $user->has_cap('student')) {
            return;
        }
        $user = new Student($user );

        $tel = Util::normalize_tel($_POST['tel'] );
        $gender = trim(sanitize_text_field($_POST['gender'] ));
        $dob = Util::parse_date(trim(sanitize_text_field($_POST['dob'] )) );
        $address = trim(sanitize_text_field($_POST['address'] ));
        $city = trim(sanitize_text_field($_POST['city'] ));
        $state = Util::validate_state($_POST['state'] );
        $zip = Util::validate_zip($_POST['zip'] );

        $parent_email = trim(sanitize_email($_POST['parent_email'] ));
        $parent_name = trim(sanitize_text_field($_POST['parent_name'] ));
        $parent_tel = Util::normalize_tel($_POST['parent_tel'] );

        $user->set_meta('tel', $tel );
        $user->set_meta('gender', $gender );
        $user->set_meta('dob', $dob );
        $user->set_meta('address', $address );
        $user->set_meta('city', $city );
        $user->set_meta('state', $state );
        $user->set_meta('zip', $zip );
        $user->set_meta('parent_email', $parent_email );
        $user->set_meta('parent_name', $parent_name );
        $user->set_meta('parent_tel', $parent_tel );

        if (isset(Util::$data['picture'] )) {
            $old_picture = $user->get_meta('picture');
            @unlink($old_picture['file'] );
            $user->set_meta('picture', Util::$data['picture'] );
        }
    }

    public function application() {
        $posts = get_posts(array(
            'numberposts' => 1,
            'post_type' => 'sch_application',
            'author' => $this->user->ID
        ));
        if (count($posts ) !== 1 ) { 
            return false;
        }
        return new Application($posts[0] );
    }
    public function meta_box() {
        $student_fullname = $this->get('first_name') . ' ' . $this->get('last_name');
        $picture = $this->get_meta('picture');
        ?>

        <div class="postbox">
            <h2 class="hndle" style="cursor: default;">
                <span><?php echo htmlentities($student_fullname ); ?></span>
            </h2>
            <div class="inside">
                <div class="studentbox">
                    <p style="text-align: center;">
                        <img style="max-width: 100%; max-height: 300px;"
                            alt="<?php echo esc_attr($student_fullname ); ?>"
                            src="<?php echo esc_attr($picture['url'] ); ?>">
                    </p>
                    <p>
                    <?php foreach (array(
                        'gender' => 'Gender',
                        'dob' => 'Date of birth',
                        'parent_name' => "Parent's name",
                    ) as $attr => $display ) { ?>
                        <strong><?php echo $display; ?>:</strong>
                        <?php echo htmlentities($this->get_meta($attr )); ?><br />
                    <?php } ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}
