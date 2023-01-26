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

class Shortcode {
    public static function shortcode($attrs = array(), $content = null ) {
        if (! current_user_can('student')) {
            if (null !== $content ) {
                return do_shortcode(apply_filters('the_content', $content ));
            }
        }
        $student = new Student(wp_get_current_user());
        $application = $student->application();
        if (false === $application ) {
            $goals = '';
            $essay = '';
        } else {
            $goals = $application->get_meta('goals');
            if (! $goals ) {
                $goals = '';
            }
            $essay = $application->post_content;
        }

        ob_start();
        ?>
        <form method="POST" action="<?php
            echo admin_url('admin-post.php?action=sch-apply');
        ?>">
            <input type="hidden" name="action" value="sch-apply">
            <input type="hidden" name="_wpnonce" value="<?php
                echo wp_create_nonce('sch-apply'); 
            ?>">
            <h2>What are your career goals? They are not required to be
            related to theatre.</h2>
            <?php wp_editor($goals, 'sch_application_goals'); ?>
            <h2>In the length of 250-500 words, describe how your high school
            theatre experience has impacted your life.</h2>
            <?php wp_editor($essay, 'sch_application_essay'); ?>
            <p><input type="submit" value="Save and submit"></p>
        </form>
        <?php
        return ob_get_clean();
    }
}