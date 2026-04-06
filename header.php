<?php
include "db.php"; 
if(!isset($_SESSION['login'])){ 
    header("Location: login.php"); 
    exit; 
}

$malzeme_sayi = $baglanti->query("SELECT COUNT(*) as sayi FROM malzemeler")->fetch_assoc()['sayi'];
$arac_sayi = $baglanti->query("SELECT COUNT(*) as sayi FROM araclar")->fetch_assoc()['sayi'];
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");
$lokasyon_sayi = $baglanti->query("SELECT COUNT(*) as sayi FROM lokasyonlar")->fetch_assoc()['sayi'];
$kullanici_sayi = $baglanti->query("SELECT COUNT(*) as sayi FROM kullanicilar")->fetch_assoc()['sayi'];
?>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#00a8ff">

<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then(reg => console.log('SW registered', reg))
      .catch(err => console.log('SW registration failed', err));
  });
}
</script>

<style>
.navbar { background-color: #1e272e; }
.navbar-brand { font-weight: bold; font-size: 1.3rem; color: #00a8ff !important; }
.nav-link { color: #dcdde1 !important; transition: 0.2s; display: flex; align-items: center; }
.nav-link:hover { color: #00a8ff !important; }
.navbar-toggler { border-color: #00a8ff; }
.navbar-toggler-icon { background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255,255,255,1%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E"); }
.dropdown-menu { background-color:#2f3640; }
.dropdown-item { color:#dcdde1; display: flex; align-items: center; }
.dropdown-item:hover { background-color:#00a8ff; color:#fff; }
.dropdown-item i { margin-right:8px; }
</style>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="fas fa-hard-hat"></i> Salman Şantiye Takip</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Anasayfa</a></li>

        <!-- Malzemeler Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-box"></i> Malzemeler (<?= $malzeme_sayi ?>)
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="malzemeler.php"><i class="fas fa-list"></i> Tüm Malzemeler</a></li>
            <li><a class="dropdown-item" href="ekle.php"><i class="fas fa-plus"></i> Malzeme Ekle</a></li>
          </ul>
        </li>

        <!-- Araçlar Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-truck"></i> Araçlar (<?= $arac_sayi ?>)
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="araclar.php"><i class="fas fa-list"></i> Tüm Araçlar</a></li>
            <li><a class="dropdown-item" href="arac_ekle.php"><i class="fas fa-plus"></i> Araç Ekle</a></li>
          </ul>
        </li>

        <!-- Kategoriler -->
        <li class="nav-item"><a class="nav-link" href="kategoriler.php"><i class="fas fa-tags"></i> Kategoriler (<?= $kategoriler->num_rows ?>)</a></li>

        <!-- Lokasyonlar -->
        <li class="nav-item"><a class="nav-link" href="lokasyon.php"><i class="fas fa-map-marker-alt"></i> Lokasyonlar (<?= $lokasyon_sayi ?>)</a></li>

        <!-- Kullanıcılar -->
        <li class="nav-item"><a class="nav-link" href="kullanici_ekle.php"><i class="fas fa-users"></i> Kullanıcılar (<?= $kullanici_sayi ?>)</a></li>

        <!-- Dashboard -->
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-chart-bar"></i> Dashboard</a></li>

        <!-- Çıkış -->
        <li class="nav-item"><a class="nav-link btn btn-danger text-white ms-2" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
      </ul>
    </div>
  </div>
</nav>