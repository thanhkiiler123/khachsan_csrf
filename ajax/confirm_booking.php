<?php 

  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');

  date_default_timezone_set('Asia/Ho_Chi_Minh');


  if(isset($_POST['check_availability']))
{   
    $frm_data = filteration($_POST);
    $status = "";
    $result = "";
    // check in and out validations

    $today_date = new DateTime(date("Y-m-d"));
    $checkin_date = new DateTime($frm_data['check_in']);
    $checkout_date = new DateTime($frm_data['check_out']);
    $roomid = (int)$frm_data['roomid'];
    $roomprice = (int)$frm_data['roomprice'];
	
    if($checkin_date == $checkout_date){
      $status = 'check_in_out_equal';
      $result = json_encode(["status"=>$status]);
    }
    else if($checkout_date < $checkin_date){
      $status = 'check_out_earlier';
      $result = json_encode(["status"=>$status]);
    }
    else if($checkin_date < $today_date){
      $status = 'check_in_earlier';
      $result = json_encode(["status"=>$status]);
    }

    //kiểm tra tính khả dụng của đặt chỗ nếu trạng thái trống, nếu không thì trả lại lỗi


    if($status!=''){
      echo $result;
    }
    else{
      session_start();
      $tb_query = "SELECT COUNT(*) AS `total_bookings` FROM `booking_order`
        WHERE booking_status IN ('Đã Đặt', 'Đã Xác Nhận Đặt Phòng')
        AND room_id=?
        AND NOT (check_out < ? OR check_in > ?)";
      $values = [$roomid,$frm_data['check_in'],$frm_data['check_out']];
      $tb_fetch = mysqli_fetch_assoc(select($tb_query,$values,'iss'));
      $rq_result = select("SELECT `quantity` FROM `rooms` WHERE `id`=?",[$roomid],'i');
      $rq_fetch = mysqli_fetch_assoc($rq_result);
      $count_rooms = $rq_fetch['quantity']-$tb_fetch['total_bookings'];
      if(($rq_fetch['quantity']-$tb_fetch['total_bookings'])==0){
        $status = 'unavailable';
        $result = json_encode(['status'=>$status]);
        echo $result;
        exit;
      }
      // echo "$rq_fetch['quantity']";
      $count_days = date_diff($checkin_date,$checkout_date)->days;
      $payment = $roomprice * $count_days;

      //$_SESSION['room']['payment'] = $payment;
      //$_SESSION['room']['available'] = true;
      
      $result = json_encode(["status"=>'available',"c_rooms"=>$count_rooms ,"days"=>$count_days, "payment"=> $payment]);
      echo $result;
    }
  }

?>