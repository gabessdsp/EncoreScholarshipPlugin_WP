<?php
namespace Scholarship;
defined( 'ABSPATH' ) || die( 'this file requires wordpress core' );

class Admin {
    public static function all_applications() {
        $staff = array();
        $is_admin = current_user_can( 'manage_options' );
        ?>
        <div class="wrap">
            <h1>
                All Applications
                <?php if ( $is_admin ) { ?>
                    |
                    <a style="color: #FF0000;"
                        href="admin.php?page=sch-delete-application-all&amp;_wpnonce=<?php
                            echo wp_create_nonce( 'sch-delete-application-all' );
                        ?>">Delete all</a>
                <?php
            }
            ?>
            </h1>
            <?php
            $applications = get_posts( array(
                'numberposts' => -1,
                'post_type' => 'sch_application'
            ) );
            if ( count( $applications ) > 0 ) {
                ?>
                <table class="wp-list-table widefat fixed striped posts" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <td>Student</td>
                            <td>School</td>
                            <td>Parent</td>
                            <td>Recommended by</td>
                            <td>Submitted</td>
                            <td>Modified</td>
                            <td>Transcript</td>
                            <td> </td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ( $applications as $application ) {
                        $application = new Application( $application );
                        $student = $application->student();
                        ?>
                        <tr>
                            <td><?php
                                echo htmlspecialchars( $student->get( 'first_name' ) ) . ' ' .
                                    htmlspecialchars( $student->get( 'last_name' ) );
                            ?></td>
                            <td><?php
                                $highschool = $application->get_meta( 'highschool' );
                                echo empty( $highschool )
                                    ? '<em>Not specified</em>'
                                    : htmlspecialchars( $highschool );
                            ?></td>
                            <td><?php
                                echo htmlspecialchars( $student->get_meta( 'parent_name' ) );
                            ?></td>
                            <td><?php
                            $staff_names = array();
                                foreach ( $application->get_meta( 'staff' ) as $member ) {
                                    if ( ! isset( $staff[ $member ] ) ) {
                                        $s = new Staff( new \WP_User( $member ) );
                                        $staff[ $member ] = array(
                                            'applications' => array(),
                                            'name' => $s->get( 'display_name' ),
                                        );
                                        foreach ( get_posts( array(
                                            'numberposts' => -1,
                                            'author' => $member,
                                            'post_type' => 'sch_recommendation',
                                        ) ) as $recommendation ) {
                                            $recommendation = new Recommendation( $recommendation );
                                            $staff[ $member ]['applications'][] = (int) (
                                                $recommendation->get_meta( 'sch_application' )
                                            );
                                        }
                                    }
                                    if ( in_array( (int) $application->ID,
                                        $staff[ $member ]['applications'] ) ) {
                                        $staff_names[] = $staff[ $member ]['name'];
                                    }
                                }
                                echo htmlspecialchars( implode( ', ',
                                    $staff_names ) );
                            ?></td>
                            <td><?php echo $application->post_date; ?></td>
                            <td><?php echo $application->post_modified; ?></td>
                            <td><?php
                            $counselor = $application->counselor();
                            if ( $counselor ) {
                                $transcript = $counselor->transcript_for( $application );
                                echo $transcript
                                    ? '<a href="' . esc_attr( $transcript['url'] ) . '">Uploaded</a>'
                                    : 'Not uploaded';
                            } else {
                                echo 'No counselor';
                            }
                            ?>
                            <td>
                                <a href="admin.php?page=sch-render<?php
                                    if ( $is_admin ) {
                                        echo '-admin';
                                    }
                                    ?>&amp;application=<?php
                                        echo $application->ID;
                                    ?>">View</a>
                                <?php
                                if ( $is_admin ) {
                                    ?>
                                    |
                                    <a style="color: #FF0000;"
                                        href="admin.php?page=sch-delete-application&amp;application=<?php
                                        echo $application->ID;
                                        ?>&amp;_wpnonce=<?php
                                            echo wp_create_nonce( 'sch-delete-application-' .
                                            $application->ID );
                                        ?>">Delete</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <?php
            } else {
                ?>
                <p><em>No applications.</em></p>
                <?php
            }
            ?>
        </div>
        <?php
    }
    public static function delete_application() {
        $application = get_post( (int) $_GET['application'] );
        if ( null === $application ||
            ! wp_verify_nonce(
                $_GET['_wpnonce'],
                'sch-delete-application-' . $application->ID
            ) ) { 
            ?>
            <h1>Invalid request</h1>
            <p><a href="admin.php?page=sch-all-applications-admin">Click here to
            view all applications.</a></p>
            <?php
            return;
        }

        $student = new Student( new \WP_User( (int) $application->post_author ) );

        ?>

        <h1>Delete Application</h1>
        <form action="admin-post.php?action=sch-delete-application" method="POST">
            <input type="hidden" name="application" value="<?php echo $application->ID; ?>">
            <input type="hidden" name="_wpnonce"
            value="<?php
                echo wp_create_nonce( 'sch-really-delete-application-' . $application->ID );
                ?>">
            <p>You have specified <strong><?php
                echo $student->get( 'first_name' ) . ' '
                    . $student->get( 'last_name' );
            ?>'s</strong> application for deletion.</p>
            <p>Note that this will also delete any associated recommendations or
            transcripts. Any invited users will remain, but can be deleted
            separately.</p>
            <p class="submit">
                <input type="submit" id="submit" class="button button-primary"
                    value="Confirm Deletion">
            </p>
        </form>
        <?php
    }
    public static function delete_application_post() {
        $application = get_post( (int) $_POST['application'] );
        if ( null !== $application && wp_verify_nonce( $_POST['_wpnonce'],
            'sch-really-delete-application-' . $application->ID ) ) {
            $application = new Application( $application );
            // delete any recommendations
            foreach ( $application->recommendations() as $rec ) {
                wp_delete_post( $rec->ID );
            }

            $counselor = new \WP_User( (int) $application->get_meta( 'counselor' ) );
            // delete any uploaded transcript
            if ( null !== $counselor && $counselor->exists() ) {
                $counselor = new Counselor( $counselor );
                $transcripts = $counselor->get_meta( 'transcripts' );
                if ( is_array( $transcripts ) &&
                    isset( $transcripts[ (int) $application->ID ] ) ) {
                    @unlink( $transcripts[ (int) $application->ID ]['file'] );
                    unset( $transcripts[ (int) $application->ID ] );
                    $counselor->set_meta( 'transcripts', $transcripts );
                }
            }

            wp_delete_post( $application->ID, true ); // finally, delete the application.
        }

        // since this is a destructive action, fail silently.
        wp_redirect( admin_url( 'admin.php?page=sch-all-applications-admin' ) );
    }
    public static function delete_application_all() {
        ?>

        <h1>Delete Application</h1>
        <form action="admin-post.php?action=sch-delete-application-all" method="POST">
            <input type="hidden" name="_wpnonce"
                value="<?php echo wp_create_nonce(
                    'sch-really-delete-application-all' ); ?>">
            <p>You are about to delete <strong>all applications</strong>.</p>
            <p>Note that this will also delete any associated recommendations or
            transcripts. Any invited users will remain, but can be deleted
            separately.</p>
            <p class="submit">
                <input type="submit" id="submit" class="button button-primary"
                    value="Confirm Deletion">
            </p>
        </form>
        <?php
    }
    public static function delete_application_all_post() {
        if ( wp_verify_nonce( $_POST['_wpnonce'],
            'sch-really-delete-application-all' ) ) {
            foreach ( get_posts( array(
                'numberposts' => -1,
                'post_type' => 'sch_application'
            ) ) as $application ) {
                $application = new Application( $application );
                // delete any recommendations
                foreach ( $application->recommendations() as $rec ) {
                    wp_delete_post( $rec->ID );
                }

                $counselor = $application->counselor();
                // delete any uploaded transcript
                if ( false !== $counselor ) {
                    $transcripts = $counselor->get_meta( 'transcripts' );
                    if ( is_array( $transcripts ) &&
                        isset( $transcripts[ (int) $application->ID ] ) ) {
                        @unlink( $transcripts[ (int) $application->ID ]['file'] );
                        unset( $transcripts[ (int) $application->ID ] );
                        $counselor->set_meta( 'transcripts', $transcripts );
                    }
                }

                // finally, delete the application.
                wp_delete_post( $application->ID, true );
            }
        }

        // since this is a destructive action, fail silently.
        wp_redirect( admin_url( 'admin.php?page=sch-all-applications-admin' ) );
    }
    public static function menu() {
        $user = wp_get_current_user();
        if ( ! $user->exists() ||
            // count_user_posts returns a string????????????
            0 === (int) count_user_posts( $user->ID, 'sch_application' ) ) {
            $string = 'Apply';
        } else {
            $string = 'Edit application';
        }

        // application editor
        add_menu_page(
            'Application',
            $string,
            'edit_sch_applications',
            'sch-application',
            '\Scholarship\Application::form',
            'dashicons-welcome-learn-more'
        );

        // list tagged applications
        add_menu_page(
            'Tagged Applications',
            'Applications',
            'edit_sch_recommendations',
            'sch-tagged',
            '\Scholarship\Staff::tagged_applications',
            'dashicons-welcome-learn-more'
        );
        // recommendation editor
        add_submenu_page(
            // this value prevents a menu item from appearing
            'options.php',
            'Recommend',
            'Recommend',
            'edit_sch_recommendations',
            'sch-recommend',
            '\Scholarship\Recommendation::form'
        );

        // for judges
        // all applications
        add_menu_page(
            'Applications',
            'Applications',
            'read_private_sch_applications',
            'sch-all-applications',
            '\Scholarship\Admin::all_applications',
            'dashicons-welcome-learn-more'
        );
        // render application
        add_submenu_page(
            'options.php',
            'Application',
            'Application',
            'read_private_sch_applications',
            'sch-render',
            '\Scholarship\Application::render'
        );

        // for site admins
        // all applications
        add_menu_page(
            'Applications',
            'Applications',
            'manage_options', // if a user has this capability, they are an admin
            'sch-all-applications-admin',
            '\Scholarship\Admin::all_applications',
            'dashicons-welcome-learn-more'
        );
        // render application
        add_submenu_page(
            'options.php',
            'Application',
            'Application',
            'manage_options',
            'sch-render-admin',
            '\Scholarship\Application::render'
        );

        // confirmation pages
        add_submenu_page(
            'options.php',
            'Delete Application',
            'Delete Application',
            'manage_options',
            'sch-delete-application',
            '\Scholarship\Admin::delete_application'
        );
        add_submenu_page(
            'options.php',
            'Delete All Applications',
            'Delete All Applications',
            'manage_options',
            'sch-delete-application-all',
            '\Scholarship\Admin::delete_application_all'
        );

        // transcript listing
        add_menu_page(
            'Transcripts',
            'Transcripts',
            'upload_transcripts',
            'sch-transcripts',
            '\Scholarship\Counselor::transcripts',
            'dashicons-welcome-learn-more'
        );
        // transcript upload page
        add_submenu_page(
            'options.php',
            'Transcripts',
            'Transcripts',
            'upload_transcripts',
            'sch-upload-transcript',
            '\Scholarship\Counselor::upload_transcript'
        );

        // settings page
        add_options_page(
            // settings and options are mixed. UI seems to use settings
            'Scholarship Applications Settings',
            'Applications',
            'manage_options',
            'sch-options',
            '\Scholarship\Options::options_page'
        );
    }
    public static function application_script( $hook ) {
        if ( 'toplevel_page_sch-application' !== $hook && !
            ( ( 'post.php' === $hook || 'post-new.php' === $hook ) &&
                isset( $_GET['post_type'] ) &&
                'sch_application' === $_GET['post_type'] ) ) {
            return;
        }

        wp_enqueue_style( 'sch-application-css',
            URL::get( '/css/sch_application.css' ) );
        wp_enqueue_script( 'sch-application-script',
            URL::get( '/js/sch_application.js' ), array( 'jquery' ) );
        wp_localize_script( 'sch-application-script', 'wordpress', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'staffnonce' => wp_create_nonce( 'sch_search_staff' ),
            'counselornonce' => wp_create_nonce( 'sch_search_counselors' ),
        ) );
    }
    public static function handle_search_staff() {
        check_ajax_referer( 'sch_search_staff' );
        $response = array();
        if ( preg_match( '/^[^a-zA-Z0-9]$/', $_POST['search'] ) ) {
            wp_send_json( $response );
        }
        foreach ( get_users( 'role=staff' ) as $member ) {
            $member = new Staff( $member );
            $name = $member->get( 'display_name' );
            $email = $member->user_email;
            if ( false !== stripos( $email, $_POST['search'] ) ||
                false !== stripos( $name, $_POST['search'] ) ) {
                $response[] = array( 'name' => $name, 'email' => $email );
            }
        }
        wp_send_json( $response );
    }
    public static function handle_search_counselors() {
        check_ajax_referer( 'sch_search_counselors' );
        $response = array();
        if ( preg_match( '/^[^a-zA-Z0-9]$/', $_POST['search'] ) ) {
            wp_send_json( $response );
        }
        foreach ( get_users( 'role=counselor' ) as $counselor ) {
            $name = $counselor->get( 'display_name' );
            $email = $counselor->user_email;
            if ( false !== stripos( $email, $_POST['search'] ) ||
                false !== stripos( $name, $_POST['search'] ) ) {
                $response[] = array( 'name' => $name, 'email' => $email );
            }
        }
        wp_send_json( $response );
    }


