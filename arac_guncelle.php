<?php
// arac_guncelle.php
require_once "db.php"; // db.php içinde session_start() güvenli

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// Mevcut veriyi çek
$arac = $baglanti->query("SELECT * FROM araclar WHERE id=$id")->fetch_assoc();
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");

if(!$arac){
    die("Araç bulunamadı.");
}

$msg = "";
if(isset($_POST['guncelle'])){
    $marka = $_POST['marka'];
    $model = $_POST['model'];
    $plaka = $_POST['plaka'];
    $camera = $_POST['camera'] ?? 'Yok';
    $gps = $_POST['gps'] ?? 'Yok';
    $sahip = $_POST['sahip'];
    $telefon = $_POST['telefon'];
    $kategori_id = (int)$_POST['kategori_id'];

    $resim = $arac['resim'];
    if(isset($_FILES['resim']) && $_FILES['resim']['tmp_name'] != ''){
        $resim = time().'_'.basename($_FILES['resim']['name']);
        if(!is_dir('uploads')){
            mkdir('uploads', 0777, true);
        }
        if(!move_uploaded_file($_FILES['resim']['tmp_name'], 'uploads/'.$resim)){
            $msg = "Resim yüklenirken hata oluştu!";
        }
    }

    if($msg === ''){
        $stmt = $baglanti->prepare("UPDATE araclar SET marka=?, model=?, plaka=?, camera=?, gps=?, sahip=?, telefon=?, kategori_id=?, resim=? WHERE id=?");
        if(!$stmt){
            die("Prepare hatası: " . $baglanti->error);
        }

        $stmt->bind_param("sssssssssi", $marka, $model, $plaka, $camera, $gps, $sahip, $telefon, $kategori_id, $resim, $id);

        if($stmt->execute()){
            $msg = "✅ Araç başarıyla güncellendi.";
            $arac = $baglanti->query("SELECT * FROM araclar WHERE id=$id")->fetch_assoc();
        } else {
            $msg = "❌ Güncelleme hatası: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Araç Güncelle</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color:#f5f6fa; color:#2f3640; font-family:'Segoe UI', sans-serif; }
.card { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); padding:20px; }
img { max-width:150px; border-radius:8px; margin-top:10px; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container mt-5">
  <h3 class="mb-4">🚚 Araç Güncelle</h3>
  <?php if($msg): ?>
    <div class="alert alert-info"><?= $msg ?></div>
  <?php endif; ?>
  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label>Marka</label>
        <input type="text" name="marka" value="<?= htmlspecialchars($arac['marka']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Model</label>
        <input type="text" name="model" value="<?= htmlspecialchars($arac['model']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Plaka</label>
        <input type="text" name="plaka" value="<?= htmlspecialchars($arac['plaka']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Kamera</label>
        <select name="camera" class="form-select" required>
          <option value="Var" <?= $arac['camera']=='Var'?'selected':'' ?>>Var</option>
          <option value="Yok" <?= $arac['camera']=='Yok'?'selected':'' ?>>Yok</option>
        </select>
      </div>
      <div class="mb-3">
        <label>GPS</label>
        <select name="gps" class="form-select" required>
          <option value="Var" <?= $arac['gps']=='Var'?'selected':'' ?>>Var</option>
          <option value="Yok" <?= $arac['gps']=='Yok'?'selected':'' ?>>Yok</option>
        </select>
      </div>
      <div class="mb-3">
        <label>Araç Sahibi</label>
        <input type="text" name="sahip" value="<?= htmlspecialchars($arac['sahip']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Telefon</label>
        <input type="text" name="telefon" value="<?= htmlspecialchars($arac['telefon']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Kategori</label>
        <select name="kategori_id" class="form-select" required>
          <?php
          $kategoriler->data_seek(0);
          while($k = $kategoriler->fetch_assoc()){ ?>
          <option value="<?= $k['id'] ?>" <?= $arac['kategori_id']==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['ad']) ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="mb-3">
        <label>Araç Resmi</label>
        <input type="file" name="resim" class="form-control" accept="image/*" capture="camera">
        <?php if($arac['resim']): ?>
          <img src="uploads/<?= $arac['resim'] ?>" alt="Araç Resmi">
        <?php endif; ?>
      </div>
      <button class="btn btn-success" name="guncelle">Güncelle</button>
    </form>
  </div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>