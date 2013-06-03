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
    protected $tutar;
    protected $siparisID;

    /**
     * Bağlantı ayarları
     */
    public $baglantiAyarlari = array(
            'timeOut' => 30
        );

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

    public function siparisAyarlari($tutar, $siparisID)
    {
        $this->tutar     = $tutar;
        $this->siparisID = $siparisID;
    }

    public function baglantiAyarlari($yeniAyarlar)
    {
        $this->baglantiAyarlari = array_merge($this->baglantiAyarlari, $yeniAyarlar);
    }

    public function dogrula()
    {
        return $this->krediKartiKontrolleri() and $this->siparisKontrolleri();
    }

    public function odeme()
    {
        // Kontrol yapmadan deneme yapan olabilir
        if ( ! $this->dogrula())
            throw new \InvalidArgumentException;

        // Bankaya post edilecek veriler
        $islemTuru = 'auth';
        $taksit    = '00';
        $kur       = 'YT';

        // İşlem tutarını düzenle
        $tutar = number_format($this->tutar, 2, '', '');

        $this->posnet->UseOpenssl();
        $this->posnet->SetURL($this->host);
        $this->posnet->SetMid($this->musteriID);
        $this->posnet->SetTid($this->terminalID);
        $this->posnet->DoAuthTran(
            $this->kartNo,
            $this->sonKullanmaTarihi,
            $this->cvc,
            $this->siparisID,
            $tutar,
            $kur,
            $taksit
        );

        // Sonuç nesnesini oluştur
        return new \YapiKrediPOSSonuc($this->posnet);
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
        return $this->tutarKontrolleri() and $this->siparisIDKontrolleri();
    }

    protected function tutarKontrolleri()
    {
        return is_numeric($this->tutar) and $this->tutar > 0;
    }

    protected function siparisIDKontrolleri()
    {
        return ! empty($this->siparisID);
    }

}