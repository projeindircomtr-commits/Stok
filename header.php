<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Yönetimi - Offline Mod</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .offline-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        
        .offline-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        h1 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        p {
            color: #666;
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        .status-badge {
            display: inline-block;
            background: #ff6b6b;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .features {
            text-align: left;
            margin: 30px 0;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        
        .features h3 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            color: #555;
        }
        
        .feature-item::before {
            content: "✓";
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #51cf66;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 12px;
            font-weight: bold;
        }
        
        .action-buttons {
            margin-top: 30px;
        }
        
        .btn-custom {
            padding: 12px 30px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary-custom {
            background: #e9ecef;
            color: #333;
        }
        
        .btn-secondary-custom:hover {
            background: #dee2e6;
            transform: translateY(-2px);
        }
        
        .sync-status {
            margin-top: 20px;
            padding: 15px;
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            border-radius: 5px;
            display: none;
        }
        
        .sync-status.show {
            display: block;
        }
        
        .sync-status.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .sync-status.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        #connection-status {
            font-size: 14px;
            margin-top: 15px;
            padding: 10px;
            background: #fff3cd;
            border-radius: 5px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">📡</div>
        <div class="status-badge">OFFLINE MOD</div>
        
        <h1>Çevrimdışı Moddasınız</h1>
        <p>İnternet bağlantısı kesilmiş, ancak uygulamayı kullanmaya devam edebilirsiniz!</p>
        
        <div class="features">
            <h3>Offline'da Neler Yapabilirsiniz?</h3>
            <div class="feature-item">Malzeme ve araç verilerini görüntüleyebilirsiniz</div>
            <div class="feature-item">Yeni malzeme ve araç ekleyebilirsiniz</div>
            <div class="feature-item">Mevcut verileri düzenleyebilirsiniz</div>
            <div class="feature-item">Veriler otomatik olarak kaydedilir</div>
            <div class="feature-item">Online olunca veriler otomatik senkronize edilir</div>
        </div>
        
        <div class="action-buttons">
            <button class="btn-custom btn-primary-custom" onclick="goToApp()">Uygulamaya Dön</button>
            <button class="btn-custom btn-secondary-custom" onclick="location.reload()">Sayfayı Yenile</button>
        </div>
        
        <div class="sync-status" id="syncStatus"></div>
        
        <div id="connection-status">
            Durum: <strong id="statusText">Bağlantı aranıyor...</strong>
        </div>
    </div>

    <script>
        // Bağlantı durumunu göster
        function updateConnectionStatus() {
            const statusText = document.getElementById('statusText');
            if (navigator.onLine) {
                statusText.textContent = '🟢 İnternet bağlantısı var! Sayfayı yenile.';
                showSyncStatus('Veriler senkronize edilmeye hazır!', 'success');
            } else {
                statusText.textContent = '🔴 İnternet bağlantısı yok';
            }
        }
        
        function goToApp() {
            window.location.href = '/stok/dashboard.php';
        }
        
        function showSyncStatus(message, type) {
            const syncStatus = document.getElementById('syncStatus');
            syncStatus.textContent = message;
            syncStatus.className = `sync-status show ${type}`;
        }
        
        // Başlangıçta ve değiştiğinde kontrol et
        updateConnectionStatus();
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // 5 saniyede bir kontrol et
        setInterval(updateConnectionStatus, 5000);
    </script>
</body>
</html>