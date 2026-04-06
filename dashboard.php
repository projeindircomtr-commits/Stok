<?php
include "db.php"; 
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }

// --- Genel Sayılar ---
$malzeme_sayi = $baglanti->query("SELECT COUNT(*) as toplam FROM malzemeler")->fetch_assoc()['toplam'];
$arac_sayi = $baglanti->query("SELECT COUNT(*) as toplam FROM araclar")->fetch_assoc()['toplam'];
$kategori_sayi = $baglanti->query("SELECT COUNT(*) as toplam FROM kategoriler")->fetch_assoc()['toplam'];
$lokasyon_sayi = $baglanti->query("SELECT COUNT(*) as toplam FROM lokasyonlar")->fetch_assoc()['toplam'];

// --- Son eklenenler ---
$son_malzemeler = $baglanti->query("SELECT * FROM malzemeler ORDER BY id DESC LIMIT 5");
$son_araclar = $baglanti->query("SELECT a.*, k.ad as kategori FROM araclar a LEFT JOIN kategoriler k ON a.kategori_id=k.id ORDER BY a.id DESC LIMIT 5");

// --- Malzeme istatistikleri (kategori bazlı) ---
$malzeme_label = [];
$malzeme_data = [];
$kat_query = $baglanti->query("SELECT k.ad, SUM(m.adet) as toplam_adet FROM malzemeler m LEFT JOIN kategoriler k ON m.kategori_id=k.id GROUP BY k.id");
while($row = $kat_query->fetch_assoc()){
    $malzeme_label[] = $row['ad'];
    $malzeme_data[] = $row['toplam_adet'];
}

