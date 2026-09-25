<?php
/**
 * Plugin Name: SRA brick count
 * Description: Publishes how many buy-a-brick products have sold, and emails booking requests to the association. No customer list is stored.
 * Version: 1.1.0
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

  register_rest_route('sra/v1', '/booking', array(
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => 'sra_send_booking_request',
  ));
});

function sra_send_booking_request(WP_REST_Request $request) {
  $space = sanitize_text_field((string) $request->get_param('space'));
  $date = sanitize_text_field((string) $request->get_param('date'));
  $name = sanitize_text_field((string) $request->get_param('name'));
  $email = sanitize_email((string) $request->get_param('email'));
  $note = sanitize_textarea_field((string) $request->get_param('note'));

  if (strlen($space) < 2 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strlen($name) < 2 || !is_email($email)) {
    return new WP_Error('bad_request', 'Missing details', array('status' => 400));
  }

  $body = "Space: {$space}\nDate: {$date}\nName: {$name}\nEmail: {$email}\n";
  if ($note !== '') {
    $body .= "\n{$note}\n";
  }
  $body .= "\nThis is a request from the new site, not a confirmed booking.\n";

  $sent = wp_mail(
    'bookings@the-sra.org',
    'Booking request: ' . $space . ' on ' . $date,
    $body,
    array('Reply-To: ' . $name . ' <' . $email . '>')
  );

  if (!$sent) {
    return new WP_Error('mail_failed', 'Could not send', array('status' => 500));
  }

  return array('ok' => true);
}
