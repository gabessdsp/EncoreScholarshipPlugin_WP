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

class Recommendation extends PostWrapper {
    public static function form() {

        $member = new Staff(wp_get_current_user());
        $post = get_post((int) $_GET['application'] );
        if (null === $post ) {
            return;
        }
        $application = new Application($post );
        $staff = $application->get_meta('staff');
        if (! in_array((int) $member->ID, $staff )) {
            return;
        }

        $student = new Student(new \WP_User((int) $application->post_author ));
        $student_fullname = $student->get('first_name') . ' ' . $student->get('last_name');
        $picture = $student->get_meta('picture');

        $recommendation = $application->recommendation_by($member );
        $recommendation_value = false === $recommendation
            ? ''
            : $recommendation->post_content;

        if (false === $recommendation ) {
            $questions = array();
        } else {
            $questions = $recommendation->get_meta('questions');
        }

        ?>
        <div id="poststuff" class="wrap">
            <form id="post" method="POST" action="admin-post.php?action=sch-recommend"><!-- id for compatibility with post-new.php -->
                <input type="hidden" name="action" value="sch-recommend">
                <input type="hidden" name="application" value="<?php echo esc_attr($application->ID ); ?>">
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('sch-recommend-' . $application->ID ); ?>">
                <h1><?php if (false === $recommendation ) { ?>Recommend This Application<?php } else { ?>Edit Your Recommendation<?php } ?></h1>
                <p>Review the student's information and application:</p>
                <div id="post-body" class="metabox-holder columns-2" style="margin-top: 20px;">
                    <div id="post-body-content" class="postarea wp-editor-expand">
                        <div class="meta-box-sortables">
                            <div class="postbox">
                                <h2 class="hndle" style="cursor: default;"><span>Career Goals</span></h2>
                                <div class="inside" id="application-content"><?php echo $application->get_meta('goals'); ?></div>
                            </div>
                            <div class="postbox">
                                <h2 class="hndle" style="cursor: default;"><span>High School Theatre Experience</span></h2>
                                <div class="inside" id="application-content"><?php echo $application->post_content; ?></div>
                            </div>
                        </div>
                        <h3>Verify and recommend the student below:</h3>
                        <table class="form-table">
                            <tbody>
                                <?php
                                foreach (array(
                                    array(
                                        'text', 'position',
                                        'What is your position as a staff member?',
                                    ),
                                    array(
                                        'checkbox', 'performer',
                                        'Is the student a performer?',
                                    ),
                                    array(
                                        'checkbox', 'pit_performer',
                                        'Is the student a pit performer?',
                                    ),
                                    array(
                                        'checkbox', 'truthful',
                                        'To the best of your knowledge, has the student been truthful with their application so far?',
                                    ),
                                    array(
                                        'number', 'shows',
                                        'How many shows has the student participated in?',
                                    ),
                                    array(
                                        'number', 'available',
                                        'How many shows are/were available to the student?',
                                    ),
                                ) as $input ) {
                                    list($type, $name, $label ) = $input;
                                    Util::table_input(
                                        $type,
                                        $name,
                                        $label,
                                        Util::get($questions, $name )
                                    );
                                }
                                ?>
                            </tbody>
                        </table>
                        <p>Rate the student in each of the following categories:</p>
                        <table class="form-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <td>Below Average</td>
                                    <td>Average</td>
                                    <td>Above Average</td>
                                    <td>Outstanding (Top 10%)</td>
                                    <td>One of the top few I've encountered (Top 1%)</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (array(
                                    'Creativity' => 'creativity',
                                    'Respect accorded by faculty' => 'respect',
                                    'Disciplined work habits/Follow through' => 'work_ethic',
                                    'Maturity' => 'maturity',
                                    'Motivation' => 'motivation',
                                    'Leadership' => 'leadership',
                                    'Integrity' => 'integrity',
                                    'Reaction to setbacks' => 'setbacks',
                                    'Concern for others' => 'concern',
                                    'Self-confidence' => 'self_confidence',
                                    'Initiative/Independence' => 'initiative',
                                ) as $label => $name ) {
                                    Util::table_input(
                                        'range',
                                        $name,
                                        $label,
                                        Util::get($questions, $name )
                                    );
                                }
                                ?>
                            </tbody>
                        </table>
                        <p>Please write, in less than 100 words, whatever you think is important about this student. We welcome information that will help us to differentiate this student from others.</p>
                        <?php wp_editor($recommendation_value, 'sch_recommendation'); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div id="side-sortables" class="meta-box-sortables">
                            <?php $student->meta_box(); ?>
                            <?php $application->meta_box(); ?>
                            <div class="postbox">
                                <h2 class="hndle" style="cursor: default;"><span>Submit Recommendation</span></h2>
                                <div class="inside">
                                    <div class="submitbox" id="submitpost">
                                        <?php submit_button('Save Recommendation', 'primary', 'submit', false, array('style' => 'width: 100%;')); ?>
                                    </div>
                                </div>
                                <p><a href="https://yorkencoreawards.com/help/" target="_blank" class="needhelp">
                        Need help? Click here.</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    public static function post() {
        $member = wp_get_current_user();
        if (! wp_verify_nonce($_POST['_wpnonce'], 'sch-recommend-'
            . $_POST['application'] ) || ! $member->exists()
            || ! $member->has_cap('staff')) {
            die('Unauthorized request');
        }

        $appid = (int) $_POST['application'];

        $application = get_post($appid );
        if (null === $application ) {
            return;
        }
        $application = new Application($application );
        $staff = $application->get_meta('staff');
        if (! in_array((int) $member->ID, $staff )) {
            return;
        }

        $recommendation = array(
            'post_type' => 'sch_recommendation',
            'post_status' => 'publish',
            'post_author' => $member->ID,
        );

        foreach (get_posts(array(
            'numberposts' => -1,
            'author' => $member->ID,
            'post_type' => 'sch_recommendation'
        )) as $p ) {
            $p = new Recommendation($p );
            // This cast seems necessary even though the appid is stored as an int...
            if ((int) $p->get_meta('sch_application') === $appid ) {
                $recommendation['ID'] = $p->ID;
                break;
            }
        }

        $questions = array();
        $questions['position'] = trim(Util::get($_POST, 'position'));
        foreach (array(
            'performer',
            'pit_performer',
            'truthful',
        ) as $checkbox ) {
            $questions[ $checkbox ] = ('yes' === Util::get($_POST, $checkbox ));
        }
        foreach (array(
            'shows',
            'available',
            'creativity',
            'respect',
            'work_ethic',
            'maturity',
            'motivation',
            'leadership',
            'integrity',
            'setbacks',
            'concern',
            'self_confidence',
            'initiative',
        ) as $number ) {
            $questions[ $number ] = intval(Util::get($_POST, $number, 0 ));
        }

        $recommendation['post_content'] = strip_shortcodes(
            str_ireplace(
                '<!--more-->', '',
                wp_kses_post($_POST['sch_recommendation'] )
            )
        );
        $recid = wp_insert_post($recommendation );
        update_post_meta($recid, 'sch_application', $appid );
        update_post_meta($recid, 'questions', $questions );

        if (Options::get('sch_enabled', 'staff')) {
            wp_redirect(admin_url('admin.php?page=sch-tagged&sch_saved=true'));
        } else {
            // same deal as with the students
            wp_logout();
            wp_redirect(wp_login_url() . '?sch_saved=true');
        }
    }

    public function application() {
        return new Application(get_post((int) get_post_meta($this->ID, 'sch_application', true )) );
    }
}