// --- Araç istatistikleri (kategori bazlı) ---
$arac_label = [];
$arac_data = [];
$arac_query = $baglanti->query("SELECT k.ad, COUNT(a.id) as toplam_arac FROM araclar a LEFT JOIN kategoriler k ON a.kategori_id=k.id GROUP BY k.id");
while($row = $arac_query->fetch_assoc()){
    $arac_label[] = $row['ad'];
    $arac_data[] = $row['toplam_arac'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Şantiye Stok Premium</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { background: linear-gradient(135deg,#1a1a1a,#0d6efd); color:#fff; font-family:'Segoe UI'; min-height:100vh; }
.card { background-color: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border-radius:15px; padding:25px; box-shadow:0 8px 25px rgba(0,0,0,0.4); margin-bottom:25px; transition: transform .3s ease, box-shadow .3s ease; }
.card:hover { transform: translateY(-5px); box-shadow:0 12px 35px rgba(0,0,0,0.5); }
.card h5 { color:#fff; font-weight:600; }
.list-group-item { background: rgba(255,255,255,0.05); color:#fff; border:none; transition: background .3s; }
.list-group-item:hover { background: rgba(0,123,255,0.2); color:#fff; border-radius:8px; }
.card-header { background: rgba(255,255,255,0.15); font-weight:600; }
.stats-icon { font-size:2.2rem; margin-right:12px; }
</style>
</head>
<body>
<!-- PWA Offline Desteği -->
<script src="app.js"></script>
<?php include "header.php"; ?>

<div class="container mt-4">

<h2 class="mb-4 text-center">📊 Şantiye Stok Dashboard Premium</h2>

<!-- Toplam Bilgiler Kartları -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card text-center p-3">
            <h5><span class="stats-icon">📦</span> Toplam Malzeme</h5>
            <h3><?= $malzeme_sayi ?></h3>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center p-3">
            <h5><span class="stats-icon">🚚</span> Toplam Araç</h5>
            <h3><?= $arac_sayi ?></h3>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center p-3">
            <h5><span class="stats-icon">🗂️</span> Kategori Sayısı</h5>
            <h3><?= $kategori_sayi ?></h3>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center p-3">
            <h5><span class="stats-icon">📍</span> Lokasyon Sayısı</h5>
            <h3><?= $lokasyon_sayi ?></h3>
        </div>
    </div>
</div>

<!-- Malzeme Grafikleri -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Malzemeler - Kategoriye Göre (Bar)</div>
            <canvas id="malzemeBarChart"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Malzemeler - Kategoriye Göre (Pasta)</div>
            <canvas id="malzemePieChart"></canvas>
        </div>
    </div>
</div>

<!-- Araç Grafikleri -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Araçlar - Kategoriye Göre (Bar)</div>
            <canvas id="aracBarChart"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Araçlar - Kategoriye Göre (Pasta)</div>
            <canvas id="aracPieChart"></canvas>
        </div>
    </div>
</div>

<div class="row">
    <!-- Son Eklenen Malzemeler -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">En Son Eklenen Malzemeler</div>
            <ul class="list-group list-group-flush">
                <?php while($m=$son_malzemeler->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $m['ad'] ?> (<?= $m['adet'] ?> adet)
                    <span><?= $m['lokasyon'] ?></span>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Son Eklenen Araçlar -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">En Son Eklenen Araçlar</div>
            <ul class="list-group list-group-flush">
                <?php while($a=$son_araclar->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $a['ad'] ?> 
                    <span><?= $a['kategori'] ?></span>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</div>

</div>

<script>
const malzemeBarCtx = document.getElementById('malzemeBarChart').getContext('2d');
const malzemePieCtx = document.getElementById('malzemePieChart').getContext('2d');
const aracBarCtx = document.getElementById('aracBarChart').getContext('2d');
const aracPieCtx = document.getElementById('aracPieChart').getContext('2d');

const barOptions = { 
    responsive:true, 
    plugins:{ 
        legend:{display:false}, 
        tooltip:{ enabled:true, backgroundColor:'rgba(0,0,0,0.8)', titleColor:'#fff', bodyColor:'#fff', padding:10 } 
    },
    animation:{ duration:1500, easing:'easeOutBounce' },
    scales:{ y:{ beginAtZero:true } }
};

// Malzeme Bar
new Chart(malzemeBarCtx, {
    type:'bar',
    data:{ labels: <?= json_encode($malzeme_label) ?>, datasets:[{ label:'Toplam Adet', data: <?= json_encode($malzeme_data) ?>, backgroundColor: 'rgba(0,123,255,0.8)', borderColor:'rgba(0,123,255,1)', borderWidth:1, hoverBackgroundColor:'rgba(0,123,255,1)' }]},
    options: barOptions
});

// Malzeme Pie
new Chart(malzemePieCtx,{
    type:'pie',
    data:{ labels: <?= json_encode($malzeme_label) ?>, datasets:[{ data: <?= json_encode($malzeme_data) ?>, backgroundColor: ['#007bff','#17a2b8','#ffc107','#28a745','#dc3545','#6f42c1','#fd7e14','#20c997'], hoverOffset:15 }]},
    options:{ responsive:true, plugins:{legend:{position:'bottom', labels:{color:'#fff'}}, tooltip:{enabled:true, backgroundColor:'rgba(0,0,0,0.85)', titleColor:'#fff', bodyColor:'#fff', padding:12 }}, animation:{animateScale:true, duration:1500} }
});

// Araç Bar
new Chart(aracBarCtx,{
    type:'bar',
    data:{ labels: <?= json_encode($arac_label) ?>, datasets:[{ label:'Toplam Araç', data: <?= json_encode($arac_data) ?>, backgroundColor: 'rgba(40,167,69,0.8)', borderColor:'rgba(40,167,69,1)', borderWidth:1, hoverBackgroundColor:'rgba(40,167,69,1)' }]},
    options: barOptions
});

// Araç Pie
new Chart(aracPieCtx,{
    type:'pie',
    data:{ labels: <?= json_encode($arac_label) ?>, datasets:[{ data: <?= json_encode($arac_data) ?>, backgroundColor: ['#28a745','#20c997','#fd7e14','#ffc107','#dc3545','#007bff','#6f42c1','#17a2b8'], hoverOffset:15 }]},
    options:{ responsive:true, plugins:{legend:{position:'bottom', labels:{color:'#fff'}}, tooltip:{enabled:true, backgroundColor:'rgba(0,0,0,0.85)', titleColor:'#fff', bodyColor:'#fff', padding:12 }}, animation:{animateScale:true, duration:1500} }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>