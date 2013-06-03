<?php

/**
 * Sanal POS Interface
 *
 * Bundan sonra yazacağım POS'lara kalıp olması açısından bu projede dahil ediyorum. 
 */
interface POSInterface
{
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