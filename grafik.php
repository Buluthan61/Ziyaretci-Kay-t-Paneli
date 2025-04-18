<?php
$dosya = 'kayitlar.json';
$veriler = file_exists($dosya) ? json_decode(file_get_contents($dosya), true) : [];
$tarihler = array_unique(array_column($veriler, 'tarih'));
rsort($tarihler);

$bugun = date('Y-m-d');
$dun = date('Y-m-d', strtotime('-1 day'));


$gunlukSayilar = [];
$gunlukOrtalamaSure = [];
$gunlukDetaylar = [];

foreach ($tarihler as $tarih) {
    $kayitlar = array_filter($veriler, fn($v) => $v['tarih'] === $tarih);
    $gunlukSayilar[$tarih] = count($kayitlar);

    $toplamSure = 0;
    $tamKayit = 0;
    foreach ($kayitlar as $k) {
        if (!empty($k['giris']) && !empty($k['cikis'])) {
            $sure = strtotime($k['cikis']) - strtotime($k['giris']);
            $toplamSure += $sure;
            $tamKayit++;
            $gunlukDetaylar[$tarih][] = ['isim' => $k['isim'], 'sure' => $sure];
        }
    }

    $gunlukOrtalamaSure[$tarih] = $tamKayit ? round($toplamSure / $tamKayit) : 0;
}

$son7gun = array_slice(array_reverse($tarihler), -7);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Grafik Analiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
   body {
  margin: 0;
  font-family: 'Segoe UI', Tahoma, sans-serif;
  display: flex;
  background: #f4f6f8;
  font-size: 14px;
}
.sol {
  width: 220px;
  background: #2c3e50;
  color: white;
  padding: 15px;
}
.sol h3 {
  text-align: center;
  font-size: 16px;
}
.kutu {
  background: #34495e;
  padding: 8px;
  margin-bottom: 8px;
  border-radius: 5px;
  cursor: pointer;
  text-align: center;
  font-size: 14px;
}
.kutu:hover {
  background: #1abc9c;
}
.sag {
  flex: 1;
  padding: 30px;
}
h2 {
  font-size: 18px;
  margin-bottom: 20px;
}
.grafik-kart {
  background: white;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
  margin-bottom: 25px;
}
.grafik-kart h5 {
  font-weight: bold;
  font-size: 16px;
  margin-bottom: 10px;
}
.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px;
}
canvas {
  height: 240px !important;
  max-width: 100%;
}

  </style>
</head>
<body>

<div class="sol">
  <h3>Tarihler</h3>
  <hr>
  <?php foreach ($tarihler as $t): ?>
    <div class="kutu" onclick="window.location.href='index.php?tarih=<?= $t ?>'">
      <?= date('d-m-Y', strtotime($t)) ?>
    </div>
  <?php endforeach; ?>
</div>

<div class="sag">
  <h2>📊 Grafiksel Ziyaretçi Analizi</h2>

  
  <div class="grafik-kart">
    <h5>Bugünkü Ziyaretçi Süreleri</h5>
    <canvas id="gunlukSureler"></canvas>
  </div>

  <div class="grid-2">
    <div class="grafik-kart">
      <h5>Dün ve Bugün Karşılaştırması</h5>
      <canvas id="dunBugunFark"></canvas>
    </div>
    <div class="grafik-kart">
      <h5>Son 7 Günlük Ziyaretçi Sayısı</h5>
      <canvas id="son7gunZiyaret"></canvas>
    </div>
  </div>

  <div class="grid-2 mt-4">
    <div class="grafik-kart">
      <h5>Ortalama Kalma Süresi (Son 7 Gün)</h5>
      <canvas id="ortalamaSure7Gun"></canvas>
    </div>
    <div class="grafik-kart">
      <h5>Günlük Ziyaretçi Sayıları</h5>
      <canvas id="gunlukSayilar7Gun"></canvas>
    </div>
  </div>
</div>


<script>
  const detaylar = <?= json_encode($gunlukDetaylar[$bugun] ?? []) ?>;
  const isimler = detaylar.map(e => e.isim);
  const sureler = detaylar.map(e => Math.round(e.sure / 60)); 

  new Chart(document.getElementById('gunlukSureler'), {
    type: 'bar',
    data: {
      labels: isimler,
      datasets: [{
        label: 'Ziyaret Süresi (dk)',
        data: sureler,
        backgroundColor: '#2980b9'
      }]
    }
  });

  new Chart(document.getElementById('dunBugunFark'), {
    type: 'bar',
    data: {
      labels: ['Dün', 'Bugün'],
      datasets: [{
        label: 'Ziyaretçi Sayısı',
        data: [<?= $gunlukSayilar[$dun] ?? 0 ?>, <?= $gunlukSayilar[$bugun] ?? 0 ?>],
        backgroundColor: ['#e67e22', '#3498db']
      }]
    }
  });

  new Chart(document.getElementById('son7gunZiyaret'), {
    type: 'line',
    data: {
      labels: <?= json_encode(array_map(fn($t) => date('d-m', strtotime($t)), $son7gun)) ?>,
      datasets: [{
        label: 'Ziyaretçi Sayısı',
        data: <?= json_encode(array_map(fn($t) => $gunlukSayilar[$t] ?? 0, $son7gun)) ?>,
        borderColor: '#27ae60',
        fill: true
      }]
    }
  });

  new Chart(document.getElementById('ortalamaSure7Gun'), {
    type: 'line',
    data: {
      labels: <?= json_encode(array_map(fn($t) => date('d-m', strtotime($t)), $son7gun)) ?>,
      datasets: [{
        label: 'Ortalama Süre (dk)',
        data: <?= json_encode(array_map(fn($t) => round(($gunlukOrtalamaSure[$t] ?? 0) / 60), $son7gun)) ?>,
        borderColor: '#8e44ad',
        fill: false
      }]
    }
  });

  new Chart(document.getElementById('gunlukSayilar7Gun'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_map(fn($t) => date('d-m', strtotime($t)), $son7gun)) ?>,
      datasets: [{
        label: 'Ziyaretçi Sayısı',
        data: <?= json_encode(array_map(fn($t) => $gunlukSayilar[$t] ?? 0, $son7gun)) ?>,
        backgroundColor: '#16a085'
      }]
    }
  });
</script>

</body>
</html>