    public static function login_redirect( $redirect, $requested, $user ) {
        if ( ! is_wp_error( $user ) ) {
            foreach ( array( 'student', 'staff', 'counselor', 'judge' ) as $role ) {
                if ( $user->has_cap( $role ) && ( ! Options::get( 'sch_enabled', $role ) ) ) {
                    wp_logout();
                    return wp_login_url() . '?sch_disabled=true';
                }
            }
            if ( $user->has_cap( 'staff' ) ) {
                $redirect = admin_url( 'admin.php?page=sch-tagged' );
            } elseif ( $user->has_cap( 'counselor' ) ) {
                $redirect = admin_url( 'admin.php?page=sch-transcripts' );
            } elseif ( $user->has_cap( 'student' ) ) {
                $redirect = admin_url( 'admin.php?page=sch-application' );
            } elseif ( $user->has_cap( 'judge' ) ) {
                $redirect = admin_url( 'admin.php?page=sch-all-applications' );
            }
        }
        return $redirect;
    }
    public static function login_message( $message ) {
        if ( isset( $_GET['sch_disabled'] ) ) {
            $message = '<div id="login_error"><strong>ERROR</strong>: '
                . htmlentities( Options::get( 'sch_disabled_message' ) )
                . '</div>';
        } elseif ( isset( $_GET['sch_saved'] ) ) {
            $message = '<p class="message">Your work has been saved, but no further edits are permitted.</p>';
        }
        return $message;
    }
}
