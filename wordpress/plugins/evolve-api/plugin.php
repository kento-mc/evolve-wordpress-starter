<?php
/**
 * Plugin Name: Evolve API
 * Plugin URI: 
 * Description: 
 * Version: 1.0
 * Author: 
 * Author URI: 
  *
  *  @version   1.0
  *  @author   
  */

  class EvolveAPI {
    /**
     * Constructor
     * @uses rest_api_init
     */
    function __construct() {
      $this->target_endpoints = array('page', 'post');
      include(__DIR__ . '/custom-post-types.php');
      add_action( 'rest_api_init', array( $this, 'create_ACF_meta_in_REST' ));
      add_action( 'rest_api_init', array( $this, 'add_image' ));
    }

    /**
     * add advanced custom fields to json api
     * https://stackoverflow.com/questions/56473929/how-to-expose-all-the-acf-fields-to-wordpress-rest-api-in-both-pages-and-custom
     */
    function create_ACF_meta_in_REST() {

      // Get all post types
      $post_types = get_post_types();

      // Add rest field for each post type
      foreach ($post_types as $post_type) {
        register_rest_field( $post_type, "post_type_data", [
          'get_callback'    => array( $this, 'expose_ACF_fields'),
          'schema'          => null,
        ]);
      }
    }

    // Get ACF fields for each page/post
    function expose_ACF_fields( $object ) {

      $ID = $object['id']; // Get page/post ID
      $arr = get_fields($ID); // Get ACF fields from post

      // Data can be mutated here if a different structure is required
      return $arr;
    }

    /**
     * Add Images to json api
     */
    function add_image() {
      $target_endpoints = get_post_types();

      foreach ($target_endpoints as $endpoint) {
        /**
         * Add 'featured_image'
         */
        register_rest_field( $endpoint, 'featured_image',
          array(
            'get_callback'    => array( $this, 'get_image_url_full'),
            'update_callback' => null,
            'schema'          => null,
          )
        );

        /**
         * Add 'featured_image_thumbnail'
         */
        register_rest_field( $endpoint, 'featured_image_thumbnail',
            array(
              'get_callback'    => array( $this, 'get_image_url_thumb'),
              'update_callback' => null,
              'schema'          => null,
            )
          );
      }
    }

    /**
     * Get Image: Thumb
     */
    function get_image_url_thumb(){
      $url = $this->get_image('thumbnail');
      return $url;
    }

    /**
     * Get Image: Full
     */
    function get_image_url_full(){
      $url = $this->get_image('full');
      return $url;
    }

    /**
     * Get Image Helpers
     */
    function get_image($size) {
      $id = get_the_ID();

      if ( has_post_thumbnail( $id ) ){
          $img_arr = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), $size );
          $url = $img_arr[0];
          return $url;
      } else {
          return false;
      }
    }
  }

  new EvolveAPI;

?>
