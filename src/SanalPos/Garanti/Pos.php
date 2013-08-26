<?php namespace SanalPos\Garanti;

use SanalPos\BasePos;

/**
 * Garanti için 3D'siz sanal POS
 */
class Pos extends BasePos implements \SanalPos\PosInterface  {
    /**
     * POS bilgileri
     */
    private $adres = 'https://sanalposprov.garanti.com.tr/VPServlet';

    /** 
     * Banka bilgileri
     */
    protected $isyeri;
    protected $terminal;
    protected $kullanici;
    protected $parola;

    /**
     * Environment
     */
    protected $test;

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
    protected $taksit;

    /**
     * Est nesnesinin injectionı, sanal pos bilgileri ve environment
     * belirlemek için kullanılıyor.
     *
     * @param Est $est
     * @param string $isyeri
     * @param string $terminal
     * @param string $kullanici
     * @param string $parola
     * @return void
     */
    public function __construct($isyeri, $terminal, $kullanici, $parola, $environment = 'PROD')
    {
        $this->isyeri      = $isyeri;
        $this->terminal    = $terminal;
        $this->kullanici   = $kullanici;
        $this->parola      = $parola;
        $this->environment = $environment;
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
     * @param float $tutar
     * @param string $siparisID
     * @return void
     */
    public function siparisAyarlari($tutar, $siparisID, $taksit)
    {
        $this->tutar     = $tutar;
        $this->siparisID = $siparisID;
        $this->taksit    = $taksit;
    }

    /**
     * Ayarları yapılan ödemeyi gerçekleştir
     *
     * @return PosSonucInterface
     */
    public function odeme()
    {
        // Kontrol yapmadan deneme yapan olabilir
        if ( ! $this->dogrula())
            throw new \InvalidArgumentException;

        // Verileri garantiye uygun hale getir
        $tutar  = $this->tutar * 100;
        $taksit = $this->taksit > 1 ? $this->taksit : '';

        // HASH
        $SecurityData = strtoupper(sha1($this->parola . str_pad($this->terminal, 9, '0', STR_PAD_LEFT)));
        $hash = strtoupper(sha1($this->siparisID . $this->terminal . $this->kartNo . $tutar . $SecurityData));
        
        $xml= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <GVPSRequest>
        <Mode>TEST</Mode>
        <Version>v0.00</Version>
        <Terminal>
            <ProvUserID>PROVAUT</ProvUserID>
            <HashData>{$hash}</HashData>
            <UserID>{$this->kullanici}</UserID>
            <ID>{$this->terminal}</ID>
            <MerchantID>{$this->isyeri}</MerchantID>
        </Terminal>
        <Customer>
            <IPAddress>{$_SERVER['REMOTE_ADDR']}</IPAddress>
            <EmailAddress></EmailAddress>
        </Customer>
        <Card>
            <Number>{$this->kartNo}</Number>
            <ExpireDate>{$this->sonKullanmaTarihi}</ExpireDate>
            <CVV2>{$this->cvc}</CVV2>
        </Card>
        <Order>
            <OrderID>{$this->siparisID}</OrderID>
            <GroupID></GroupID>
        </Order>
        <Transaction>
            <Type>sales</Type>
            <InstallmentCnt>{$taksit}</InstallmentCnt>
            <Amount>{$tutar}</Amount>
            <CurrencyCode>949</CurrencyCode>
            <CardholderPresentCode>0</CardholderPresentCode>        
            <MotoInd>N</MotoInd>
        </Transaction>
        </GVPSRequest>
        ";
        
        $cevap = $this->xmlGonder($xml);

        // Sonuç nesnesini oluştur
        return new Sonuc($cevap);
    }

    /**
     * Verilen XML'i Garanti'ye gönderip cevabı döndüren method
     *
     * @param string $xml Gönderilecek xml
     * @return string $xml
     */
    function xmlGonder($xml)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->adres);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $cevap = curl_exec($ch);
        curl_close($ch);
        
        return $cevap;
    }

}