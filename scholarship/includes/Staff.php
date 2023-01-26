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

class Staff extends UserWrapper {
    public static function tagged_applications() {

        $staff = new Staff(wp_get_current_user());

        $tagged = $staff->tagged();
        $recommended = $staff->recommendations();
        
    ?>

        <div class="wrap">
            <h1>Tagged Applications</h1>
            <?php if (isset($_GET['sch_saved'] )) { ?>
                <div class="updated notice">
                    <p>Recommendation saved.</p>
                </div>
            <?php } ?>
            <?php if (0 === count($tagged )) { ?>
                <p><em>You have not been tagged in any applications. Come back later!</em></p>
            <?php } else { ?>
                <?php if (count($tagged ) > count($recommended )) { ?>
                    <h2>Pending Recommendations</h2>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <td>Student</td>
                                <td>Parent</td>
                                <td>Submitted</td>
                                <td>Modified</td>
                                <td> </td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tagged as $application ) { ?>
                            <?php if (! in_array($application, $recommended )) { ?>
                                <tr>
                                    <td><?php
                                    $student = new Student(new \WP_User((int) $application->post_author ));
                                    echo $student->get('first_name') . ' ' . $student->get('last_name');
                                    ?></td>
                                    <td><?php echo $student->get_meta('parent_name'); ?></td>
                                    <td><?php echo $application->post_date; ?></td>
                                    <td><?php echo $application->post_modified; ?></td>
                                    <td><a href="admin.php?page=sch-recommend&amp;application=<?php echo $application->ID; ?>">Recommend</a></td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <?php if (0 !== count($recommended )) { ?>
                    <h2>Completed Recommendations</h2>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <td>Student</td>
                                <td>Parent</td>
                                <td>Submitted</td>
                                <td>Modified</td>
                                <td> </td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recommended as $application ) { ?>
                            <tr>
                                <td><?php
                                $student = new Student(new \WP_User((int) $application->post_author ));
                                echo $student->get('first_name') . ' ' . $student->get('last_name');
                                ?></td>
                                <td><?php echo $student->get_meta('parent_name'); ?></td>
                                <td><?php echo $application->post_date; ?></td>
                                <td><?php echo $application->post_modified; ?></td>
                                <td><a href="admin.php?page=sch-recommend&amp;application=<?php echo $application->ID; ?>">Edit recommendation</a></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            <?php } ?>
        </div>
    <?php
    }

    public function tagged() {
        $tagged = array();
        foreach (get_posts(array(
            'numberposts' => -1,
            'post_type' => 'sch_application'
        ))  as $application ) {
            $application = new Application($application );
            $staff = $application->get_meta('staff');
            if (in_array((int) $this->ID, $staff )) {
                $tagged[] = $application;
            }
        }
        return $tagged;
    }
    public function recommendations() {
        $recommended = array();
        foreach (get_posts(array(
            'numberposts' => -1,
            'post_type' => 'sch_recommendation',
            'author' => $this->ID
        )) as $recommendation ) {
            $recommendation = new Recommendation($recommendation );
            $recommended[] = $recommendation->application();
        }
        return $recommended;
    }
}
