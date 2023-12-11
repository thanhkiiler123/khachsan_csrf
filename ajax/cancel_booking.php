<?php 

  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');

  date_default_timezone_set('Asia/Ho_Chi_Minh');
  session_start();


  if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
    redirect('index.php');
  }

  if(isset($_POST['cancel_booking']))
  {
    $frm_data = filteration($_POST);
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);
    if (!$token || $token !== $_SESSION['csrf_token']) {
      echo 'Hủy phòng thất bại!';
      header($_SERVER['SERVER_PROTOCOL'] . ' 405 Thiếu CSRF token');
      exit;
    } else {
      $query = "UPDATE `booking_order` SET `booking_status`=?, `refund`=? 
        WHERE `booking_id`=? AND `user_id`=?";
  
      $values = ['Đã Huỷ',0,$frm_data['id'],$_SESSION['uId']];
  
      $result = update($query,$values,'siii');
  
      echo $result;
    }
  }

?>