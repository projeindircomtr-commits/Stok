// Malzeme ekleme offline kaydı
function saveOfflineMalzeme(data){
    let offline = localStorage.getItem('malzemeler_offline');
    let malzemeler = offline ? JSON.parse(offline) : [];
    malzemeler.push(data);
    localStorage.setItem('malzemeler_offline', JSON.stringify(malzemeler));
    alert("Offline kaydedildi, internet geldiğinde otomatik gönderilecek.");
}

// Form submit
document.getElementById('malzemeForm').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = {
        ad: document.getElementById('ad').value,
        adet: document.getElementById('adet').value,
        kategori: document.getElementById('kategori').value,
        lokasyon: document.getElementById('lokasyon').value,
        resim: document.getElementById('resim').value // base64 veya filename
    };

    if(navigator.onLine){
        // online ise direkt server'a gönder
        fetch('malzeme_insert.php', {
            method: 'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(formData)
        }).then(res=>res.text()).then(r=>{
            alert("Servera kaydedildi: "+r);
        });
    } else {
        // offline ise localStorage'a kaydet
        saveOfflineMalzeme(formData);
    }
});