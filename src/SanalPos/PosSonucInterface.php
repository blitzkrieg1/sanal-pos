<?php namespace SanalPos;

/**
 * Bankadan gelen döngüler
 */
interface PosSonucInterface {
    /**
     * Sonuç başarılı mı değil mi
     *
     * @return bool Sonuç başarılı mı değil mi
     */
    public function basariliMi();

    /**
     * Hata varsa hata mesajlarını döndüren method
     * BasariliMi false döndürürse kullanılacak.
     *
     * @return array Hata mesajları
     */
    public function hataMesajlari();

    /**
     * Bankadan gelen ve hiç değişmeyecek içerik
     *
     * @return string Gelen ham veri
     */
    public function raw();
} 