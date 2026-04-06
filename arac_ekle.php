<?php
require_once "db.php";

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

$mesaj = '';

if(isset($_POST['kaydet'])){
    $marka = $baglanti->real_escape_string($_POST['marka']);
    $model = $baglanti->real_escape_string($_POST['model']);
    $plaka = $baglanti->real_escape_string($_POST['plaka']);
    $sahip = $baglanti->real_escape_string($_POST['sahip']);
    $telefon = $baglanti->real_escape_string($_POST['telefon']);

    $sql = "INSERT INTO araclar (marka, model, plaka, sahip, telefon)
            VALUES ('$marka', '$model', '$plaka', '$sahip', '$telefon')";
    
    if($baglanti->query($sql)){
        $mesaj = "✅ Araç başarıyla kaydedildi!";
    } else {
        $mesaj = "❌ Hata: ".$baglanti->error;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#667eea">
<meta name="description" content="Stok Yönetimi Sistemi">
<link rel="manifest" href="manifest.json">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg,#1a1a1a,#0d6efd); color:#fff; font-family:'Segoe UI'; min-height:100vh; }
.card { background-color: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius:15px; padding:30px; box-shadow:0 8px 20px rgba(0,0,0,0.3); margin-top:30px; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
<div class="card">
<h3 class="mb-4">🚗 Araç Ekle</h3>

<?php if($mesaj != ''): ?>
<div class="alert alert-light text-dark"><?= $mesaj ?></div>
<?php endif; ?>

<form action="" method="POST">
  <div class="mb-3">
    <label>Marka</label>
    <input type="text" name="marka" class="form-control" placeholder="Araç Markası" required>
  </div>
  <div class="mb-3">
    <label>Model</label>
    <input type="text" name="model" class="form-control" placeholder="Araç Modeli" required>
  </div>
  <div class="mb-3">
    <label>Plaka</label>
    <input type="text" name="plaka" class="form-control" placeholder="Araç Plakası" required>
  </div>
  <div class="mb-3">
    <label>Sahip</label>
    <input type="text" name="sahip" class="form-control" placeholder="Araç Sahibi" required>
  </div>
  <div class="mb-3">
    <label>Telefon</label>
    <input type="tel" name="telefon" class="form-control" placeholder="İletişim Telefonu" required>
  </div>
  <button type="submit" name="kaydet" class="btn btn-success">Kaydet</button>
</form>
</div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- PWA Offline Desteği -->
<script src="app.js"></script>
</body>
</html>