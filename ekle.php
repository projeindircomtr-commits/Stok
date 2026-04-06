<?php
// ekle.php
require_once "db.php"; // db.php içinde session_start() var, tekrar start etmeye gerek yok

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// Kategoriler ve lokasyonlar
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");
$lokasyonlar = $baglanti->query("SELECT * FROM lokasyonlar");

$mesaj = '';

if(isset($_POST['kaydet'])){
    $ad = $baglanti->real_escape_string($_POST['ad']);
    $adet = intval($_POST['adet']);
    $kategori_id = intval($_POST['kategori_id']);
    $lokasyon = $baglanti->real_escape_string($_POST['lokasyon']);

    $resimAdi = null;

    if(isset($_FILES['resim']) && $_FILES['resim']['error'] == 0){
        $hedefKlasor = 'uploads/';
        if(!is_dir($hedefKlasor)){
            mkdir($hedefKlasor, 0777, true);
        }

        $resimAdi = time().'_'.preg_replace('/[^a-zA-Z0-9_.]/','_',$_FILES['resim']['name']);
        $hedef = $hedefKlasor.$resimAdi;

        if(!move_uploaded_file($_FILES['resim']['tmp_name'], $hedef)){
            $mesaj = "❌ Resim yüklenemedi! uploads klasör izinlerini kontrol et veya dosya boyutu çok büyük.";
        }
    }

    $sql = "INSERT INTO malzemeler (ad, adet, kategori_id, lokasyon, resim)
            VALUES ('$ad', '$adet', '$kategori_id', '$lokasyon', '$resimAdi')";
    if($baglanti->query($sql)){
        $mesaj = "✅ Malzeme başarıyla kaydedildi!";
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg,#1a1a1a,#0d6efd); color:#fff; font-family:'Segoe UI'; min-height:100vh; }
.card { background-color: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius:15px; padding:30px; box-shadow:0 8px 20px rgba(0,0,0,0.3); margin-top:30px; }
</style>
</head>
<body>

<?php include "header.php"; ?> <!-- Navbar include -->

<div class="container">
<div class="card">
<h3 class="mb-4">+ Malzeme Ekle</h3>

<?php if($mesaj != ''): ?>
<div class="alert alert-light text-dark"><?= $mesaj ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label>Malzeme Adı</label>
    <input type="text" name="ad" class="form-control" placeholder="Malzeme Adı" required>
  </div>
  <div class="mb-3">
    <label>Adet</label>
    <input type="number" name="adet" class="form-control" placeholder="Adet" required>
  </div>
  <div class="mb-3">
    <label>Kategori</label>
    <select name="kategori_id" class="form-control" required>
      <option value="">Seçiniz</option>
      <?php
      $kategoriler->data_seek(0);
      while($k=$kategoriler->fetch_assoc()): ?>
        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['ad']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="mb-3">
    <label>Lokasyon</label>
    <select name="lokasyon" class="form-control" required>
      <option value="">Seçiniz</option>
      <?php
      $lokasyonlar->data_seek(0);
      while($l=$lokasyonlar->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($l['ad']) ?>"><?= htmlspecialchars($l['ad']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="mb-3">
    <label>Resim Yükle / Kamera ile Çek</label>
    <input type="file" name="resim" class="form-control" accept="image/*" capture="environment">
    <small class="text-light">Mobil cihazlarda direkt kamera açılacak</small>
  </div>
  <button type="submit" name="kaydet" class="btn btn-success">Kaydet</button>
</form>
</div>
</div>

<?php include "footer.php"; ?> <!-- Footer include -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>