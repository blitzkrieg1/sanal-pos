<?php

require 'vendor/autoload.php';
require 'src/POSInterface.php';
require 'src/POSSonucInterface.php';
require 'src/YapiKrediPOSSonuc.php';
require 'src/YapiKrediPOS.php';
require 'src/EstPOSSonuc.php';
require 'src/EstPOS.php';
require 'src/TaksitHesap.php';

// EstPos nesnesini oluştur
$est = new est('finansbank', '600027219', 'yuceladmin', 'KUTU7219', false);
$pos = new \EstPos($est);
$pos->krediKartiAyarlari('4543600290478695', '1215', '000');
$pos->siparisAyarlari(00.01, 'SIPARISID' . time(), 0);
$sonuc = $pos->odeme();

var_dump($sonuc);
var_dump($sonuc->basariliMi());
var_dump($sonuc->hataMesajlari());