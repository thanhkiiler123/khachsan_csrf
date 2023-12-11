<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - CONFIRM BOOKING</title>
  <style>
    .button-disabled {
      background-color: #77d7c9; 
      color: #999;
      cursor: not-allowed; 
      pointer-events: none;
    }

  </style>
</head>

<body class="bg-light">

  <?php require('inc/header.php'); ?>

  <?php

  /*
      Check room id from url is present or not
      Shutdown mode is active or not
      User is logged in or not
    */

  if (!isset($_GET['id']) || $settings_r['shutdown'] == true) {
    redirect('rooms.php');
  } else if (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
    redirect('rooms.php');
  }

  // filter and get room and user data

  $data = filteration($_GET);

  $room_res = select("SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?", [$data['id'], 1, 0], 'iii');

  if (mysqli_num_rows($room_res) == 0) {
    redirect('rooms.php');
  }

  $room_data = mysqli_fetch_assoc($room_res);

  /*$_SESSION['room'] = [
    "id" => $room_data['id'],
    "name" => $room_data['name'],
    "price" => $room_data['price'],
    "payment" => null,
    "available" => false,
  ];*/


  $user_res = select("SELECT * FROM `user_cred` WHERE `id`=? LIMIT 1", [$_SESSION['uId']], "i");
  $user_data = mysqli_fetch_assoc($user_res);
  ?>



  <div class="container">
    <div class="row">

      <div class="col-12 my-5 mb-4 px-4">
        <h2 class="fw-bold">XÁC NHẬN ĐẶT PHÒNG</h2>
        <div style="font-size: 14px;">
          <a href="index.php" class="text-secondary text-decoration-none">TRANG CHỦ</a>
          <span class="text-secondary"> > </span>
          <a href="rooms.php" class="text-secondary text-decoration-none">PHÒNG</a>
          <span class="text-secondary"> > </span>
          <a href="#" class="text-secondary text-decoration-none">XÁC NHẬN</a>
        </div>
      </div>

      <div class="col-lg-7 col-md-12 px-4">
        <?php
        $room_thumb = ROOMS_IMG_PATH . "thumbnail.jpg";
        $thumb_q = mysqli_query($con, "SELECT * FROM `room_images` 
            WHERE `room_id`='$room_data[id]' 
            AND `thumb`='1'");

        if (mysqli_num_rows($thumb_q) > 0) {
          $thumb_res = mysqli_fetch_assoc($thumb_q);
          $room_thumb = ROOMS_IMG_PATH . $thumb_res['image'];
        }

        echo <<<data
            <div class="card p-3 shadow-sm rounded">
              <img src="$room_thumb" class="img-fluid rounded mb-3">
              <h5>$room_data[name]</h5>
              <h6>$room_data[price] vnđ</h6>
            </div>
          data;

        ?>
      </div>

      <div class="col-lg-5 col-md-12 px-4">
        <div class="card mb-4 border-0 shadow-sm rounded-3">
          <div class="card-body">
            <form action="pay_now.php" method="POST" id="booking_form">
              <h6 class="mb-3">CHI TIẾT PHÒNG ĐẶT</h6>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tên</label>
                  <input name="name" type="text" value="<?php echo $user_data['name'] ?>" class="form-control shadow-none" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Số Điện Thoại</label>
                  <input name="phonenum" type="number" value="<?php echo $user_data['phonenum'] ?>" class="form-control shadow-none" required>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">Địa Chỉ</label>
                  <textarea name="address" class="form-control shadow-none" rows="1" required><?php echo $user_data['address'] ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Ngày Nhận Phòng</label>
                  <input name="checkin" onchange="check_availability()" type="date" class="form-control shadow-none" required>
                </div>
                <div class="col-md-6 mb-4">
                  <label class="form-label">Ngày Trả Phòng</label>
                  <input name="checkout" onchange="check_availability()" type="date" class="form-control shadow-none" required>
                </div>
                <input name="uid" type="hidden" value="<?php echo $_SESSION['uId'] ?>" >
                <input name="roomid" type="hidden" value="<?php echo $room_data['id'] ?>" >
                <input name="orderid" type="hidden" value="<?php echo 'ORD_' . $_SESSION['uId'] . random_int(11111, 9999999) ?>" >
                <input name="roomname" type="hidden" value="<?php echo $room_data['name'] ?>" >
                <input name="roomprice" type="hidden" value="<?php echo $room_data['price'] ?>" >
                <input name="roompayment" type="hidden" value="<?php echo null ?>" >
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">
                <div class="col-12">
                  <div class="spinner-border text-info mb-3 d-none" id="info_loader" role="status">
                    <span class="visually-hidden">Đang Tải ...</span>
                  </div>

                  <h6 class="mb-3 text-danger" id="pay_info">Cung cấp ngày nhận phòng và trả phòng!</h6>
                  <h6 class="mb-3 text-danger" id="pay_info"></h6>

                  <button id="book-now" name="pay_now" class="btn w-100 text-white custom-bg shadow-none mb-1" disabled>Đặt Ngay</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>



  <?php require('inc/footer.php'); ?>
  <script>
    let booking_form = document.getElementById('booking_form');
    let info_loader = document.getElementById('info_loader');
    let pay_info = document.getElementById('pay_info');

    function check_availability() {
      let checkin_val = booking_form.elements['checkin'].value;
      let checkout_val = booking_form.elements['checkout'].value;
      let roomid = booking_form.elements['roomid'].value;
      let roomprice = booking_form.elements['roomprice'].value;
      booking_form.elements['pay_now'].setAttribute('disabled', true);

      if (checkin_val != '' && checkout_val != '') {
        pay_info.classList.add('d-none');
        pay_info.classList.replace('text-dark', 'text-danger');
        info_loader.classList.remove('d-none');

        let data = new FormData();

        data.append('check_availability', '');
        data.append('check_in', checkin_val);
        data.append('check_out', checkout_val);
        data.append('roomid', roomid);
        data.append('roomprice', roomprice);
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/confirm_booking.php", true);

        xhr.onload = function() {
          let data = JSON.parse(this.responseText);

          if (data.status == 'check_in_out_equal') {
            pay_info.innerText = "Bạn không thể trả phòng trong cùng một ngày!";
          } else if (data.status == 'check_out_earlier') {
            pay_info.innerText = "Ngày trả phòng sớm hơn ngày nhận phòng!";
          } else if (data.status == 'check_in_earlier') {
            pay_info.innerText = "Ngày nhận phòng sớm hơn ngày hôm nay!";
          } else if (data.status == 'unavailable') {
            pay_info.innerText = "Đã hết phòng cho ngày đặt phòng này!";
          } else {
            pay_info.innerHTML = "Số Phòng Trống: " + data.c_rooms + "<br>Số ngày Đặt: " + data.days + "<br>Tổng số tiền phải trả: " + data.payment + ' vnđ';
            // pay_info.innerHTML = "Số lượng: " + data.ppp + ' ppp';
            // pay_info.innerHTML = "Số lượng T: " + data.kk + ' ppp';
	          document.querySelector("input[name='roompayment']").value=data.payment;
            pay_info.classList.replace('text-danger', 'text-dark');
            booking_form.elements['pay_now'].removeAttribute('disabled');
          }

          pay_info.classList.remove('d-none');
          info_loader.classList.add('d-none');
        }

        xhr.send(data);
      }

    }
  </script>

  <?php
    // function query($query, $params, $types) {
    //   $pdo = new PDO('mysql:host=localhost;dbname=vinhhotel', 'root', '');
    //   $stmt = $pdo->prepare($query);
    //   $stmt->execute($params);
    //   return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    // // Lấy số lượng phòng còn lại từ cơ sở dữ liệu
    // $q_quantity = "SELECT `quantity` FROM `rooms` WHERE `id` = ?";
    // $result = query($q_quantity, [$_SESSION['room']['id']], 'iiiiiiiii');
    // $remaining_quantity = $result[0]['quantity'];

    // echo $remaining_quantity;
    // if ($remaining_quantity == 0) {
    //   // Hiển thị thông báo đã hết phòng
    //   // Thêm lớp CSS mới vào nút đặt phòng
    //   echo '<script>document.getElementById("book-now").classList.add("button-disabled");</script>';
    //   echo '<script>document.getElementById("pay_info").innerHTML = "Đã hết phòng, vui lòng chọn phòng khác!!!";</script>';
    //   // echo "<script>let booking_form = document.getElementById('booking_form');booking_form.elements['pay_now'].setAttribute('disabled', true);</script>";
    // }

    // if ($remaining_quantity > 8) {
    //   // Hiển thị thông báo đã hết phòng
    //   echo "Đã hết phòng";
    //   // Thêm lớp CSS mới vào nút đặt phòng và disable nút đó
    //   echo "<script>let booking_form = document.getElementById('booking_form');booking_form.elements['pay_now'].setAttribute('disabled', true);booking_form.elements['pay_now'].style.opacity = '0.5';</script>";
    // } else {
    //   // Xóa lớp CSS và enable nút đặt phòng
    //   echo "<script>let booking_form = document.getElementById('booking_form');booking_form.elements['pay_now'].removeAttribute('disabled');booking_form.elements['pay_now'].style.opacity = '1';</script>";
    // }
    
  ?>

</body>

</html>