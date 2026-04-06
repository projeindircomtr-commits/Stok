<?php
include "db.php"; 
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }

// Kategoriler ve lokasyonlar
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");
$lokasyonlar = $baglanti->query("SELECT * FROM lokasyonlar");

// Toplam sayılar
$malzeme_sayi = $baglanti->query("SELECT COUNT(*) as sayi FROM malzemeler")->fetch_assoc()['sayi'];
$arac_sayi = $baglanti->query("SELECT COUNT(*) as sayi FROM araclar")->fetch_assoc()['sayi'];

// Son eklenen malzemeler (stok ≤1 olanlar uyarı)
$stok_uyarilari = $baglanti->query("SELECT * FROM malzemeler WHERE adet <= 1 ORDER BY adet ASC");

// Grafik verisi (malzemeler kategoriye göre)
$kategori_label = [];
$kategori_data = [];
$kat_query = $baglanti->query("SELECT k.ad, SUM(m.adet) as toplam_adet 
                               FROM malzemeler m 
                               LEFT JOIN kategoriler k ON m.kategori_id=k.id 
                               GROUP BY k.id");
while($row = $kat_query->fetch_assoc()){
    $kategori_label[] = $row['ad'];
    $kategori_data[] = $row['toplam_adet'];
}

// Grafik verisi (araçlar kategoriye göre)
$arac_label = [];
$arac_data = [];
$arac_query = $baglanti->query("SELECT k.ad, COUNT(a.id) as toplam_adet 
                                FROM araclar a 
                                LEFT JOIN kategoriler k ON a.kategori_id=k.id 
                                GROUP BY k.id");
while($row = $arac_query->fetch_assoc()){
    $arac_label[] = $row['ad'];
    $arac_data[] = $row['toplam_adet'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Şantiye Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { 
    font-family:'Segoe UI', sans-serif; 
    background: linear-gradient(135deg,#1a1a1a,#0d6efd); 
    color: #fff; 
    min-height: 100vh;
}
.card { 
    border-radius: 15px; 
    box-shadow: 0 8px 20px rgba(0,0,0,0.3); 
    transition: 0.3s; 
    background: rgba(255,255,255,0.1); 
    backdrop-filter: blur(10px); 
    cursor:pointer;
}
.card:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.5); }

.icon-circle { width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto; }
.bg-primary { background-color: #0d6efd !important; }
.bg-success { background-color: #198754 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-info { background-color: #0dcaf0 !important; }

.card h5 { font-weight: bold; color:#fff; }
.card p { font-size: 1.4rem; font-weight: 600; margin:0; color:#fff; }

.alert-light { background: rgba(255,255,255,0.2); color:#fff; }

@media (max-width:768px){
    .card h5 { font-size:1rem; }
    .card p { font-size:1.2rem; }
}
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container mt-5">

  <!-- Genel Kartlar -->
  <div class="row g-4">
    <div class="col-md-3 col-sm-6">
      <a href="malzemeler.php" class="text-decoration-none">
        <div class="card p-4 text-center">
          <div class="icon-circle bg-primary mb-3"><i class="bi bi-box-seam fs-3"></i></div>
          <h5>Malzemeler</h5>
          <p><?= $malzeme_sayi ?> adet</p>
        </div>
      </a>
    </div>

    <div class="col-md-3 col-sm-6">
      <a href="araclar.php" class="text-decoration-none">
        <div class="card p-4 text-center">
          <div class="icon-circle bg-success mb-3"><i class="bi bi-truck fs-3"></i></div>
          <h5>Araçlar</h5>
          <p><?= $arac_sayi ?> adet</p>
        </div>
      </a>
    </div>

    <div class="col-md-3 col-sm-6">
      <div class="card p-4 text-center">
        <div class="icon-circle bg-warning mb-3"><i class="bi bi-tags fs-3"></i></div>
        <h5>Kategoriler</h5>
        <p><?= $kategoriler->num_rows ?></p>
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <div class="card p-4 text-center">
        <div class="icon-circle bg-info mb-3"><i class="bi bi-geo-alt fs-3"></i></div>
        <h5>Lokasyonlar</h5>
        <p><?= $lokasyonlar->num_rows ?></p>
      </div>
    </div>
  </div>

  <!-- Stok Uyarıları -->
  <?php if($stok_uyarilari->num_rows > 0): ?>
  <div class="card mt-5 p-3">
    <h5>⚠️ Stok Uyarıları</h5>
    <ul class="list-group list-group-flush mt-2">
      <?php while($m = $stok_uyarilari->fetch_assoc()): ?>
        <li class="list-group-item" style="background: rgba(255,0,0,0.2); color:#fff;">
          <?= $m['ad'] ?> - <?= $m['adet'] ?> adet kaldı
        </li>
      <?php endwhile; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Grafikler -->
  <div class="row mt-5">
    <div class="col-md-6">
      <div class="card p-3">
        <h5>Malzemeler Adet Dağılımı (Kategoriye Göre)</h5>
        <canvas id="malzemeChart"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h5>Araçlar Dağılımı (Kategoriye Göre)</h5>
        <canvas id="aracChart"></canvas>
      </div>
    </div>
  </div>

</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ctx1 = document.getElementById('malzemeChart').getContext('2d');
const malzemeChart = new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: <?= json_encode($kategori_label) ?>,
        datasets: [{
            label: 'Toplam Adet',
            data: <?= json_encode($kategori_data) ?>,
            backgroundColor: 'rgba(0,123,255,0.7)',
            borderColor: 'rgba(0,123,255,1)',
            borderWidth: 1
        }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
});

const ctx2 = document.getElementById('aracChart').getContext('2d');
const aracChart = new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: <?= json_encode($arac_label) ?>,
        datasets: [{
            label: 'Toplam Adet',
            data: <?= json_encode($arac_data) ?>,
            backgroundColor: 'rgba(25,135,84,0.7)',
            borderColor: 'rgba(25,135,84,1)',
            borderWidth: 1
        }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
});
</script>

</body>
</html>