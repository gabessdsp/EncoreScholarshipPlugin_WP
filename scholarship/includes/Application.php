<?php
namespace Scholarship;
defined( 'ABSPATH' ) || die( 'this file requires wordpress core' );

class Application extends PostWrapper {
    public static function render() {
        $application = get_post( (int) $_GET['application'] );
        if ( null === $application ) {
            return;
        }
        $application = new Application( $application );
        $student = new Student( new \WP_User( (int) $application->post_author ) );
        $student_fullname = $student->get( 'first_name' ) . ' '
            . $student->get( 'last_name' );

        $staff = array();
        foreach ( $application->get_meta( 'staff' ) as $id ) {
            $member = new Staff ( new \WP_User( $id ) );
            $recommendation = null;
            foreach ( get_posts( array(
                'numberposts' => -1,
                'author' => $member->ID,
                'post_type' => 'sch_recommendation',
            ) ) as $r ) {
                $r = new Recommendation( $r );
                if ( (int) $r->get_meta( 'sch_application' ) ===
                    (int) $application->ID ) {
                    $recommendation = $r;
                    break; // there will only be one matching recommendation per staff member, so this minimizes the number of iterations.
                }
            }
            $staff[] = array(
                'staff' => $member,
                'recommendation' => $recommendation,
                'fullname' => $member->get( 'display_name' ),
            );
        }

        $rangestrings = array(
            'Not specified',
            'Below Average',
            'Average',
            'Above Average',
            'Outstanding (Top 10%)',
            'One of the top few I\'ve encountered (Top 1%)',
        );
        ?>

        <div id="poststuff" class="wrap">
            <h1>Application: <?php echo $student_fullname; ?></h1>
            <div id="post-body" class="metabox-holder columns-2"
                style="margin-top: 20px;">
                <div id="post-body-content" class="postarea wp-editor-expand">
                    <div class="meta-box-sortables">
                        <div class="postbox">
                            <h2 class="hndle" style="cursor: default;">
                                <span>Career Goals</span>
                            </h2>
                            <div class="inside" id="application-content">
                                <?php echo $application->get_meta( 'goals' ); ?>	
                            </div>
                        </div>
                        <div class="postbox">
                            <h2 class="hndle" style="cursor: default;">
                                <span>High School Theatre Experience</span>
                            </h2>
                            <div class="inside" id="application-content">
                                <?php echo $application->post_content; ?>
                            </div>
                        </div>
                        <?php
                        foreach ( $staff as $member ) {
                            if ( null !== $member['recommendation'] ) {
                                $questions = $member['recommendation']->get_meta( 'questions' );
                                ?>
                                <div class="postbox">
                                    <h2 class="hndle" style="cursor: default;">
                                        <span>Recommendation:
                                            <?php echo htmlspecialchars( $member['fullname'] ); ?>
                                        </span>
                                    </h2>
                                    <div class="inside" id="application-content">
                                        <p>
                                            <strong>Position:</strong>
                                            <?php echo htmlspecialchars( $questions['position'] ); ?>
                                        </p>
                                        <p>
                                            <?php
                                            foreach ( array(
                                                'Student is a performer' => 'performer',
                                                'Student is a pit performer' => 'pit_performer',
                                                'Student is truthful' => 'truthful',
                                            ) as $label => $checkbox ) {
                                                ?>
                                                <strong><?php echo $label; ?>:</strong>
                                                <?php echo $questions[ $checkbox ] ? 'yes' : 'no'; ?><br>
                                                <?php
                                            }
                                            ?>
                                            <strong>Shows participated in:</strong>
                                            <?php echo $questions[ 'shows' ]; ?><br>
                                            <strong>Shows available:</strong>
                                            <?php echo $questions[ 'available' ]; ?>
                                        </p>
                                        <p>
                                            <?php
                                            foreach ( array(
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
                                            ) as $label => $range) {
                                                ?>
                                                <strong><?php echo $label; ?>:</strong>
                                                <?php echo $rangestrings[ $questions[ $range ] ]; ?><br>
                                                <?php
                                            }
                                            ?>
                                        </p>
                                        <p>
                                            <strong>Comments:</strong><br>
                                            <?php echo $member['recommendation']->post_content; ?>
                                        </p>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
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
    public static function help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id' => 'overview',
            'title' => 'Overview',
            'content' => '<p>Fill out all information on the form, then click the "Save and Submit" button.</p>' .
                '<p><a href="https://yorkencoreawards.com/help/" target="_blank">Click here to read the FAQ.</a></p>',
        ) );
        $screen->add_help_tab( array(
            'id' => 'staff',
            'title' => 'Staff',
            'content' => '<p>You will need to specify a <em>minimum</em> of two production staff from your school to recommend your application.</p>' .
                '<p>Type in the text box to search our database by name and e-mail. If we don\'t have a staff member on file, type in their e-mail address and click on the "(Invite staff)" choice that appears.</p>',
        ) );
        $screen->add_help_tab( array(
            'id' => 'counselor',
            'title' => 'Guidance Counselor',
            'content' => '<p>You will need to specify a guidance counselor to upload your transcript.</p>' .
                '<p>Type in the text box to search our database by name and e-mail. If we don\'t have your counselor on file, just type in their full e-mail address into the text box.</p>' .
                '<p>Your counselor will be sent an e-mail informing them that you have requested that they upload a transcript. However, you <em>must</em> also make a formal, in-person request to your counselor for the transcript to be uploaded and inform them that they will be receiving an e-mail.</p>',
        ) );
    }
    public static function meta_boxes() {
        add_meta_box( 'sch_staff_meta', 'Staff',
            '\Scholarship\Application::staff_meta_box', 'sch_application' );
    }
    public static function staff_meta_box( $app = null ) {
        $staff = '';
        if ( null !== $app ) {
            $staff = $app->get_meta( 'staff' );
        }
        $staff_names = array();
        $staff_value = array();
        if ( ! empty( $staff ) ) {
            foreach ( $staff as $id ) {
                $staff = new \WP_User( $id );
                $staff_names[] = $staff->get( 'display_name' );
                $staff_value[] = $staff->user_email;
            }
            $staff_names = implode( $staff_names, ',' );
            $staff_value = implode( $staff_value, ',' );
        } else {
            $staff_value = '';
        }

        ?>
        <div id="sch_application_staff_meta" class="sch_application_search">
            <p class="howto">Pick at least two musical staff members from your school (IE. Director, choreographer, orchestra director etc.)to tag on your application.
            They will recieve e-mails asking them to write a recomendation for your
            application. WARNING: sesdrams.org accounts and other internal school use only e-mail addresses will not work here. Contact them to get a different e-mail to use.</p>
            <div class="nojs-staff hide-if-js">
                <p>Add or remove staff</p>
                <textarea name="sch_application_staff" rows="3" cols="20"
                    id="sch_application_staff"<?php
                    if ( ! empty( $staff_value ) ) {
                        echo' data-names="' . esc_attr( $staff_names ) . '"';
                    }
                    ?>>
                    <?php
                    if ( ! empty( $staff_value ) ) {
                        echo $staff_value;
                    }
                    ?></textarea>
                <p class="howto">Separate staff membersss with commas</p>
            </div>
            <div class="ajaxstaff hide-if-no-js">
                <label class="screen-reader-text" for="sch_application_newstaff">
                Staff e-mail addresses</label>
                <p>
                    <input id="sch_application_newstaff"
                        name="sch_application_newstaff" class="form-input-tip"
                        autocomplete="off" value="" type="text">
                </p>
                <ul id="sch_application_staff_completions"
                    class="sch_application_search_completions"></ul>
                <p class="howto">Type to search by name or e-mail</p>
                <ul class="tagchecklist" id="sch_application_staff_list"></ul>
            </div>
        </div>
        <?php
    }
    public static function form() {
        $student = new Student( wp_get_current_user() );
        $posts = get_posts( array(
            'numberposts' => -1,
            'author' => $student->ID,
            'post_type' => 'sch_application',
        ) );

        $content = '';
        $post = null;
        if ( 0 !== count( $posts ) ) {
            $post = new Application( $posts[0] );
            $content = $post->post_content;
        }

        $tel = Util::format_tel( $student->get_meta( 'tel' ) );
        $parent_name = $student->get_meta( 'parent_name' );
        $parent_email = $student->get_meta( 'parent_email' );
        $parent_tel = Util::format_tel( $student->get_meta( 'parent_tel' ) );
        $gender = $student->get_meta( 'gender' );
        $address = $student->get_meta( 'address' );
        $city = $student->get_meta( 'city' );
        $state = $student->get_meta( 'state' );
        $zip = $student->get_meta( 'zip' );
        $dob = $student->get_meta( 'dob' );

        $counselor = '';
        $goals = '';
        if ( 0 !== count( $posts ) ) {
            $goals = $post->get_meta( 'goals' );
            $highschool = $post->get_meta( 'highschool' );
            $gpa = $post->get_meta( 'gpa' );
            $director = $post->get_meta( 'director' );
            $college = $post->get_meta( 'college' );
            $accepted = $post->get_meta( 'accepted' );
            $activities = $post->get_meta( 'activities' );
            $scholarships = $post->get_meta( 'scholarships' );

            $counselor_user = new \WP_User( (int) $post->get_meta( 'counselor' ) );
            if ( $counselor_user->exists() ) {
                $counselor = $counselor_user->user_email;
            }
        }
        ?>

        <div id="poststuff" class="wrap">
            <form id="post" method="POST" action="admin-post.php?action=sch-apply">
                <input type="hidden" name="action" value="sch-apply">
                <input type="hidden" name="_wpnonce" value="<?php
                    echo wp_create_nonce( 'sch-apply' ); 
                ?>">
                <h1>
                <?php
                if ( null === $post ) {
                    echo 'Apply For a Scholarship';
                } else {
                    echo 'Edit Your Application';
                } ?>
                </h1>
                <?php if ( isset( $_GET['sch_saved'] ) ) { ?>
                    <div class="updated notice">
                        <p>Application saved.</p>
                    </div>
                <?php } ?>
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content" class="postarea wp-editor-expand">
                        <h3>Academic Accomplishments</h3>
                        <p><a href="https://yorkencoreawards.com/help/" target="_blank" class="needhelp">
                        Need help? Click here.</a></p>
                        <table class="form-table">
                        <tr class="application-highschool-wrap">
                            <th><label for="highschool">High School</label></th>
                            <td>
                                <input type="text" name="highschool" id="highschool"
                                    class="regular-text"<?php
                                    if ( isset( $highschool ) ) {
                                        echo ' value="' . esc_attr( $highschool ) .'"';
                                    }
                                    ?> />
                            </td>
                        </tr>
                        <tr class="application-gpa-wrap">
                            <th><label for="gpa">Current GPA</label></th>
                            <td>
                                <input type="number" min="0.0" max="4.0" step="0.01"
                                    name="gpa" id="gpa" class="regular-text"<?php
                                    if ( isset( $gpa ) ) {
                                        echo ' value="' . $gpa . '"';
                                    }
                                    ?> />
                            </td>
                        </tr>
                        <tr class="application-director-wrap">
                            <th><label for="director">High School Musical
                            Director</label></th>
                            <td>
                                <input type="text" name="director" id="director"
                                    class="regular-text"<?php
                                    if ( isset( $director ) ) {
                                        echo ' value="' . esc_attr( $director ) . '"';
                                    }
                                    ?> />
                            </td>
                        </tr>
                        <tr class="application-college-wrap">
                            <th><label for="college">College you are planning to
                            attend</label></th>
                            <td>
                                <input type="text" name="college" id="college"
                                    class="regular-text"<?php
                                    if ( isset( $college ) ) {
                                        echo ' value="' . esc_attr( $college ) . '"';
                                    }
                                    ?> />
                            </td>
                        </tr>
                        <tr class="application-accepted-wrap">
                            <th><label for="accepted">Have you been accepted?</label></th>
                            <td>
                                <input type="checkbox" name="accepted" id="accepted"
                                    value="accepted" <?php
                                    if ( isset( $accepted ) && 'accepted' === $accepted ) {
                                        echo 'checked ';
                                    } ?>/>
                            </td>
                        </tr>
                        </table>

                        <?php foreach ( array(
                            'nontheatre' => array(
                                'High School Activities (other than theatre)',
                                array(
                                    'Activity' => array( 'name', 6 ),
                                ),
                            ),
                            'productions' => array(
                                'Theatre Production Experience',
                                array(
                                    'Show' => array( 'show', 2 ),
                                    'Place Produced' => array( 'location', 2 ),
                                    'Character/Involvement' => array( 'character', 2 ),
                                ),
                            ),
                            'camps' => array(
                                'High School Classes, Workshops, or Camps Related to Theatre',
                                array(
                                    'Activity' => array( 'name', 6 ),
                                ),
                            ),
                            'scholarships' => array(
                                'Expected Scholarships',
                                array(
                                    'Scholarship' => array( 'name', 5 ),
                                    'Amount' => array( 'amount', 2 ),
                                ),
                            ),
                        ) as $slug => $tmp ) {
                            list( $title, $cols ) = $tmp;
                            ?>
                            <h3><?php echo $title; ?></h3>
                            <table id="sch_application_<?php echo $slug; ?>"
                                class="sch_application_activitytable wp-list-table widefat fixed striped posts">
                                <thead>
                                    <tr>
                                        <?php foreach ( $cols as $name => $tmp ) {
                                            list( $colslug, $span ) = $tmp;
                                            ?>
                                            <td<?php
                                            if ( 1 !== $span ) {
                                                ?> colspan="<?php echo $span; ?>"<?php
                                            }
                                            ?>><?php echo $name; ?></td>
                                        <?php } ?>
                                        <?php if ( 'scholarships' !== $slug ) { ?>
                                            <td>Grades</td>
                                        <?php } ?>
                                        <td>
                                            <a href="#" class="sch_application_add"
                                                id="sch_application_add<?php echo $slug; ?>">Add</a>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ( isset( $activities[ $slug ] ) && ! empty( $activities[ $slug ] ) ) {
                                    $n = 0;
                                    foreach ( $activities[ $slug ] as $activity ) { ?>
                                        <tr>
                                            <?php
                                            foreach ( $cols as $name => $tmp ) {
                                                list( $colslug, $span ) = $tmp;
                                                ?>
                                                <td <?php if ( 1 !== $span ) {
                                                        echo ' colspan="' . $span . '"';
                                                    } ?>>
                                                    <?php
                                                    if ( 'amount' === $colslug ) {
                                                        echo '$ ';
                                                    }
                                                    ?>
                                                    <input type="<?php
                                                        echo ( 'amount' === $colslug ) ? 'number' : 'text';
                                                        ?>"
                                                        name="<?php
                                                        echo $slug;
                                                        ?>[<?php
                                                        echo $colslug;
                                                        ?>][<?php
                                                        echo $n;
                                                        ?>]"
                                                        value="<?php
                                                        echo esc_attr( $activity[ $colslug ] );
                                                        ?>" />
                                                </td>
                                                <?php
                                            }
                                            ?>
                                            <?php if ( 'scholarships' !== $slug ) { ?>
                                                <td>
                                                    <select name="<?php
                                                    echo $slug;
                                                    ?>[grades][<?php
                                                    echo $n;
                                                    ?>][]"
                                                        multiple size="2">
                                                    <?php foreach ( array( 9, 10, 11, 12 ) as $grade ) { ?>
                                                        <option value="<?php echo $grade; ?>"<?php
                                                        if ( in_array( $grade, $activity['grades'] ) ) {
                                                            echo ' selected';
                                                        }
                                                        ?>><?php
                                                        echo $grade;
                                                        ?><sup>th</sup></option>
                                                    <?php } ?>
                                                    </select>
                                                </td>
                                            <?php } ?>
                                            <td><a href="#">Remove</button></td>
                                        </tr>
                                    <?php $n += 1; } ?>
                                <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                        <h3>What are your career goals? They are not required to be
                        related to theatre.</h3>
                        <?php wp_editor( $goals, 'sch_application_goals' ); ?>
                        <h3>In the length of 250-500 words, describe how your high school
                        theatre experience has impacted your life.</h3>
                        <?php wp_editor( $content, 'sch_application_essay' ); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div id="side-sortables" class="meta-box-sortables">
                            <div class="postbox">
                                <h2 class="hndle" style="cursor: default">
                                    <span>Student Information</span>
                                </h2>
                                <div class="inside">
                                    <div id="sch_application_student_meta">
                                        <p class="howto">Review this information and make sure it
                                        is correct.</p>
                                        <p>Gender<br />
                                            <label>
                                                <input type="radio" name="gender" id="gender_male"
                                                    value="male" <?php
                                                    if ( isset( $gender ) && 'male' === $gender ) {
                                                        echo 'checked ';
                                                    }
                                                    ?>/> Male
                                            </label>&nbsp; &nbsp;<!-- I'm sorry -->
                                            <label>
                                                <input type="radio" name="gender" id="gender_female"
                                                value="female" <?php
                                                if ( isset( $gender ) && 'female' === $gender ) {
                                                    echo 'checked ';
                                                }
                                                ?>/>
                                                Female
                                            </label>
                                        </p>
                                        <p><label for="dob">Date of birth</label><br />
                                            <input type="date" name="dob" id="dob"
                                                value="<?php echo esc_attr( $dob ); ?>" /></p>
                                        <p><label for="address">Address</label><br />
                                            <input type="text" name="address" id="address"
                                                value="<?php echo esc_attr( $address ); ?>" /></p>
                                        <p><label for="city">City</label><br />
                                            <input type="text" name="city" id="city"
                                                value="<?php echo esc_attr( $city ); ?>" /></p>
                                        <p><label for="state">State</label><br />
                                            <?php
                                            Util::state_select( 'state', 'state', null, $state );
                                            ?></p>
                                        <p><label for="zip">Zip code</label><br />
                                            <input type="number" maxlength="5" name="zip" id="zip"
                                                value="<?php echo esc_attr( $zip ); ?>" /></p>
                                        <p><label for="tel">Student telephone number</label><br />
                                            <input type="tel" name="tel" id="tel"
                                                value="<?php echo esc_attr( $tel ); ?>" /></p>
                                        <p><label for="parent_name">Parent name</label><br />
                                            <input type="text" name="parent_name" id="parent_name"
                                                value="<?php echo esc_attr( $parent_name ); ?>" /></p>
                                        <p><label for="parent_email">Parent email</label><br />
                                            <input type="email" name="parent_email" id="parent_email"
                                                value="<?php echo esc_attr( $parent_email ); ?>" /></p>
                                        <p><label for="parent_tel">Parent telephone number</label><br />
                                            <input type="tel" name="parent_tel" id="parent_tel"
                                                value="<?php echo esc_attr( $parent_tel ); ?>" /></p>
                                    </div>
                                </div>
                            </div>
                            <div class="postbox">
                                <h2 class="hndle" style="cursor: default;"><span>Staff</span></h2>
                                <div class="inside">
                                    <?php self::staff_meta_box( $post ); ?>
                                </div>
                            </div>
                            <div class="postbox">
                                <h2 class="hndle" style="cursor: default;"><span>Guidance Counselor</span></h2>
                                <div class="inside">
                                    <div id="sch_application_counselor_meta" class="sch_application_search">
                                        <p class="howto">Enter your guidance counselor's email
                                        address. They will recieve an e-mail asking them to upload
                                        your transcript. You <strong>must</strong> make a formal,
                                        in-person request to your counselor for the transcript to
                                        be uploaded and inform them that they will be receiving an
                                        e-mail. WARNING: sesdrams.org accounts and other internal school use only e-mail addresses will not work here. Contact them to get a different e-mail to use.</p>
                                        <div>
                                            <label class="screen-reader-text" for="counselor">
                                            Guidance Counselor's e-mail address</label>
                                            <p>
                                                <input id="counselor" name="counselor"
                                                class="form-input-tip" autocomplete="off"
                                                value="<?php echo esc_attr( $counselor ); ?>"
                                                type="text">
                                            </p>
                                        </div>
                                        <ul id="sch_application_counselor_completions"
                                        class="hide-if-no-js sch_application_search_completions"></ul>
                                        <p class="hide-if-no-js howto">Type to search by name or
                                        e-mail</p>
                                    </div>
                                </div>
                            </div>
                            <div class="postbox">
                                <h2 class="hndle" style="cursor: default;">
                                    <span>Submit Application</span>
                                </h2>
                                <div class="inside">
                                    <div class="submitbox" id="submitpost">
                                        <p class="howto">You will be able to come back and edit
                                        your application until the due date.</p>
                                        <?php
                                        submit_button( 'Save and Submit', 'primary', 'submit',
                                            false, array( 'style' => 'width: 100%;' ) );
                                        ?>
                                        <p>
                                            <a href="https://yorkencoreawards.com/help/"
                                                target="_blank"  class="needhelp">Need help? Click here.</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
    public static function post() {
        $student = new Student( wp_get_current_user() );
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'sch-apply' )
            || ! $student->exists() || ! $student->has_cap( 'student' ) ) {
            die( 'Unauthorized request' );
        }

        $oldapp = $student->application();

        $post = array(
            'post_type' => 'sch_application',
            'post_status' => 'publish',
            'post_author' => $student->ID,
        );

        $oldcounselor = 0;
        $oldstaff = array();
        if ( false !== $oldapp) {
            $post['ID'] = $oldapp->ID;
            $post['post_date'] = $oldapp->post_date;
            $oldcounselor = (int) $oldapp->get_meta( 'counselor' );
            $oldstaff = $oldapp->get_meta( 'staff' );
        }

        $post['post_content'] = strip_shortcodes(
            str_ireplace( 
                '<!--more-->', '',
                wp_kses_post( $_POST['sch_application_essay'] )
            )
        );
        $goals = strip_shortcodes(
            str_ireplace( 
                '<!--more-->', '',
            wp_kses_post( $_POST['sch_application_goals'] )
            )
        );

        // this stuff has all been previously entered, so just don't update if it's invalid
        $tel = Util::normalize_tel( $_POST['tel'] );
        $gender = strtolower( trim( sanitize_text_field( $_POST['gender'] ) ) );
        $address = trim( sanitize_text_field( $_POST['address'] ) );
        $city = trim( sanitize_text_field( $_POST['city'] ) );
        $state = trim( sanitize_text_field( $_POST['state'] ) );
        $zip = Util::validate_zip( $_POST['zip'] );
        $dob = Util::parse_date( trim( sanitize_text_field( $_POST['dob'] ) ) );

        $parent_email = trim( sanitize_email( $_POST['parent_email'] ) );
        $parent_name = trim( sanitize_text_field( $_POST['parent_name'] ) );
        $parent_tel = Util::normalize_tel( $_POST['parent_tel'] );

        if ( strlen( $tel ) === 10 ) {
            $student->set_meta( 'tel', $tel );
        }
        if ( 'male' === $gender || 'female' === $gender ) {
            $student->set_meta( 'gender', $gender );
        }
        if ( strlen( $address ) > 0 ) {
            $student->set_meta( 'address', $address );
        }
        if ( strlen( $city ) > 0 ) {
            $student->set_meta( 'city', $city );
        }
        if ( false !== $state ) {
            $student->set_meta( 'state', $state );
        }
        if ( false !== $zip ) {
            $student->set_meta( 'zip', $zip );
        }
        if ( false !== $dob ) {
            $student->set_meta( 'dob', $dob );
        }

        if ( is_email( $parent_email ) ) {
            $student->set_meta( 'parent_email', $parent_email );
        }
        if ( strlen( $parent_name ) > 0 ) {
            $student->set_meta( 'parent_name', $parent_name );
        }
        if ( strlen( $parent_tel ) === 10 ) {
            $student->set_meta( 'parent_tel', $parent_tel );
        }

        // the rest of these are allowed to be empty
        $highschool = trim( sanitize_text_field( $_POST['highschool'] ) );
        $gpa = (float) trim( sanitize_text_field( $_POST['gpa'] ) );
        // except for GPA, which is clamped at 0.0 and 4.0
        if ( $gpa < 0.0 ) {
            $gpa = 0.0;
        } elseif ( $gpa > 4.0 ) {
            $gpa = 4.0;
        }
        $director = trim( sanitize_text_field( $_POST['director'] ) );
        $college = trim( sanitize_text_field( $_POST['college'] ) );
        $accepted = trim( sanitize_text_field( $_POST['accepted'] ) );
        if ( 'accepted' !== $accepted ) {
            $accepted = '';
        }

        $activities = array();

        foreach ( array(
            'nontheatre' => array(
                'name',
            ),
            'productions' => array(
                'show',
                'location',
                'character',
            ),
            'camps' => array(
                'name',
            ),
            'scholarships' => array(
                'name',
                'amount',
            ),
        ) as $slug => $cols ) {
            if ( isset( $_POST[ $slug ] ) && is_array( $_POST[ $slug ] ) ) {
                $skip = false;
                foreach ( $cols as $col ) {
                    if ( ! isset( $_POST[ $slug ][ $col ] ) ||
                        ! is_array( $_POST[ $slug ][ $col ] ) ) {
                        $skip = true;
                        break;
                    }
                }
                if ( ! $skip ) {
                    $activities[ $slug ] = array();
                    foreach ( $_POST[ $slug ][ $cols[0] ] as $n => $activity ) {
                        $skip = false;
                        $activity = array();
                        foreach ( $cols as $col ) {
                            if ( ! isset( $_POST[ $slug ][ $col ][ $n ] ) ||
                                strlen( $_POST[ $slug ][ $col ][ $n ] ) < 1 ) {
                                $skip = true;
                                break;
                            } else {
                                if ( 'amount' === $col ) {
                                    $activity[ $col ] = intval( $_POST[ $slug ][ $col ][ $n ] );
                                } else {
                                    $activity[ $col ] = trim( sanitize_text_field(
                                        $_POST[ $slug ][ $col ][ $n ] ) );
                                }
                            }
                        }

                        if ( 'scholarships' !== $slug ) {
                            if ( ! isset( $_POST[ $slug ]['grades'][ $n ] ) ||
                                ! is_array( $_POST[ $slug ]['grades'][ $n ] ) ) {
                                $skip = true;
                            }
                        }

                        if ( $skip ) {
                            continue;
                        }

                        if ( 'scholarships' === $slug ) {
                            $activities[ $slug ][] = $activity;
                        } else {
                            $grades = array();
                            foreach ( $_POST[ $slug ]['grades'][ $n ] as $grade ) {
                                $grade = (int) trim( $grade );
                                if ( $grade >= 9 && $grade <= 12 ) {
                                    $grades[] = $grade;
                                }
                            }
                            if ( count( $grades ) > 0 ) {
                                $activity['grades'] = $grades;
                                $activities[ $slug ][] = $activity;
                            }
                        }
                    }
                }
            }
        }

        $replace = array(
            'student' => $student->get( 'first_name' ) . ' ' . $student->get( 'last_name' ),
        );

        $staff = array();
        $emails = explode( ',', sanitize_text_field(
            $_POST['sch_application_staff'] ) );
        foreach ( $emails as $email ) {
            $email = trim( sanitize_email( $email ) );
            if ( is_email( $email ) ) {
                if ( false === email_exists( $email ) ) { // new staff
                    $member = Mail::invite_user( $email, 'staff' );
                    $staff[] = (int) $member->ID; // ->ID is a string.
                } else { // existing staff
                    $member = get_user_by( 'email', $email );
                    if ( $member->has_cap( 'staff' ) ) {
                        $staff[] = (int) $member->ID;

                        if ( ! in_array( (int) $member->ID, $oldstaff ) ) {
                            $replace['name'] = $member->get( 'display_name' );
                            Mail::mail_user(
                                $member,
                                Options::get( 'sch_mail_tag_staff', 'subject' ),
                                Options::get( 'sch_mail_tag_staff', 'body' ),
                                $replace
                            );
                        }
                    }
                }
            }
        }

        $counselor = 0;
        $email = trim( sanitize_email( $_POST['counselor'] ) );
        if ( is_email( $email ) ) {
            if ( false === email_exists( $email ) ) { // new counselor
                $user = Mail::invite_user( $email, 'counselor' );
                $counselor = $user->ID;
            } else {
                $user = get_user_by( 'email', $email );
                if ( $user->has_cap( 'counselor' ) ) {
                    $counselor = (int) $user->ID;
                    $replace['name'] = $user->get( 'display_name' );
                    if ( $counselor !== $oldcounselor ) {
                        Mail::mail_user(
                            $user,
                            Options::get( 'sch_mail_tag_counselor', 'subject' ),
                            Options::get( 'sch_mail_tag_counselor', 'body' ),
                            $replace
                        );
                    }
                }
            }
        }

        $post = new Application( get_post( wp_insert_post( $post ) ) );
        $post->set_meta( 'staff', $staff );
        $post->set_meta( 'counselor', $counselor );
        $post->set_meta( 'highschool', $highschool );
        $post->set_meta( 'director', $director );
        $post->set_meta( 'gpa', $gpa );
        $post->set_meta( 'college', $college );
        $post->set_meta( 'accepted', $accepted );
        $post->set_meta( 'activities', $activities );
        $post->set_meta( 'goals', $goals );

        if ( Options::get( 'sch_enabled', 'student' ) ) {
            wp_redirect( admin_url( 'admin.php?page=sch-application&sch_saved=true' ) );
        } else {
        // if applications are disabled while the student is editing their
        // application, allow one save and then log them out.
            wp_logout();
            wp_redirect( wp_login_url() . '?sch_saved=true' );
        }
    }

    public function student() {
        return new Student( $this->user() );
    }

    public function counselor() {
        $counselor = new Counselor( new \WP_User( (int) $this->get_meta( 'counselor' ) ) );
        if ( null === $counselor || ! $counselor->exists() ) {
            return false;
        }
        return $counselor;
    }

    public function recommendations() {
        $recommends = array();
        foreach ( get_posts( array(
            'numberposts' => -1,
            'post_type' => 'sch_recommendation'
        ) ) as $post ) {
            if ( (int) get_post_meta( $post->ID, 'sch_application', true )
                === (int) $this->ID ) {
                $recommends[] = new Recommendation( $post );
            }
        }
        return $recommends;
    }
    public function recommendation_by( Staff $staff ) {
        foreach ( get_posts( array(
            'numberposts' => -1,
            'post_type' => 'sch_recommendation',
            'author' => $staff->ID
        ) ) as $post ) {
            if ( (int) get_post_meta( $post->ID, 'sch_application', true )
                === (int) $this->ID ) {
                return new Recommendation( $post );
            }
        }
        return false;
    }
    public function meta_box() {
        ?>

        <div class="postbox">
            <h2 class="hndle" style="cursor: default;">
                <span>Academic Accomplishments</span>
            </h2>
            <div class="inside">
                <div class="appbox">
                    <p>
                        <?php foreach ( array(
                            'highschool' => 'High School',
                            'gpa' => 'Current GPA',
                            'director' => 'Musical Director',
                            'college' => 'Planned College',
                        ) as $attr => $display ) { ?>
                            <strong><?php echo $display; ?>:</strong> <?php
                            $meta = (string) $this->get_meta( $attr );
                            if ( strlen( $meta ) > 0 ) {
                                echo htmlentities( $meta );
                            } else {
                                echo 'N/A';
                            }
                            ?><br />
                        <?php } ?>
                        <strong>Accepted:</strong> <?php
                        if ( 'accepted' === $this->get_meta( 'accepted' ) ) {
                            echo 'yes';
                        } else {
                            echo 'no';
                        }
                        ?>
                    </p>
                    <?php
                    $activities = $this->get_meta( 'activities' );
                    foreach ( array(
                        'nontheatre' => array(
                            'High School Non-Theatre Activities',
                            array(
                                'name' => 'Activity',
                            ),
                        ),
                        'productions' => array(
                            'Production Experience',
                            array(
                                'show' => 'Show Title',
                                'location' => 'Place Produced',
                                'character' => 'Character/Involvement',
                            ),
                        ),
                        'camps' => array(
                            'High School Theatre Activities',
                            array(
                                'name' => 'Activity',
                            ),
                        ),
                        'scholarships' => array(
                            'Expected Scholarships',
                            array(
                                'name' => 'Scholarship',
                                'amount' => 'Amount',
                            ),
                        ),
                    ) as $slug => $tmp ) {
                        list( $name, $fields ) = $tmp;
                        ?>
                        <?php if ( isset( $activities[ $slug ] ) ) { ?>
                            <strong><?php echo $name; ?>:</strong>
                            <?php foreach ( $activities[ $slug ] as $activity ) { ?>
                                <p>
                                    <?php foreach ( $fields as $fieldslug => $fieldname ) { ?>
                                        <strong><?php echo $fieldname; ?>:</strong>
                                        <?php
                                            if ( 'amount' === $fieldslug ) {
                                                echo '$';
                                            }
                                            echo htmlspecialchars( $activity[ $fieldslug ] );
                                        ?>
                                        <br />
                                    <?php } ?>
                                    <?php if ( 'scholarships' !== $slug ) { ?>
                                        <strong>Grade(s):</strong>
                                        <?php echo implode( ', ', $activity['grades'] ); ?>
                                    <?php } ?>
                                </p>
                            <?php } ?>	
                        <?php } else { ?>
                            <p><strong><?php echo $name; ?>:</strong> None</p>
                        <?php } ?>
                    <?php } ?>
                    <p><strong>Transcript: </strong> <?php
                    $counselor = $this->counselor();
                    if ( false === $counselor || ! $counselor->exists() ) {
                        echo 'No counselor specified';
                    } else {
                        $transcript = $counselor->transcript_for( $this );
                        if ( false === $transcript ) {
                            echo 'Not uploaded';
                        } else {
                            ?><a href="<?php echo esc_attr( $transcript['url'] ); ?>">
                            Uploaded</a><?php
                        }
                    } ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}
