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

class PostWrapper {
    protected $post = null;

    public function __construct($post) {
        if (!$post instanceof \WP_Post) {
            error_log("PostWrapper Error: Expected WP_Post, got " . gettype($post) . ". Value: " . print_r($post, true));
            return; // Prevents further issues by exiting early
        }
        $this->post = $post;
    }

    public function __get($name) {
        return $this->post->$name;
    }
    public function __call($name, $arguments) {
        return call_user_func_array(array(
            $this->post,
            $name,
       ), $arguments);
    }
    public function get_meta($meta) {
        return get_post_meta($this->post->ID, $meta, true);
    }
    public function set_meta($meta, $value) {
        update_post_meta($this->post->ID, $meta, $value);
    }

    public function user() {
        return new \WP_User((int) $this->post_author);
    }
}
