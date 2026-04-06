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
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f0f2f5; font-family:'Segoe UI', sans-serif; }
.table { background:#fff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.table th, .table td { vertical-align:middle; text-align:center; }
.img-thumb { width:60px; height:60px; object-fit:cover; border-radius:8px; }
.card { border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.btn-group { margin-bottom:10px; }
</style>
</head>
<body>

<?php include "header.php"; ?>

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

  <!-- Excel, PDF ve Yazdır Butonları -->
  <div class="btn-group mb-3">
    <button class="btn btn-success" onclick="exportTableToExcel('malzeme-table', 'malzemeler')">Excel Çıktısı</button>
    <button class="btn btn-danger" onclick="exportTableToPDF()">PDF Çıktısı</button>
    <button class="btn btn-secondary" onclick="printTable()">Yazdır</button>
  </div>

  <table class="table table-hover" id="malzeme-table">
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
      <tr id="malzeme-<?= $m['id'] ?>">
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
          <button class="btn btn-danger btn-sm" onclick="silMalzeme(<?= $m['id'] ?>)">Sil</button>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include "footer.php"; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
// AJAX ile malzeme silme
function silMalzeme(id){
    if(confirm("Silmek istediğine emin misin?")){
        fetch('sil_ajax.php?id=' + id)
        .then(response => response.text())
        .then(data => {
            if(data === "ok"){
                document.getElementById('malzeme-' + id).remove();
            } else {
                alert("Hata oluştu: " + data);
            }
        });
    }
}

// Excel
function exportTableToExcel(tableID, filename=''){
    let table = document.getElementById(tableID);
    let wb = XLSX.utils.table_to_book(table, {sheet:"Sheet1"});
    XLSX.writeFile(wb, filename+".xlsx");
}

// PDF
function exportTableToPDF(){
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF();
    doc.autoTable({
        html: '#malzeme-table',
        columnStyles: {0:{cellWidth:20},5:{cellWidth:40}}, // Resim ve İşlem sütunu dar
        didDrawCell: function(data){
            if(data.column.index===0 || data.column.index===5) data.cell.text = '';
        }
    });
    doc.save('malzemeler.pdf');
}

// Yazdır
function printTable(){
    let divToPrint = document.getElementById("malzeme-table");
    let newWin = window.open("");
    newWin.document.write("<html><head><title>Malzemeler</title>");
    newWin.document.write(`
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Segoe UI', sans-serif; margin:20px; }
            table { width:100%; border-collapse: collapse; }
            th, td { border:1px solid #000 !important; padding:5px; text-align:center; vertical-align:middle; }
            img { width:50px; height:50px; object-fit:cover; border-radius:5px; }
            @media print { button, input { display:none; } }
        </style>
    `);
    newWin.document.write("</head><body>");
    newWin.document.write('<h3>Malzemeler Listesi</h3>');
    newWin.document.write(divToPrint.outerHTML);
    newWin.document.write("</body></html>");
    newWin.document.close();
    newWin.focus();
    newWin.print();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<!-- PWA Offline Desteği -->
<script src="app.js"></script>