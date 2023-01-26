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

class UserWrapper {
    protected $user = null;
    public function __construct(\WP_User $user ) {
        $this->user = $user;
    }
    public function __get($name ) {
        return $this->user->$name;
    }
    public function __call($name, $arguments ) {
        return call_user_func_array(array(
            $this->user,
            $name,
        ), $arguments );
    }
    public function get_meta($meta ) {
        return get_user_meta($this->user->ID, $meta, true );
    }
    public function set_meta($meta, $value ) {
        update_user_meta($this->user->ID, $meta, $value );
    }
}
