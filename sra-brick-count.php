<?php
/**
 * Plugin Name: SRA brick count
 * Description: Publishes how many buy-a-brick products have sold. Returns a count only. No names and no orders.
 * Version: 1.0.0
 */

add_action('rest_api_init', function () {
  register_rest_route('sra/v1', '/bricks', array(
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function () {
      $small = (int) get_post_meta(10531, 'total_sales', true);
      $large = (int) get_post_meta(10537, 'total_sales', true);
      return array(
        'sold' => $small + $large,
        'small' => $small,
        'large' => $large,
        'target' => 500,
      );
    },
  ));
});
