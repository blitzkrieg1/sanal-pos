<?php

/**
 * Yapı Kredi için sanal POS
 */
class YapiKrediPOS implements POSInterface
{
    protected $posnet;

    /**
     * Banka ayarları
     */
    protected $hostlar = array(
            'test'       => 'http://setmpos.ykb.com/PosnetWebService/XML',
            'production' => 'https://www.posnet.ykb.com/PosnetWebService/XML'
        );
    protected $host;
    protected $musteriID;
    protected $terminalID;

    /**
     * Kart bilgileri
     */
    protected $kartNo;
    protected $sonKullanmaTarihi;
    protected $cvc;

    /** 
     * Sipariş bilgileri
     */
    protected $miktar;
    protected $siparisID;

    public function __construct(Posnet $posnet, $musteriID, $terminalID, $environment = 'production')
    {
        // Posnet injection
        $this->posnet = $posnet;

        // Banka giriş verileri
        $this->musteriID  = $musteriID;
        $this->terminalID = $terminalID;
        $this->host       = $this->hostlar[$environment];
    }

    public function krediKartiAyarlari($kartNo, $sonKullanmaTarihi, $cvc)
    {
        $this->kartNo            = $kartNo;
        $this->sonKullanmaTarihi = $sonKullanmaTarihi;
        $this->cvc               = $cvc;
    }

    public function siparisAyarlari($miktar, $siparisID)
    {
        $this->miktar    = $miktar;
        $this->siparisID = $siparisID;
    }

    public function dogrula()
    {
        return $this->krediKartiKontrolleri() and $this->siparisKontrolleri();
    }

    public function odeme()
    {
        
    }

    protected function krediKartiKontrolleri()
    {
        return $this->kartNoKontrolleri() and $this->sonKullanmaTarihiKontrolleri() and $this->cvcKontrolleri();
    }

    protected function kartNoKontrolleri()
    {
        return preg_match('/^[0-9]{16}$/', $this->kartNo);
    }

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

    public function cvcKontrolleri()
    {
        return preg_match('/^[0-9]{3}$/', $this->cvc);
    }

    protected function siparisKontrolleri()
    {
        return $this->miktarKontrolleri() and $this->siparisIDKontrolleri();
    }

    protected function miktarKontrolleri()
    {
        return is_numeric($this->miktar) and $this->miktar > 0;
    }

    protected function siparisIDKontrolleri()
    {
        return ! empty($this->siparisID);
    }
}