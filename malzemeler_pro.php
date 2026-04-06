<?php
include "db.php"; 
if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// Arama ve filtre
$ara = isset($_GET['ara']) ? $baglanti->real_escape_string($_GET['ara']) : '';
$kategori_id = isset($_GET['kategori_id']) ? intval($_GET['kategori_id']) : 0;
$lokasyon = isset($_GET['lokasyon']) ? $baglanti->real_escape_string($_GET['lokasyon']) : '';

// Kategoriler ve lokasyonlar
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");
$lokasyonlar = $baglanti->query("SELECT * FROM lokasyonlar");

// Malzemeler sorgu
$sql = "SELECT m.*, k.ad as kategori FROM malzemeler m LEFT JOIN kategoriler k ON m.kategori_id=k.id WHERE 1";
if($ara != '') $sql .= " AND m.ad LIKE '%$ara%'";
if($kategori_id > 0) $sql .= " AND m.kategori_id = $kategori_id";
if($lokasyon != '') $sql .= " AND m.lokasyon = '$lokasyon'";
$sql .= " ORDER BY m.id DESC";
$malzemeler = $baglanti->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f0f2f5; font-family:'Segoe UI'; }
.navbar { background-color:#0d6efd; }
.navbar a { color:#fff !important; font-weight:600; }
.navbar .nav-link:hover { background-color: rgba(255,255,255,0.2); border-radius:8px; }
.table { background:#fff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.table th, .table td { vertical-align:middle; text-align:center; }
.img-thumb { width:60px; height:60px; object-fit:cover; border-radius:8px; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">📦 Şantiye Stok</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="ekle.php">+ Malzeme Ekle</a></li>
        <li class="nav-item"><a class="nav-link active" href="malzemeler_pro.php">Malzemeler</a></li>
        <li class="nav-item"><a class="nav-link" href="kategoriler.php">Kategoriler</a></li>
        <li class="nav-item"><a class="nav-link" href="lokasyon.php">Lokasyon</a></li>
        <li class="nav-item"><a class="nav-link" href="kullanici_ekle.php">Kullanıcı</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">

<div class="card p-3 mb-3">
  <form method="GET" class="row g-3">
    <div class="col-md-4">
      <input type="text" name="ara" value="<?= htmlspecialchars($ara) ?>" class="form-control" placeholder="Malzeme Ara...">
    </div>
    <div class="col-md-3">
      <select name="kategori_id" class="form-control">
        <option value="0">Tüm Kategoriler</option>
        <?php while($k=$kategoriler->fetch_assoc()): ?>
          <option value="<?= $k['id'] ?>" <?= $kategori_id==$k['id']?'selected':'' ?>><?= $k['ad'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="lokasyon" class="form-control">
        <option value="">Tüm Lokasyonlar</option>
        <?php while($l=$lokasyonlar->fetch_assoc()): ?>
          <option value="<?= $l['ad'] ?>" <?= $lokasyon==$l['ad']?'selected':'' ?>><?= $l['ad'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Filtrele</button>
    </div>
  </form>
</div>

<table class="table table-hover">
  <thead class="table-dark">
    <tr>
      <th>Resim</th>
      <th>Malzeme Adı</th>
      <th>Adet</th>
      <th>Kategori</th>
      <th>Lokasyon</th>
      <th>İşlemler</th>
    </tr>
  </thead>
  <tbody>
    <?php while($m=$malzemeler->fetch_assoc()): ?>
    <tr>
      <td>
        <?php if($m['resim'] && file_exists("uploads/".$m['resim'])): ?>
          <img src="uploads/<?= $m['resim'] ?>" class="img-thumb">
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
      <td><?= $m['ad'] ?></td>
      <td><?= $m['adet'] ?></td>
      <td><?= $m['kategori'] ?></td>
      <td><?= $m['lokasyon'] ?></td>
      <td>
        <a href="guncelle.php?id=<?= $m['id'] ?>" class="btn btn-warning btn-sm">Güncelle</a>
        <a href="sil.php?id=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silmek istediğine emin misin?')">Sil</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>