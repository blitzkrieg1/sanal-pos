<?php

/**
 * Sanal POS Interface
 *
 * Bundan sonra yazacağım POS'lara kalıp olması açısından bu projede dahil ediyorum. 
 */
interface POSInterface
{
    /**
     * Kredi kartı ayarları
     *
     * @param integer $kartNo
     * @param integer $sonKullanmaTarihi (MMYY)
     * @param integer $cvc
     * @return void
     */
    public function krediKartiAyarlari($kartNo, $sonKullanmaTarihi, $cvc);

    /**
     * Sipariş ayarları
     *
     * @param decimal $miktar
     * @param string $siparisID
     * @return void
     */
    public function siparisAyarlari($miktar, $siparisID);

    /** 
     * Girilen kredi kartı gibi verilerin bankaya göndermeden önce doğrulaması
     * 
     * @return bool
     */
    public function dogrula();

    /*
     * Doğrulamadan sonra kullanılacak methodlar
     */

    /*
     * Verileri bankaya gönderecek
     *
     * @return POSSonuc
     */
    public function odeme();

    /**
     * Sonradan eklenebilecek özellikler
     *
     * public function preAuth();
     * public function iade();
     * public function iptal(); 
     * public function siparisDetaylari();
     */
}