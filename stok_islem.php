<?php include "db.php"; $id=$_GET['id'];
if($_POST){
    $miktar=$_POST['miktar']; $tip=$_POST['tip']; $kisi=$_POST['kisi'] ?? '';

    if($tip=="giris"){ $baglanti->query("UPDATE malzemeler SET adet=adet+$miktar WHERE id=$id"); }
    else{ 
        $baglanti->query("UPDATE malzemeler SET adet=adet-$miktar WHERE id=$id"); 
        $baglanti->query("INSERT INTO zimmet (malzeme_id,alan_kisi,miktar) VALUES ($id,'$kisi',$miktar)");
    }

    $baglanti->query("INSERT INTO stok_hareket (malzeme_id,islem,miktar) VALUES ($id,'$tip',$miktar)");
    header("Location:index.php");
}
?>
<form method="post">
<input type="number" name="miktar" placeholder="Miktar">
<select name="tip"><option value="giris">Giriş</option><option value="cikis">Çıkış / Zimmet</option></select>
<input name="kisi" placeholder="Kime verildi">
<button>Kaydet</button>
</form>