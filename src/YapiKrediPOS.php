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

    /**
     * Posnet nesnesinin injectionı, sanal pos bilgileri ve environment
     * belirlemek için kullanılıyor.
     *
     * @param Posnet $posnet
     * @param string $musteriID
     * @param string $terminalID
     * @param string $environment
     * @return void
     */
    public function __construct(Posnet $posnet, $musteriID, $terminalID, $environment = 'production')
    {
        // Posnet injection
        $this->posnet = $posnet;

        // Banka giriş verileri
        $this->musteriID  = $musteriID;
        $this->terminalID = $terminalID;
        $this->host       = $this->hostlar[$environment];
    }

    /**
     * Kredi kartı ayarlarını yap
     *
     * @param string $kartNo
     * @param string $sonKullanmaTarihi
     * @param string $cvc
     * @return void
     */
    public function krediKartiAyarlari($kartNo, $sonKullanmaTarihi, $cvc)
    {
        $this->kartNo            = $kartNo;
        $this->sonKullanmaTarihi = $sonKullanmaTarihi;
        $this->cvc               = $cvc;
    }

    /**
     * Sipariş ayarlarını belirle
     *
     * @param decimal $tutar
     * @param string $siparisID
     * @return void
     */
    public function siparisAyarlari($tutar, $siparisID)
    {
        $this->tutar     = $tutar;
        $this->siparisID = $siparisID;
    }

    /**
     * Bağlantı ayarlarını düzenle
     *
     * @param array $yeniAyarlar
     * @return void
     */
    public function baglantiAyarlari($yeniAyarlar)
    {
        $this->baglantiAyarlari = array_merge($this->baglantiAyarlari, $yeniAyarlar);
    }

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
     * Ayarları yapılan ödemeyi gerçekleştir
     *
     * @return YapiKrediPOSSonuc
     */
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

    /**
     * Kontrol methodları
     */
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