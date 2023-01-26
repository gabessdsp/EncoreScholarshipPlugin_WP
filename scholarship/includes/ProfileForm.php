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

class ProfileForm {

    public static function fields(\WP_User $user) {
        if (! $user->has_cap('student')) {
            return;
        }
        $student = new Student($user);
        $picture = $student->get_meta('picture');
        $tel = Util::format_tel($student->get_meta('tel'));
        $gender = $student->get_meta('gender');
        $address = $student->get_meta('address');
        $state = $student->get_meta('state');
        $city = $student->get_meta('city');
        $zip = $student->get_meta('zip');
        $dob = $student->get_meta('dob');

        $parent_name = $student->get_meta('parent_name');
        $parent_email = $student->get_meta('parent_email');
        $parent_tel = Util::format_tel($student->get_meta('parent_tel'));
        ?>
        <h3>Student information</h3>
        <table class="form-table">
        <tr class="user-gender-wrap">
            <th><label for="gender">Gender <span class="description">(required)</span></label></th>
            <td><label><input type="radio" name="gender" id="gender_male" value="male" <?php if ('male' === $gender) { ?>checked <?php } ?>/> Male</label>&nbsp; &nbsp;<!-- I'm sorry -->
                <label><input type="radio" name="gender" id="gender_female" value="female" <?php if ('female' === $gender) { ?>checked <?php } ?>/> Female</label></td>
        </tr>
        <tr class="user-dob-wrap">
            <th><label for="dob">Date of Birth <span class="description">(required)</span></label></th>
            <td><input type="date" name="dob" id="dob" class="regular-text" placeholder="yyyy-mm-dd" value="<?php echo esc_attr($dob); ?>" /></td>
        </tr>
        <tr class="user-address-wrap">
            <th><label for="address">Address <span class="description">(required)</span></label></th>
            <td><input type="text" name="address" id="address" class="regular-text" value="<?php echo esc_attr($address); ?>" /></td>
        </tr>
        <tr class="user-city-wrap">
            <th><label for="city">City <span class="description">(required)</span></label></th>
            <td><input type="text" name="city" id="city" class="regular-text" value="<?php echo esc_attr($city); ?>" /></td>
        </tr>
        <tr class="user-state-wrap">
            <th><label for="state">State <span class="description">(required)</span></label></th>
            <td><?php Util::state_select('state', 'state', null, $state); ?></td>
        </tr>
        <tr class="user-zip-wrap">
            <th><label for="zip">Zip code <span class="description">(required)</span></label></th>
            <td><input type="number" name="zip" id="zip" class="regular-text" maxlength="5" value="<?php echo esc_attr($zip); ?>" /></td>
        </tr>
        <tr class="user-tel-wrap">
            <th><label for="tel">Student telephone number <span class="description">(required)</span></label></th>
            <td><input type="tel" name="tel" id="tel" class="regular-text" value="<?php echo esc_attr($tel); ?>" /></td>
        </tr>
        <tr class="user-parent-name-wrap">
            <th><label for="parent_name">Parent name <span class="description">(required)</span></label></th>
            <td><input type="text" name="parent_name" id="parent_name" class="regular-text" value="<?php echo esc_attr($parent_name); ?>" /></td>
        </tr>
        <tr class="user-parent-email-wrap">
            <th><label for="parent_email">Parent email <span class="description">(required)</span></label></th>
            <td><input type="email" name="parent_email" id="parent_email" class="regular-text" value="<?php echo esc_attr($parent_email); ?>" /></td>
        </tr>
        <tr class="user-parent-tel-wrap">
            <th><label for="parent_tel">Parent telephone number <span class="description">(required)</span></label></th>
            <td><input type="tel" name="parent_tel" id="parent_tel" class="regular-text" value="<?php echo esc_attr($parent_tel); ?>" /></td>
        </tr>
        <tr class="user-picture-wrap">
            <th><label for="picture">Picture <span class="description">(required)</span></label></th>
            <td><input type="file" name="picture" id="picture" accept="image/*" />
            <p class="description" style="margin-top: 10px;"><img style="max-width: 300px; max-height: 300px;" id="picture_preview" src="<?php echo esc_attr($picture['url']); ?>" alt="Picture preview" /></p></td>
        </tr>
        </table>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("your-profile").setAttribute("enctype", "multipart/form-data");
                document.getElementById("picture").addEventListener("change", function () {
                    var f, i = document.getElementById("picture_preview");
                    if (this.files[0]) {
                        f = new FileReader();
                        f.addEventListener("load", function () {
                            i.setAttribute("src", f.result);
                        });
                        f.readAsDataURL(this.files[0]);
                    } else {
                        i.setAttribute("src", "<?php echo str_replace($picture['url'], '"', '\\"') ?>");
                    }
                });
            });
        </script>
    <?php }
}
