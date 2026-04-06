<?php
// arac_ekle.php

// db.php güvenli include
require_once "db.php"; // db.php içinde session_start() artık güvenli olmalı

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// Kategoriler
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");
if(!$kategoriler){
    die("Kategori sorgu hatası: ".$baglanti->error);
}

$msg = '';
if(isset($_POST['kaydet'])){
    $marka = $_POST['marka'];
    $model = $_POST['model'];
    $plaka = $_POST['plaka'];
    $camera = $_POST['kamera'] ?? 'Yok';
    $gps = $_POST['gps'] ?? 'Yok';
    $sahip = $_POST['sahip'];
    $telefon = $_POST['telefon'];
    $kategori_id = (int)$_POST['kategori_id'];

    // Resim upload
    $resim = '';
    if(isset($_FILES['resim']) && $_FILES['resim']['tmp_name'] != ''){
        $resim = time().'_'.basename($_FILES['resim']['name']);
        if(!is_dir('uploads')){
            mkdir('uploads', 0777, true);
        }
        if(!move_uploaded_file($_FILES['resim']['tmp_name'], 'uploads/'.$resim)){
            $msg = "Resim yüklenemedi!";
        }
    }

    if($msg === ''){
        // Plaka kontrolü
        $kontrol = $baglanti->prepare("SELECT id, resim FROM araclar WHERE plaka=?");
        $kontrol->bind_param("s", $plaka);
        $kontrol->execute();
        $kontrol->store_result();

        if($kontrol->num_rows > 0){
            // Var -> güncelle
            $kontrol->bind_result($arac_id, $eski_resim);
            $kontrol->fetch();

            if($resim == '') $resim = $eski_resim;

            $stmt = $baglanti->prepare("UPDATE araclar SET marka=?, model=?, camera=?, gps=?, sahip=?, telefon=?, kategori_id=?, resim=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $marka, $model, $camera, $gps, $sahip, $telefon, $kategori_id, $resim, $arac_id);
            if($stmt->execute()){
                $msg = "✅ Araç güncellendi.";
            } else {
                $msg = "❌ Güncelleme hatası: " . $stmt->error;
            }
        } else {
            // Yok -> ekle
            $stmt = $baglanti->prepare("INSERT INTO araclar (marka, model, plaka, camera, gps, sahip, telefon, kategori_id, resim) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssssis", $marka, $model, $plaka, $camera, $gps, $sahip, $telefon, $kategori_id, $resim);
            if($stmt->execute()){
                $msg = "✅ Araç başarıyla eklendi.";
            } else {
                $msg = "❌ Ekleme hatası: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Araç Ekle</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color:#f5f6fa; color:#2f3640; font-family:'Segoe UI', sans-serif; }
.card { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
img { max-width:150px; border-radius:8px; margin-top:10px; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container mt-5">
  <h3>🚚 Araç Ekle</h3>
  <?php if($msg != ''){ echo '<div class="alert alert-info">'.$msg.'</div>'; } ?>
  <div class="card p-4 mt-3">
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label>Marka</label>
        <input type="text" name="marka" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Model</label>
        <input type="text" name="model" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Plaka</label>
        <input type="text" name="plaka" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Kamera</label>
        <select name="kamera" class="form-select" required>
          <option value="Var">Var</option>
          <option value="Yok" selected>Yok</option>
        </select>
      </div>
      <div class="mb-3">
        <label>GPS</label>
        <select name="gps" class="form-select" required>
          <option value="Var">Var</option>
          <option value="Yok" selected>Yok</option>
        </select>
      </div>
      <div class="mb-3">
        <label>Araç Sahibi</label>
        <input type="text" name="sahip" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Telefon</label>
        <input type="text" name="telefon" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Kategori</label>
        <select name="kategori_id" class="form-select" required>
          <?php
          $kategoriler->data_seek(0);
          while($k = $kategoriler->fetch_assoc()){ ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['ad']) ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="mb-3">
        <label>Araç Resmi</label>
        <input type="file" name="resim" class="form-control" accept="image/*" capture="camera">
      </div>
      <button class="btn btn-success" name="kaydet">Kaydet</button>
    </form>
  </div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>