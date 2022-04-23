<?php
define('SHORTINIT', true);
$path = $_SERVER['DOCUMENT_ROOT'];
require_once($path . '/wp-load.php');

global $wpdb;
header('Content-Type: application/json; charset=utf-8');

if (!isset($_REQUEST['date'])) {
    die("Date required");
}

if (!isset($_REQUEST['product_id'])) {
    die("Product ID required");
}

$date = date_create_from_format("Y-m-d H:i:s", $_REQUEST['date'] . " 00:00:00");
$product_id = (int) $_REQUEST['product_id'];

$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}bookings WHERE product_id = %d AND start_time BETWEEN FROM_UNIXTIME(%d) AND FROM_UNIXTIME(%d)", $product_id, $date->getTimestamp(), $date->getTimestamp() + 86400 );
$res = $wpdb->get_results($sql);

$booked = array();

foreach($res as $booking) {
    $booking_start = date_create_from_format("Y-m-d H:i:s", $booking->start_time);
    $booking_end = date_create_from_format("Y-m-d H:i:s", $booking->end_time);
    $start_hour = (int) $booking_start->format("H");
    $end_hour = (int) $booking_end->format("H");
    for ($i = $start_hour; $i < $end_hour; $i++) {
        $booked[$i] = true;
    }
}

$start = 10;
$end = 23;

$times = array();

for ($i = $start; $i < $end; $i++) {
    $hour = $i;
    $postfix = "am";
    if ($hour == 12) {
        $postfix = "noon";
    }
    $available = !isset($booked[$hour]);
    if ($hour > 12) {
        $hour -= 12;
        $postfix = "pm";
    }
    $times["$hour $postfix"] = $available;
}

echo(json_encode($times));
