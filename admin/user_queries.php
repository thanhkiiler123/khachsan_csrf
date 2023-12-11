<?php
  require('inc/essentials.php');
  require('inc/db_config.php');
  adminLogin();

  if(isset($_GET['seen']))
  {
    $frm_data = filteration($_GET);

    if($frm_data['seen']=='all'){
      $q = "UPDATE `user_queries` SET `seen`=?";
      $values = [1];
      if(update($q,$values,'i')){
        alert('success','Đánh dấu tất cả là đã đọc!');
      }
      else{
        alert('error','Lỗi hệ thống!');
      }
    }
    else{
      $q = "UPDATE `user_queries` SET `seen`=? WHERE `sr_no`=?";
      $values = [1,$frm_data['seen']];
      if(update($q,$values,'ii')){
        alert('success','Đánh dấu là đã đọc!');
      }
      else{
        alert('error','Lỗi hệ thống!');
      }
    }
  }

if(isset($_GET['del'], $_GET['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
    $frm_data = filteration($_GET);
    
    if($frm_data['del']=='all'){
      $q = "DELETE FROM `user_queries`";
      if(mysqli_query($con,$q)){
        alert('success','Tất cả dữ liệu đã bị xóa!');
      }
      else{
        alert('error','Operation failed!');
      }
    }
    else{
      $q = "DELETE FROM `user_queries` WHERE `sr_no`=?";
      $values = [$frm_data['del']];
      if(delete($q,$values,'i')){
        alert('success','Đã xóa dữ liệu!');
      }
      else{
        alert('error','Đã xóa dữ liệu!');
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Phản Hồi</title>
  <?php require('inc/links.php'); ?>
</head>
<body class="bg-light">

  <?php require('inc/header.php'); ?>

  <div class="container-fluid" id="main-content">
    <div class="row">
      <div class="col-lg-10 ms-auto p-4 overflow-hidden">
        <h3 class="mb-4">Khách Hàng Phản Hồi</h3>

        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">

            <div class="text-end mb-4">
              <a href="?seen=all" class="btn btn-dark rounded-pill shadow-none btn-sm">
                <i class="bi bi-check-all"></i> Đánh dấu tất cả đã đọc
              </a>
              <a href="?del=all" class="btn btn-danger rounded-pill shadow-none btn-sm">
                <i class="bi bi-trash"></i> Xoá tất cả
              </a>
            </div>

            <div class="table-responsive-md" style="height: 450px; overflow-y: scroll;">
              <table class="table table-hover border">
                <thead class="sticky-top">
                  <tr class="bg-dark text-light">
                    <th scope="col">#</th>
                    <th scope="col">Tên</th>
                    <th scope="col">Email</th>
                    <th scope="col" width="20%">Tiêu Đề</th>
                    <th scope="col" width="30%">Tin Nhắn</th>
                    <th scope="col">Ngày</th>
                    <th scope="col">Trạng Thái</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $q = "SELECT * FROM `user_queries` ORDER BY `sr_no` DESC";
                    $data = mysqli_query($con,$q);
                    $i=1;

                    while($row = mysqli_fetch_assoc($data))
                    {
                      $date = date('d-m-Y',strtotime($row['datentime']));
                      $seen='';
                      if($row['seen']!=1){
                        $seen = "<a href='?seen=$row[sr_no]' class='btn btn-sm rounded-pill btn-primary'>Đánh dấu là đã đọc</a> <br>";
                      }
                      $seen.="<a href='?del=$row[sr_no]&csrf_token=$_SESSION[csrf_token]' class='btn btn-sm rounded-pill btn-danger mt-2'>Xoá</a>";

                      echo<<<query
                        <tr>
                          <td>$i</td>
                          <td>$row[name]</td>
                          <td>$row[email]</td>
                          <td>$row[subject]</td>
                          <td>$row[message]</td>
                          <td>$date</td>
                          <td>$seen</td>
                        </tr>
                      query;
                      $i++;
                    }
                  ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>


      </div>
    </div>
  </div>
  

  <?php require('inc/scripts.php'); ?>

</body>
</html>