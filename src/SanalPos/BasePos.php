<?php namespace SanalPos;

class BasePos {
    /**
     * Kontrol methodları
     */
    /**
     * Tüm kontrolleri yap
     *
     * @return bool
     */
    public function dogrula()
    {
        return $this->krediKartiKontrolleri() and $this->siparisKontrolleri();
    }
    
    /**
     * Kredi kartı için tüm kontrolleri yapan method
     *
     * @return bool
     */
    protected function krediKartiKontrolleri()
    {
        return $this->kartNoKontrolleri() and $this->sonKullanmaTarihiKontrolleri() and $this->cvcKontrolleri();
    }

    /**
     * Kredi kart numarasının geçerliliğini kontrol eden method
     *
     * @return bool
     */
    protected function kartNoKontrolleri()
    {
        return preg_match('/^[0-9]{16}$/', $this->kartNo);
    }

    /**
     * Kredi kart son kullanma tarihinin geçerliliğini kontrol eden method
     *
     * @return bool
     */
    protected function sonKullanmaTarihiKontrolleri()
    {
        // 4 karakter ve numerik
        if ( ! preg_match('/^[0-9]{4}$/', $this->sonKullanmaTarihi))
            return false;

        // Ay kontrolü
        // 1-12 arası olmak zorunda
        $ay = (int) substr($this->sonKullanmaTarihi, 0, 2);
        if ($ay > 12 or $ay <= 0)
            return false;

        // Yıl kontrolü
        // Geçmiş yıl olamaz
        $yil = (int) substr($this->sonKullanmaTarihi, 2, 2);
        if ($yil < date('y'))
            return false;

        return true;
    }

    /**
     * Kredi kartı güvenlik kodunun geçerliliğini kontrol eden method
     *
     * @return bool
     */
    public function cvcKontrolleri()
    {
        return preg_match('/^[0-9]{3}$/', $this->cvc);
    }

    /**
     * Sipriş için girilen tüm verileri kontrol eden method
     *
     * @return bool
     */
    protected function siparisKontrolleri()
    {
        return $this->tutarKontrolleri() and $this->siparisIDKontrolleri();
    }

    /**
     * Girilen sipariş tutarını kontrol eden method
     *
     * @return bool
     */
    protected function tutarKontrolleri()
    {
        return is_numeric($this->tutar) and $this->tutar > 0;
    }

    /**
     * Girilen sipariş ID'sini kontrol eden method
     *
     * @return bool
     */
    protected function siparisIDKontrolleri()
    {
        return ! empty($this->siparisID);
    }
}