<?php
ob_start();
include("connect.php");
session_start();
mysql_query("set names 'utf8'");

// xử lý thêm vào giỏ hàng
if (isset($_GET['tvgh'])) {
	// sp thêm trùng
    $kt = 0;
    for ($i = 1; $i <= count($_SESSION['giohang']); $i++) {
        if ($_SESSION['giohang'][$i]['idSP'] == $_GET['tvgh']) {
			$kt = 1;
			$_SESSION['giohang'][$i]['sl']++;
			$idSP = $_GET['tvgh'];
			$tenSP = $_SESSION['giohang'][$i]['TenSP'];
		}
	}
	
	// sp thêm ko trùng
	if ($kt == 0) {
		$sptronggio = count($_SESSION['giohang']) + 1;
		$s = mysql_query("select * from nn_sanpham where idSP={$_GET['tvgh']}");
		$d = mysql_fetch_array($s);
		$_SESSION['giohang'][$sptronggio]['idSP'] = $d['idSP'];
		$_SESSION['giohang'][$sptronggio]['TenSP'] = $d['TenSP'];
		$_SESSION['giohang'][$sptronggio]['Gia'] = $d['Gia'];
		$_SESSION['giohang'][$sptronggio]['UrlHinh'] = $d['UrlHinh'];
		$_SESSION['giohang'][$sptronggio]['sl'] = 1;
		$idSP = $d['idSP'];
		$tenSP = $d['TenSP'];
	}
	
	header("location:$tenSP-$idSP.tco");
}

// -----------------------------chi tiết giỏ hàng-------------------------------------
// xóa sản phẩm
if (isset($_GET['xoasp'])) {
	for ($i = 1; $i <= count($_SESSION['giohang']); $i++) {
		if ($_SESSION['giohang'][$i]['idSP'] == $_GET['xoasp']) {
			for ($k = $i; $k < count($_SESSION['giohang']); $k++) {
				$_SESSION['giohang'][$k] = $_SESSION['giohang'][$k + 1];
			}
			unset($_SESSION['giohang'][count($_SESSION['giohang'])]);
			break;
		}
	}
	header("location:gio-hang/");
}
// cập nhật giỏ hàng
if (isset($_POST['capnhatdh'])) {
	for ($i = 1; $i <= count($_SESSION['giohang']); $i++) {
		if ($_POST['sl'.$i] <= 0) {
			for ($k = $i; $k < count($_SESSION['giohang']); $k++) {
				$_SESSION['giohang'][$k] = $_SESSION['giohang'][$k + 1];
			}
			unset($_SESSION['giohang'][count($_SESSION['giohang'])]);
		}
		else $_SESSION['giohang'][$i]['sl'] = $_POST['sl'.$i];
	}
	header("location:gio-hang/");
}
// xóa giỏ hàng
if (isset($_GET['xoagiohang'])) {
	unset($_SESSION['giohang']);
	header("location:gio-hang/");
}

//----------------------------------thông tin đặt hàng------------------------------------
// khách hàng ko có tài khoản
if (isset($_POST['TenNguoiNhan'])) {
	$ThoiGianDat = date("Y-m-d", time());
	$NgayGiaoHang = date("Y-m-d", time() + 3*24*60*60);
	$idNguoiDung = 0;
	$mangaunhien = md5(rand(0, 999));
	$s = "insert into nn_donhang values(NULL, $idNguoiDung, '$ThoiGianDat', '{$_POST['TenNguoiNhan']}', '{$_POST['DiaDiemGiao']}', '$NgayGiaoHang', '{$_POST['Email']}', '{$_POST['DienThoai']}', '', '$mangaunhien', 1)";
	if (mysql_query($s)) {
		$s2 = mysql_query("select * from nn_donhang order by idDH desc limit 0,1");
		$d2 = mysql_fetch_array($s2);
		$idDH = $d2['idDH'];
		$kt = 0;
		for ($i = 1; $i <= count($_SESSION['giohang']);$i++) {
			if (!mysql_query("insert into nn_donhangchitiet values($idDH, {$_SESSION['giohang'][$i]['idSP']}, {$_SESSION['giohang'][$i]['sl']}, {$_SESSION['giohang'][$i]['Gia']})")) $kt = 1;
		}
		if ($kt==0) include("PHPMailer-master/examples/gmail.phps");
		else echo "insert nn_donhangchitiet loi";
	}
	else echo $s;
}

if (isset($_GET['mnn'])) {
	$s = "update nn_donhang set MaNgauNhien='', TinhTrang=0 where MaNgauNhien='{$_GET['mnn']}'";
	if (mysql_query($s)) echo "Ban da xac nhan thanh cong.";
	else echo $s;
}
?>