window.addEventListener('online', function(){
    let offline = localStorage.getItem('malzemeler_offline');
    if(offline){
        let malzemeler = JSON.parse(offline);
        malzemeler.forEach(m=>{
            fetch('malzeme_insert.php', {
                method: 'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify(m)
            });
        });
        localStorage.removeItem('malzemeler_offline');
        alert("Offline veriler servera aktarıldı!");
    }
});