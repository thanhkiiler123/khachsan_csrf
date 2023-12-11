<?php

require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

date_default_timezone_set('Asia/Ho_Chi_Minh');

session_start();

if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
  redirect('index.php');
}

if (isset($_POST['pay_now'])) {
  $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);
  if (!$token || $token !== $_SESSION['csrf_token']) {
    echo 'Đặt phòng thất bại!';
    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Thiếu CSRF token');
    exit;
  } else {
    $checkSum = "";
  
    /*$ORDER_ID = 'ORD_' . $_SESSION['uId'] . random_int(11111, 9999999);
    $CUST_ID = $_SESSION['uId'];
    $TXN_AMOUNT = $_SESSION['room']['payment'];*/
  
    // Insert payment data into database
    $frm_data = filteration($_POST);
    $uid = (int)$frm_data['uid'];
    $query1 = "INSERT INTO `booking_order`(`user_id`, `room_id`, `check_in`, `check_out`,`order_id`) VALUES (?,?,?,?,?)";
  
    insert($query1, [
      $uid, $frm_data['roomid'], $frm_data['checkin'],
      $frm_data['checkout'], $frm_data['orderid']
    ], 'issss');
    $booking_id = mysqli_insert_id($con);
    $roomprice = (int)$frm_data['roomprice'];
    $roompayment = (int)$frm_data['roompayment'];
    $query2 = "INSERT INTO `booking_details`(`booking_id`, `room_name`, `price`, `total_pay`,
        `user_name`, `phonenum`, `address`) VALUES (?,?,?,?,?,?,?)";
  
    insert($query2, [
      $booking_id, $frm_data['roomname'], $roomprice,
      $roompayment, $frm_data['name'], $frm_data['phonenum'], $frm_data['address']
    ], 'isiisss');
    $roomid = (int)$frm_data['roomid'];
    $query = "SELECT quantity FROM rooms WHERE id = ?";
    $result = select($query, [$roomid], 'i');
    if ($result && mysqli_num_rows($result) > 0) {
      $room = mysqli_fetch_assoc($result);
      var_dump($room);
      $quantity = $room['quantity'];
      $new_quantity = $quantity - 0;
      $query = "UPDATE rooms SET quantity = ? WHERE id = ?";
      update($query, [$new_quantity, $roomid], 'ii');
    }
    redirect("bookings.php");
  }
}