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

class LoginForm {
    public static function fields() {
        if (isset(Util::$data['form'] )) {
            $sch_form = Util::$data['form'];
        } else {
            $sch_form = null;
        }
        ?>
        <div id="sch_student_fields">
            <p>
                <label for="user_first_name">First name<br>
                <input name="user_first_name" id="user_first_name" class="input" type="text" autocomplete="given-name"<?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['first_name'] ); ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="user_last_name">Last name<br>
                <input name="user_last_name" id="user_last_name" class="input" type="text" autocomplete="family-name"<?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['last_name'] ); ?>"<?php } ?>></label>
            </p>
            <p style="margin-bottom: 16px;">
                <label style="cursor: text;">Gender</label><br>
                <label><input name="user_gender" type="radio"<?php if (null !== $sch_form && 'male' === $sch_form['gender'] ) { ?> checked<?php } ?> value="male"> Male</label>&nbsp; &nbsp;
                <label><input name="user_gender" type="radio"<?php if (null !== $sch_form && 'female' === $sch_form['gender'] ) { ?> checked<?php } ?> value="female"> Female</label>
            </p>
            <p>
                <label for="user_dob">Date of birth<br>
                <input name="user_dob" id="user_dob" class="input" type="date" placeholder="yyyy-mm-dd" autocomplete="bday"<?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['dob'] ); ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="address">Address<br>
                <input name="user_address" id="user_address" class="input" type="text"<?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['address'] ); ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="city">City<br>
                <input name="user_city" id="user_city" class="input" type="text"<?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['city'] ); ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="state">State<br>
                <?php Util::state_select('user_state', 'user_state', 'input', null === $sch_form ? null : $sch_form['state'] ); ?></label>
            </p>
            <p>
                <label for="zip">Zip code<br>
                <input name="user_zip" id="user_zip" class="input" type="number" maxlength="5" <?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['zip'] ); ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="user_tel">Phone number<br>
                <input name="user_tel" id="user_tel" class="input" type="tel" placeholder="Include area code" autocomplete="tel"<?php if (null !== $sch_form ) { ?> value="<?php echo $sch_form['tel']; ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="user_parent_email">Parent's e-mail<br>
                <input name="user_parent_email" id="user_parent_email" class="input" type="email"<?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['parent_email'] ); ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="user_parent_name">Parent's full name<br>
                <input name="user_parent_name" id="user_parent_name" class="input" type="text"<?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['parent_name'] ); ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="user_parent_tel">Parent's phone number<br>
                <input name="user_parent_tel" id="user_parent_tel" class="input" type="tel" placeholder="Include area code"<?php if (null !== $sch_form ) { ?> value="<?php echo esc_attr($sch_form['parent_tel'] ); ?>"<?php } ?>></label>
            </p>
            <p>
                <label for="user_picture">Picture of you<br>
                <input name="user_picture" id="user_picture" style="width: 100%; margin: 5px 6px 16px 0" type="file" accept="image/*" autocomplete="photo"></label>
            </p>
            <p style="text-align: center; margin-left: -14px; width: 300px;">
                <img id="user_picture_preview" style="max-height: 300px; max-width: 300px; margin: 5px 0 16px;" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Image preview">
            </p>
            <p>
            (WARNING: sesdrams.org accounts and other internal school use only e-mail addresses will not work here. Contact them to get a different e-mail to use.)
            </p>
        </div>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function () {
                "use strict";
                document.getElementById("registerform").setAttribute("enctype", "multipart/form-data");
                document.getElementById("user_picture").addEventListener("change", function () {
                    var f, i = document.getElementById("user_picture_preview");
                    if (this.files[0]) {
                        f = new FileReader();
                        f.addEventListener("load", function () {
                            i.setAttribute("src", f.result);
                        });
                        f.readAsDataURL(this.files[0]);
                    } else {
                        i.setAttribute("src", "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7");
                    }
                });
            });
        </script>
        <?php
    }

    public static function errors($errors, $update, $user = null ) {
        if (isset($_POST['action'] ) && 'update' === $_POST['action'] ) {
            $prefix = '';
            $actual_user = new \WP_User($user->ID ); // The object passed here is not actually a WP_User instance (???)
            if (! $actual_user->exists() || ! $actual_user->has_cap('student')) {
                return $errors;
            }
        } elseif (isset($_GET['action'] ) && 'register' === $_GET['action'] ) {
            $_POST['action'] = $_GET['action'];
            $prefix = 'user_';
        } else {
            return $errors;
        }

        if (! Options::get('sch_enabled', 'student')) {
            $errors->add('sch_disabled_error', '<strong>ERROR</strong>: ' . htmlentities(Options::get('sch_disabled_message')));
            return $errors;
        }

        $first_name = trim(sanitize_text_field($_POST[ $prefix . 'first_name' ]));
        $last_name = trim(sanitize_text_field($_POST[ $prefix . 'last_name' ]));
        $tel = Util::normalize_tel(sanitize_text_field($_POST[ $prefix . 'tel' ]));
        $address = trim(sanitize_text_field($_POST[ $prefix . 'address' ]));
        $city = trim(sanitize_text_field($_POST[ $prefix . 'city' ]));
        $state = Util::validate_state($_POST[ $prefix . 'state' ]);
        $zip = Util::validate_zip($_POST[ $prefix . 'zip' ]);
        $gender = strtolower(trim(sanitize_text_field($_POST[ $prefix . 'gender' ])));
        $dob = Util::parse_date(trim($_POST[ $prefix . 'dob' ]));

        $parent_email = trim(sanitize_email($_POST[ $prefix . 'parent_email' ]));
        $parent_name = trim(sanitize_text_field($_POST[ $prefix . 'parent_name' ]));
        $parent_tel = Util::normalize_tel(sanitize_text_field($_POST[ $prefix . 'parent_tel' ]));

        $file = $_FILES[ $prefix . 'picture' ];

        Util::$data['form'] = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'tel' => $tel,
            'parent_email' => $parent_email,
            'parent_name' => $parent_name,
            'parent_tel' => $parent_tel,
            'gender' => $gender,
            'dob' => false === $dob ? '' : $dob,
            'address' => $address,
            'city' => $city,
            'state' => false === $state ? '' : $state,
            'zip' => false === $zip ? '' : $zip,
        );

        if ('male' !== $gender && 'female' !== $gender ) {
            $errors->add($prefix . 'gender_error', '<strong>ERROR</strong>: Select a gender.');
        }
        if (false === $dob ) {
            $errors->add($prefix . 'dob_error', '<strong>ERROR</strong>: Invalid/missing date of birth.');
        }
        if (strlen($address ) < 1 ) {
            $errors->add($prefix . 'address_error', '<strong>ERROR</strong>: Missing address.');
        }
        if (strlen($city ) < 1 ) {
            $errors->add($prefix . 'city_error', '<strong>ERROR</strong>: Missing city.');
        }
        if (false === $state ) {
            $errors->add($prefix . 'state_error', '<strong>ERROR</strong>: Invalid/missing state.');
        }
        if (false === $zip ) {
            $errors->add($prefix . 'zip_error', '<strong>ERROR</strong>: Invalid/missing zip code.');
        }

        if (! is_email($parent_email )) {
            $errors->add($prefix . 'parent_email_error', '<strong>ERROR</strong>: Invalid parent e-mail.');
        }

        if (strlen($first_name ) < 1 ) {
            $errors->add($prefix . 'first_name_error', '<strong>ERROR</strong>: Missing student first name.');
        }
        if (strlen($last_name ) < 1 ) {
            $errors->add($prefix . 'last_name_error', '<strong>ERROR</strong>: Missing student last name.');
        }
        if (strlen($parent_name ) < 1 ) {
            $errors->add($prefix . 'parent_name_error', '<strong>ERROR</strong>: Missing parent name.');
        }

        if (strlen($tel ) !== 10 ) {
            $errors->add($prefix . 'tel_error', '<strong>ERROR</strong>: Invalid student telephone number.');
        }
        if (strlen($parent_tel ) !== 10 ) {
            $errors->add($prefix . 'parent_tel_error', '<strong>ERROR</strong>: Invalid parent telephone number.');
        }

        if (empty($file ) || UPLOAD_ERR_NO_FILE === $file['error'] ) {
            if (true !== $update ) {
                $errors->add($prefix . 'picture_error', '<strong>ERROR</strong>: Missing student picture.');
            }
        } else {
            Util::$data['picture'] = wp_handle_upload($file, array('test_form' => false, 'action' => $_POST['action'] ));
            if (isset(Util::$data['picture']['error'] )) {
                $errors->add($prefix . 'picture_error', '<strong>ERROR</strong>: Failed to upload picture: ' . Util::$data['picture']['error'] );
            } else {
                if ('image' !== substr(Util::$data['picture']['type'], 0, 5 )) {
                    $errors->add($prefix . 'picture_error', '<strong>ERROR</strong>: Uploaded file is not an image.');
                    @unlink(Util::$data['picture']['file'] );
                }
            }
        }
        return $errors;
    }
}
