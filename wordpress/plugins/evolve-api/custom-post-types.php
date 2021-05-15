<?php

/**
 * create custom post-types and taxonomies
 */
function create_post_types() {

  /**
   * Define/Edit custom post-types and taxonomies here
   */
  $post_types = array(
    // slug, plural, singular, icon, menu order
    'evolve-posts' => array('evolve-posts', 'Evolve', 'Evolve Post', 'media-document', 4),
  );

  $taxonomies = array(
    // post-type to which custom taxonomies will be associated
    'evolve-posts' => array(
      // taxonomy name => array(name, slug, plural, singular, Array<assosicated post types>)
      'evolve-categories' => array('evolve_category', 'evolve-category', 'Evolve Categories', 'Evolve Category')
    )
  );

  foreach ($post_types as $type) {
    register_post_type( $type[0],
      // CPT Options
      array(
        'labels' => array(
          'name' => __( $type[1] ),
          'singular_name' => __( $type[2] )
        ),
        'supports' => array(
          'title',
          'editor',
          'comments',
          'revisions',
          'trackbacks',
          'author',
          'excerpt',
          'page-attributes',
          'thumbnail',
          'custom-fields',
          'post-formats'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => $type[0]),
        'show_in_menu' => true,
        'menu_position' => $type[4],
        'show_in_rest' => true,
        'menu_icon'  => "dashicons-{$type[3]}",
      )
    );

    if (array_key_exists($type[0], $taxonomies)) { // check if custom taxonomies exist for post-type

      foreach ($taxonomies[$type[0]] as $taxonomy) { // iterate over taxonomies and register each one
        register_taxonomy($taxonomy[0], count($taxonomy) > 4 ? $taxonomy[4] : $type[0],
          array(
            'labels' => array(
              'name' => $taxonomy[2],
              'add_new_item' => "Add new $taxonomy[3]"
            ),
            'description' => '',
            'public' => null,
            'publicly_queryable' => null,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'rest_controller_class' => 'WP_REST_Terms_Controller',
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'query_var'=>true,
            'capabilities' => array(
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts'
            ),
            'rewrite' => array(
                'slug' => $taxonomy[1],
                'with_front' => true,
                'hierarchical' => false,
                'ep_mask' => 'EP_NONE'
            ),
            'meta_box_cb' => null
          )
        );
      }
    }
  }
}

// Hooking up our function to theme setup
add_action( 'init', 'create_post_types' );

/**
 * Create taxonomy filters on post-type admin pages
 * https://wordpress.stackexchange.com/a/3215
 */
function my_restrict_manage_posts() {

  // only display these taxonomy filters on desired custom post_type listings
  global $typenow;
  $post_types = get_post_types();
  // $type = array_pop($post_types);
  foreach ($post_types as $type) {
    if ($typenow == $type && $typenow != 'post') {

      // create an array of taxonomy slugs you want to filter by - if you want to retrieve all taxonomies, could use get_taxonomies() to build the list
      $filters = get_object_taxonomies($type);

      foreach ($filters as $tax_slug) {
        // retrieve the taxonomy object
        $tax_obj = get_taxonomy($tax_slug);
        $tax_name = $tax_obj->labels->name;
        // retrieve array of term objects per taxonomy
        $terms = get_terms($tax_slug);

        // output html for taxonomy dropdown filter
        echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
        echo "<option value=''>Show All $tax_name</option>";
        foreach ($terms as $term) {
            // output each select option line, check against the last $_GET to show the current option selected
            echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
        }
        echo "</select>";
      }
    }
  }

};

add_action( 'restrict_manage_posts', 'my_restrict_manage_posts');
