<?php
// footer.php

date_default_timezone_set('Europe/Istanbul');

// Şehir ve para birimi seçimi (GET veya varsayılan)
$sehir = isset($_GET['sehir']) ? $_GET['sehir'] : 'Istanbul';
$para = isset($_GET['para']) ? $_GET['para'] : 'USD';

// Hava durumu API'siz (wttr.in)
$weather = @file_get_contents("https://wttr.in/".urlencode($sehir)."?format=3");
if(!$weather) $weather = "Hava durumu alınamadı";

// Kur ve piyasa verileri (TCMB ve alternatif)
$kur = [
    'USD' => '0',
    'EUR' => '0',
    'ALTIN' => '0',
    'GRAMALTIN' => '0'
];

// TCMB JSON örnek (USD ve EUR)
$tcmb_data = @file_get_contents('https://www.tcmb.gov.tr/kurlar/today.xml');
if($tcmb_data){
    $xml = simplexml_load_string($tcmb_data);
    $kur['USD'] = (string)$xml->Currency[0]->ForexSelling;
    $kur['EUR'] = (string)$xml->Currency[3]->ForexSelling;
}

// Altın fiyatı (Gram ve Ons) – public site scraping (basit örnek)
$altin_json = @file_get_contents('https://api.genelpara.com/embed/doviz.json'); // public json
if($altin_json){
    $altin_data = json_decode($altin_json,true);
    if(isset($altin_data['gram-altin']['satis'])) $kur['GRAMALTIN'] = $altin_data['gram-altin']['satis'];
    if(isset($altin_data['altin']['ons'])) $kur['ALTIN'] = $altin_data['altin']['ons'];
}

// Şehir listesi
$sehirler = ['Istanbul','Ankara','Izmir','Antalya','Bursa','Adana','Trabzon'];
?>

<style>
footer { background:#1e272e; color:#fff; padding:20px 0; font-family:'Segoe UI'; }
footer a { color:#00a8ff; text-decoration:none; }
footer a:hover { text-decoration:underline; }
.footer-container { display:flex; flex-wrap:wrap; justify-content:space-around; align-items:center; }
.footer-section { margin:10px; text-align:center; min-width:180px; }
select, option { padding:5px; border-radius:5px; border:none; }
</style>

<footer>
<div class="container footer-container">
    <div class="footer-section">
        <strong>Tarih & Saat:</strong><br>
        <?= date('d.m.Y H:i:s') ?>
    </div>

    <div class="footer-section">
        <form method="GET" style="display:inline;">
            <strong>Hava Durumu:</strong><br>
            <select name="sehir" onchange="this.form.submit()">
                <?php foreach($sehirler as $s): ?>
                    <option value="<?= $s ?>" <?= $s==$sehir?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <p style="margin:5px 0"><?= $weather ?></p>
    </div>

    <div class="footer-section">
        <form method="GET" style="display:inline;">
            <strong>Piyasa / Kur:</strong><br>
            <select name="para" onchange="this.form.submit()">
                <option value="USD" <?= $para=='USD'?'selected':'' ?>>USD / TL</option>
                <option value="EUR" <?= $para=='EUR'?'selected':'' ?>>EUR / TL</option>
                <option value="ALTIN" <?= $para=='ALTIN'?'selected':'' ?>>Altın / Ons</option>
                <option value="GRAMALTIN" <?= $para=='GRAMALTIN'?'selected':'' ?>>Gram Altın</option>
            </select>
        </form>
        <p style="margin:5px 0">
            <?php
            if(isset($kur[$para])) echo $kur[$para].($para=='ALTIN'?' USD':($para=='GRAMALTIN'?' TL':' TL'));
            else echo "Veri yok";
            ?>
        </p>
    </div>

    <div class="footer-section">
        Yapımcı: <strong>Muhammed Salman</strong>
    </div>
</div>
</footer>

<!-- PWA Offline Desteği -->
<script src="app.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('service-worker-v2.js')
                .then(registration => {
                    console.log('✅ Service Worker kaydedildi');
                })
                .catch(error => {
                    console.error('Service Worker kaydı başarısız:', error);
                });
        });
    }
</script>