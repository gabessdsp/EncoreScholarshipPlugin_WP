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

class Counselor extends UserWrapper {
    public static function transcripts() {
        $counselor = new Counselor(wp_get_current_user());

        $requested = array();
        foreach (get_posts(array(
            'numberposts' => -1,
            'post_type' => 'sch_application'
        ))  as $application ) {
            $application = new Application($application );
            $appcounselor = $application->get_meta('counselor');
            if ((int) $counselor->ID === (int) $appcounselor ) {
                $requested[] = $application;
            }
        }

        $uploaded = $counselor->get_meta('transcripts');
        if (! is_array($uploaded )) {
            $uploaded = array();
        }
        ?>

        <div class="wrap">
            <h1>Transcripts</h1>
            <?php if (isset($_GET['sch_saved'] )) { ?>
                <div class="notice updated">
                    <p>Transcript uploaded.</p>
                </div>
            <?php } ?>
            <?php if (0 === count($requested )) { ?>
                <p><em>No students have requested a transcript from you. Come back
                later!</em></p>
            <?php } else { ?>
                <?php if (count($requested ) > count($uploaded )) { ?>
                    <h2>Requested Transcripts</h2>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <td>Student</td>
                                <td>Parent</td>
                                <td>High School</td>
                                <td>Requested</td>
                                <td> </td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($requested as $application ) { ?>
                            <?php if (! isset($uploaded[ (int) $application->ID ] )) { ?>
                                <tr>
                                    <td><?php
                                    $student = $application->student();
                                    echo $student->get('first_name') . ' ' . $student->get('last_name');
                                    ?></td>
                                    <td><?php echo $student->get_meta('parent_name'); ?></td>
                                    <td><?php
                                    $highschool = $application->get_meta('highschool');
                                    if (! empty($highschool )) {
                                        echo $highschool;
                                    } else {
                                        echo '<em>Not specified</em>';
                                    }
                                    ?></td>
                                    <td><?php echo $application->post_date; ?></td>
                                    <td><a href="admin.php?page=sch-upload-transcript&amp;application=<?php echo $application->ID; ?>">Upload Transcript</a></td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <?php if (0 < count($uploaded )) { ?>
                    <h2>Uploaded Transcripts</h2>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <td>Student</td>
                                <td>Parent</td>
                                <td>High School</td>
                                <td>Requested</td>
                                <td> </td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($uploaded as $appid => $file ) {
                            $application = new Application(get_post($appid ));
                            ?>
                            <tr>
                                <td><?php
                                $student = new Student(new \WP_User((int) $application->post_author ));
                                echo $student->get('first_name') . ' ' . $student->get('last_name');
                                ?></td>
                                <td><?php echo $student->get_meta('parent_name'); ?></td>
                                <td><?php
                                $highschool = $application->get_meta('highschool');
                                if (! empty($highschool )) {
                                    echo $highschool;
                                } else {
                                    echo '<em>Not specified</em>';
                                }
                                ?></td>
                                <td><?php echo $application->post_date; ?></td>
                                <td><a href="admin.php?page=sch-upload-transcript&amp;application=<?php echo $application->ID; ?>">Reupload Transcript</a></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            <?php } ?>
        </div>
        <?php
    }
    public static function upload_transcript() {
        $counselor = new Counselor(wp_get_current_user());
        $application = get_post((int) $_GET['application'] );
        if (null === $application ) {
            return;
        }
        $application = new Application($application );

        if ((int) $application->get_meta('counselor') !== (int) $counselor->ID ) {
            return;
        }

        $student = $application->student();
        $transcripts = $counselor->get_meta('transcripts');
        if (is_array($transcripts )) {
            if (isset($transcripts[ (int) $application->ID ] )) {
                $uploaded = $transcripts[ (int) $application->ID ];
            }
        }
        ?>
        <div id="poststuff" class="wrap">
            <h1>Transcript upload</h1>
            <div id="post-body" class="metabox-holder columns-2" style="margin-top: 20px;">
                <div id="post-body-content" class="postarea wp-editor-expand">
                    <form method="POST" action="admin-post.php?action=sch-upload" enctype="multipart/form-data">
                        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('sch-upload-' . $application->ID )); ?>">
                        <input type="hidden" name="action" value="sch-upload">
                        <input type="hidden" name="application" value="<?php echo $application->ID; ?>">
                        <h3>Upload a Word document (.doc, .docx) or PDF file (.pdf) of <?php echo $student->get('first_name'); ?>'s transcript.</h3>
                        <p><a href="https://yorkencoreawards.com/help/" target="_blank" class="needhelp">
                        Need help? Click here.</a></p>
                        <?php if (isset($uploaded )) { ?>
                            <p>You have already uploaded a transcript for this student. You can <a href="<?php echo esc_attr($uploaded['url'] ); ?>">download it</a> or upload a different transcript to replace it.</p>
                            <p><a href="https://yorkencoreawards.com/help/" target="_blank" class="needhelp">
                        Need help? Click here.</a></p>
                        <?php } ?>
                        <p><input required type="file" name="transcript" accept=".pdf,.doc,.docx"></p>
                        <p><input class="button button-primary button-large" type="submit" value="Upload"></p>
                    </form>
                </div>
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables">
                        <?php
                        $student->meta_box();
                        $application->meta_box();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    public static function upload_post() {
        $counselor = new Counselor(wp_get_current_user());
        if (
            ! wp_verify_nonce(
                $_POST['_wpnonce'],
                'sch-upload-' . $_POST['application']
            )
            || ! $counselor->exists()
            || ! $counselor->has_cap('counselor')
        ) {
            die('Unauthorized request');
        }

        $application = get_post((int) $_POST['application'] );
        if (null === $application ) {
            wp_redirect(admin_url('admin.php?page=sch-transcripts'));
            die();
        }
        $application = new Application($application );
        if ((int) $application->get_meta('counselor')
            !== (int) $counselor->ID
        ) {
            wp_redirect(admin_url('admin.php?page=sch-transcripts'));
            die();
        }

        if (! isset($_FILES['transcript'] )) {
            wp_redirect(
                admin_url(
                    'admin.php?page=sch-upload-transcript&application='
                    . $_GET['application']
                )
            );
            die();
        }

        $file = wp_handle_upload(
            $_FILES['transcript'],
            array('test_form' => false, 'action' => 'sch-upload')
        );
        if (isset($file['error'] )) {
            wp_redirect(
                admin_url(
                    'admin.php?page=sch-upload-transcript&application='
                    . $_GET['application']
                )
            );
            die();
        }

        $transcripts = $counselor->get_meta('transcripts');
        if (! is_array($transcripts )) {
            $transcripts = array();
        }

        if (isset($transcripts[ (int) $application->ID ] )) {
            @unlink($transcripts[ (int) $application->ID ]['file'] );
        }

        $transcripts[ (int) $application->ID ] = $file;
        $counselor->set_meta('transcripts', $transcripts );


        if (Options::get('sch_enabled', 'counselor')) {
            wp_redirect(admin_url(
                'admin.php?page=sch-transcripts&sch_saved=true'));
        } else { // same deal as with the students
            wp_logout();
            wp_redirect(wp_login_url() . '?sch_saved=true');
        }
    }

    public function transcript_for(Application $application ) {
        $transcripts = $this->get_meta('transcripts');
        if (is_array($transcripts )
            && isset($transcripts[ (int) $application->ID ] )) {
            return $transcripts[ (int) $application->ID ];
        }
        return false;
    }
}
