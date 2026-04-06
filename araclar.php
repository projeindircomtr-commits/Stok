<?php
// araclar.php
require_once "db.php";

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// Kategorileri çek
$kategoriler = $baglanti->query("SELECT * FROM kategoriler");

// Seçilen kategori
$kategori_id = $_GET['kategori_id'] ?? '';

// Araçları çek (kategori filtresi varsa uygula)
$kategori_sql = $kategori_id ? "WHERE a.kategori_id=" . (int)$kategori_id : "";
$araclar = $baglanti->query("
    SELECT a.*, k.ad as kategori 
    FROM araclar a 
    LEFT JOIN kategoriler k ON a.kategori_id=k.id
    $kategori_sql
    ORDER BY a.id DESC
");

// Araç sayısı
$arac_sayi = $araclar->num_rows;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Araçlar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f5f6fa; color: #2f3640; font-family:'Segoe UI', sans-serif; }
.table th, .table td { vertical-align: middle; }
img { max-width: 80px; border-radius: 6px; }
.card { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); padding:20px; }
.btn-group { margin-bottom: 10px; }
</style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container mt-5">
  <h3 class="mb-4">🚚 Araçlar Listesi (<?= $arac_sayi ?>)</h3>

  <div class="d-flex justify-content-between mb-3">
    <form method="get" class="d-flex">
      <select name="kategori_id" class="form-select me-2" onchange="this.form.submit()">
        <option value="">Tüm Kategoriler</option>
        <?php
        $kategoriler->data_seek(0);
        while($k = $kategoriler->fetch_assoc()): ?>
          <option value="<?= $k['id'] ?>" <?= ($kategori_id == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['ad']) ?></option>
        <?php endwhile; ?>
      </select>
    </form>

    <div class="btn-group">
      <button class="btn btn-success" onclick="exportTableToExcel('aracTable', 'araclar')">Excel Çıktısı</button>
      <button class="btn btn-danger" onclick="exportTableToPDF()">PDF Çıktısı</button>
      <button class="btn btn-secondary" onclick="printTable()">Yazdır</button>
    </div>
  </div>

  <div class="card">
    <div class="mb-3">
      <input type="text" id="searchInput" class="form-control" placeholder="Tüm sütunlarda ara...">
    </div>

    <div class="table-responsive">
      <table class="table table-hover" id="aracTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Resim</th>
            <th>Marka</th>
            <th>Model</th>
            <th>Plaka</th>
            <th>Kamera</th>
            <th>GPS</th>
            <th>Sahip</th>
            <th>Telefon</th>
            <th>Kategori</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php if($araclar->num_rows > 0): ?>
            <?php while($a = $araclar->fetch_assoc()): ?>
              <tr id="arac-<?= $a['id'] ?>">
                <td><?= $a['id'] ?></td>
                <td><?php if($a['resim']): ?><img src="uploads/<?= $a['resim'] ?>" alt="Araç Resmi" style="width:50px;height:50px;object-fit:cover;border-radius:4px;"><?php else: echo "-"; endif; ?></td>
                <td><?= htmlspecialchars($a['marka']) ?></td>
                <td><?= htmlspecialchars($a['model']) ?></td>
                <td><?= htmlspecialchars($a['plaka']) ?></td>
                <td><?= htmlspecialchars($a['camera']) ?></td>
                <td><?= htmlspecialchars($a['gps']) ?></td>
                <td><?= htmlspecialchars($a['sahip']) ?></td>
                <td><?= htmlspecialchars($a['telefon']) ?></td>
                <td><?= htmlspecialchars($a['kategori']) ?></td>
                <td>
                  <a href="arac_guncelle.php?id=<?= $a['id'] ?>" class="btn btn-warning btn-sm">Güncelle</a>
                  <button class="btn btn-danger btn-sm" onclick="silArac(<?= $a['id'] ?>)">Sil</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="11" class="text-center">Kayıt bulunamadı.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <p id="noResult" class="text-center mt-2" style="display:none;">Kayıt bulunamadı.</p>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
// Araç silme AJAX
function silArac(id){
    if(confirm("Silmek istediğine emin misin?")){
        fetch('arac_sil_ajax.php?id=' + id)
        .then(response => response.text())
        .then(data => {
            if(data === "ok"){
                document.getElementById('arac-' + id).remove();
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
        html: '#aracTable',
        columnStyles: {1:{cellWidth:20},10:{cellWidth:30}}, // Resim ve İşlem sütunu dar
        didDrawCell: function(data){
            if(data.column.index===1 || data.column.index===10) data.cell.text = '';
        }
    });
    doc.save('araclar.pdf');
}

// Yazdır
function printTable(){
    let divToPrint = document.getElementById("aracTable");
    let newWin = window.open("");
    newWin.document.write("<html><head><title>Araçlar</title>");
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
    newWin.document.write('<h3>Araçlar Listesi</h3>');
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