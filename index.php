<?php
$dosya = 'kayitlar.json';
$tarih = $_GET['tarih'] ?? date('Y-m-d');
$veriler = [];

if (file_exists($dosya)) {
    $veriler = json_decode(file_get_contents($dosya), true);
}

$bugun_kayitlar = array_filter($veriler, fn($k) => $k['tarih'] === $tarih);
$tarihler = array_unique(array_column($veriler, 'tarih'));
rsort($tarihler);

$eksikCikislar = array_filter($bugun_kayitlar, fn($k) => empty($k['cikis']));

// geçmiş tarih kayıtlarını tutttuğumuz blok
$dunkuTarih = date('Y-m-d', strtotime('-1 day', strtotime($tarih)));
$dunku_kayitlar = array_filter($veriler, fn($k) => $k['tarih'] === $dunkuTarih);

$bugun_sayisi = count($bugun_kayitlar);
$dunku_sayisi = count($dunku_kayitlar);
$fark = $bugun_sayisi - $dunku_sayisi;

if ($fark > 0) {
  $dune_gore = "Bugün düne göre $fark kişi daha fazla geldi";
} elseif ($fark < 0) {
  $dune_gore = "Dün " . abs($fark) . " kişi daha fazla gelmişti";
} else {
  $dune_gore = "Dün ve Bugün Ziyaretci Sayısı Aynı";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ziyaretçi Takip</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <style>
    @media print {
  body * {
    visibility: hidden;
  }

  .sag, .sag * {
    visibility: visible;
  }

  .sol,
  .btns,
  .istatistik-boxes,
  .modal,
  .modal-backdrop,
  #aramaInput,
  form[action="ekle.php"],
  th:nth-child(5),
  td:nth-child(5) {
    display: none !important;
  }

  table {
    margin-top: 0 !important;
  }
}

    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: #f4f6f8;
      min-height: 100vh;
      display: flex;
      transition: background 0.3s, color 0.3s;
    }
    body.dark {
      background: #121212;
      color: #e0e0e0;
    }
    .sol {
      width: 250px;
      background: #2c3e50;
      color: white;
      padding: 20px;
    }
    .sol h3 {
      text-align: center;
      margin-top: 0;
      margin-bottom: 20px;
    }
    .kutu {
      background: #34495e;
      margin-bottom: 10px;
      padding: 10px;
      border-radius: 5px;
      text-align: center;
      cursor: pointer;
      transition: 0.2s;
    }
    .kutu:hover {
      background: #1abc9c;
    }
    .sag {
      flex: 1;
      padding: 30px;
    }
    .sag h2 {
      margin-top: 0;
      margin-bottom: 20px;
    }
    form {
      margin-bottom: 20px;
    }
    input[type="text"] {
      padding: 10px;
      width: 300px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-bottom: 10px;
    }
    .btns { margin-top: 10px; margin-bottom: 20px; }
    table {
      width: 100%;
      background: white;
      border-collapse: collapse;
      margin-top: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    th {
      background-color: #2c3e50;
      color: white;
      padding: 12px;
    }
    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
    }
    tr:hover {
      background-color: #f0f0f0;
    }
    button {
      padding: 10px 15px;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    button:hover {
      background-color: #2980b9;
      transform: scale(1.05);
    }
    .btn-danger { background-color: #e74c3c; }
    .btn-danger:hover { background-color: #c0392b; }
    .btn-warning { background-color: #f39c12; }
    .btn-warning:hover { background-color: #d68910; }
    .btn-secondary { background-color: #7f8c8d; }
    .btn-secondary:hover { background-color: #636e72; }
    .alert-box {
      background-color: #ffeeba;
      border: 1px solid #f0ad4e;
      padding: 10px;
      border-radius: 5px;
      color: #856404;
    }
    .istatistik-boxes {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 10px;
    }
    .istatistik-boxes div {
      padding: 10px;
      border-radius: 15px;
      font-weight: bold;
    }
    .istatistik-boxes .bugun { 
        background-color: #d1ecf1; 
        border: 1px solid #2c3e50;
        padding: 10px;
        border-radius: 5px;
        color: #0c5460; 
    }
    .istatistik-boxes .dun { 
        background-color: #d6d8d9; 
        border: 1px solid #383d41;
        padding: 10px;
        border-radius: 5px;
        color: #383d41; }
    .istatistik-boxes .analiz { 
        background-color:rgb(214, 198, 18); 
        border: 1px solid #856404;
        padding: 10px;
        border-radius: 5px;
        color:rgb(69, 69, 59); 
    }
     
  </style>
</head>
<body>

<div class="sol">
<h3 style="display: flex; justify-content: space-between; align-items: center;">
  Bütün Tarihler
  <i class="fa-solid fa-house fa-xs" style="color: #ffffff; cursor: pointer;" onclick="buguneGit()" title="Bugüne git"></i>
</h3>


  <hr>
  <?php foreach ($tarihler as $t): ?>
  <div class="kutu" onclick="window.location.href='?tarih=<?= $t ?>'"><?= date('d-m-Y', strtotime($t)) ?></div>
<?php endforeach; ?>
</div>


<div class="sag">
  <div class="row">
    <div class="col-lg-9">
    <h2>Ziyaretçi Girişi - <?= date('d-m-Y', strtotime($tarih)) ?></h2>

      <input type="text" id="aramaInput" class="form-control mb-3" placeholder="🔍 İsim ara...">


      <form method="post" action="ekle.php">
        <input type="text" name="isim" required placeholder="İsim Soyisim">
        <button type="submit" class="btn btn-primary">Ekle</button>
      </form>

      <div class="btns">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Yazdır</button>
        <a href="excel.php?tarih=<?= $tarih ?>"><button type="button" class="btn btn-success">📊 Excel İndir</button></a>
        <button onclick="tabloyuPDFYap()" class="btn btn-secondary">📄 PDF İndir</button>
        </div>
    </div>

    <div class="col-lg-3">
      <div class="istatistik-boxes">
        <?php if (count($eksikCikislar) > 0): ?>
          <div class="alert-box">🔔 Henüz çıkış yapmayan <strong><?= count($eksikCikislar) ?></strong> kişi var!</div>
        <?php endif; ?>
        <div class="bugun">🔷 Bugün Gelen Toplam Ziyaretci Sayısı: <?= $bugun_sayisi ?></div>
        <div class="dun">🔶 <?= $dune_gore ?></div>
        <div class="analiz" onclick="window.location.href='grafik.php'">📊 Grafik Analiz</div>
      </div>
    </div>
  </div>


  <div class="table-responsive">
        <table id="kayitTablo" class="table table-bordered">
          <thead class="table-dark">
            <tr>
              <th>Ziyaretci İsim Soyisim</th>
              <th>Giriş Zamanı</th>
              <th>Çıkış Zamanı</th>
              <th>Geçirilen Toplam Süre</th>
              <th>İşlemler</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bugun_kayitlar as $i => $k): ?>
              <tr>
                <td><?= htmlspecialchars($k['isim']) ?></td>
                <td><?= date('d-m-Y H:i:s', strtotime($k['giris'])) ?></td>
                <td><?= isset($k['cikis']) ? date('d-m-Y H:i:s', strtotime($k['cikis'])) : '-' ?></td>
                <td>
                  <?php
                    if (!empty($k['giris']) && !empty($k['cikis'])) {
                      $girisZaman = strtotime($k['giris']);
                      $cikisZaman = strtotime($k['cikis']);
                      $fark = $cikisZaman - $girisZaman;
                      $saat = floor($fark / 3600);
                      $dakika = floor(($fark % 3600) / 60);
                      $saniye = $fark % 60;
                      echo "$saat sa. $dakika dk. $saniye sn.";
                      
                    } else {
                      echo "-";
                    }
                  ?>
                </td>
                <td>
                  <?php if (empty($k['cikis'])): ?>
                    <form method="post" action="cikis.php" style="display:inline;">
                      <input type="hidden" name="index" value="<?= $i ?>">
                      <button type="submit" class="btn btn-primary">ÇIKTI</button>
                    </form>
                  <?php else: ?>✔<?php endif; ?>

                  <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#duzeltModal<?= $i ?>">DÜZELT</button>
                  <form method="post" action="sil.php" style="display:inline;">
                    <input type="hidden" name="index" value="<?= $i ?>">
                    <button type="submit" class="btn btn-danger">SİL</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

</div>



<?php foreach ($bugun_kayitlar as $i => $k): ?>
<div class="modal fade" id="duzeltModal<?= $i ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="duzelt.php">
      <input type="hidden" name="index" value="<?= $i ?>">
      <div class="modal-header">
        <h5 class="modal-title">Kaydı Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>İsim:</label>
        <input type="text" class="form-control mb-2" name="isim" value="<?= htmlspecialchars($k['isim']) ?>" required>
        <label>Giriş Saati:</label>
        <input type="text" class="form-control mb-2" name="giris" value="<?= $k['giris'] ?>" required>
        <label>Çıkış Saati:</label>
        <input type="text" class="form-control" name="cikis" value="<?= $k['cikis'] ?? '' ?>">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Kaydet</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<script>
  document.getElementById("aramaInput").addEventListener("keyup", function () {
    var value = this.value.toLowerCase();
    var rows = document.querySelectorAll("#kayitTablo tbody tr");
    rows.forEach(function (row) {
      var isim = row.cells[0].innerText.toLowerCase();
      row.style.display = isim.includes(value) ? "" : "none";
    });
  });

  function tabloyuPDFYap() {
    var tarih = "<?= date('d-m-Y', strtotime($tarih)) ?>"; 


    var pdfDiv = document.createElement('div');


    var baslik = document.createElement('h3');
    baslik.textContent = 'Ziyaretçi Listesi - ' + tarih;
    baslik.style.textAlign = 'center';
    baslik.style.marginBottom = '20px';
    pdfDiv.appendChild(baslik);

    
    var orijinalTablo = document.getElementById('kayitTablo').cloneNode(true);

    for (let i = 0; i < orijinalTablo.rows.length; i++) {
        if (orijinalTablo.rows[i].cells.length > 4) {
            orijinalTablo.rows[i].deleteCell(4);
        }
    }


    orijinalTablo.style.width = '100%';
    orijinalTablo.style.borderCollapse = 'collapse';
    orijinalTablo.querySelectorAll('th, td').forEach(cell => {
        cell.style.border = '1px solid #000';
        cell.style.padding = '8px';
    });

    pdfDiv.appendChild(orijinalTablo);


    var opt = {
        margin:       0.5,
        filename:     'ziyaretci_listesi_' + tarih + '.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(pdfDiv).save();
}


function buguneGit() {
  const bugunTarih = "<?= date('Y-m-d') ?>";
  window.location.href = '?tarih=' + bugunTarih;
}


</script>
</body>
</html>