<?php
namespace Scholarship;
defined( 'ABSPATH' ) || die( 'this file requires wordpress core' );

class PostWrapper {
	protected $post = null;
	public function __construct( \WP_Post $post ) {
		$this->post = $post;
	}
	public function __get( $name ) {
		return $this->post->$name;
	}
	public function __call( $name, $arguments ) {
		return call_user_func_array( array(
			$this->post,
			$name,
		), $arguments );
	}
	public function get_meta( $meta ) {
		return get_post_meta( $this->post->ID, $meta, true );
	}
	public function set_meta( $meta, $value ) {
		update_post_meta( $this->post->ID, $meta, $value );
	}

	public function user() {
		return new \WP_User( (int) $this->post_author );
	}
}
