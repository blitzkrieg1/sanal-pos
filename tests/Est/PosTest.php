<?php namespace SanalPosTest\Est;

/**
 * Est POS testleri 
 */
class PosTest extends \PHPUnit_Framework_TestCase {
    protected $pos;

    public function setUp() 
    {
        // Est mock
        $est = \Mockery::mock('Est');

        $this->pos = new \SanalPos\Est\Pos($est, 'ISYERIID', 'KULLANICI', 'PAROLA', 'test');
    }

     public function tearDown()
    {
        \Mockery::close();
    }

    public function testGecersizKrediKarti() 
    {
        $this->pos->krediKartiAyarlari('GECERSIZKREDIKARTI', '1013', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID', 1);

        $this->assertFalse($this->pos->dogrula());
    }

    public function testGecersizSonKullanmaTarihiFormati() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '10211', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID', 1);

        $this->assertFalse($this->pos->dogrula());
    }

    public function testSonKullanmaTarihiGecersizAy() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1314', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID', 1);

        $this->assertFalse($this->pos->dogrula());
    }

    public function testGecmisSonKullanmaTarihi() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1012', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID', 1);

        $this->assertFalse($this->pos->dogrula());
    }

    public function testGecersizCCV() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '1234');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID', 1);

        $this->assertFalse($this->pos->dogrula());
    }

    public function testSifirHarcama() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '123');
        $this->pos->siparisAyarlari(0.00, 'SIPARISID', 1);

        $this->assertFalse($this->pos->dogrula());
    }

    public function testGecerliSiparisDogrulama() 
    {
        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID', 1);

        $this->assertTrue($this->pos->dogrula());
    }

    /** 
     * @expectedException InvalidArgumentException
     */
    public function testDogrulamadanOdemeDenemesi() 
    {
        $this->pos->odeme();
    }

    public function testAuthOdemeBasarisiz() 
    {
        $ornekBasarisizSonuc = [
                "orderid"     => "qwaszx",
                "transid"     => "10177-TfgE-1-1544",
                "groupid"     => "qwaszx",
                "response"    => "Error",
                "return_code" => 99,
                "error_msg"   => "Bu siparis numarasi ile zaten basarili bir siparis var.",
                "host_msg"    => "",
                "auth_code"   => "",
                "result"      => "",
                "transaction_time" => [
                        "tm_sec"   => 32,
                        "tm_min"   => 31,
                        "tm_hour"  => 19,
                        "tm_mday"  => 26,
                        "tm_mon"   => 5,
                        "tm_year"  => 110,
                        "tm_wday"  => 6,
                        "tm_yday"  => 176,
                        "unparsed" => ""
                    ]
            ];

        // Mocklar
        $est = \Mockery::mock('est');
        $est->shouldReceive('pay')->once()->andReturn($ornekBasarisizSonuc);

        $this->pos = new \SanalPos\Est\Pos($est, 'ISYERIID', 'KULLANICI', 'PAROLA', 'test');

        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID', 1);

        // Döngü türü kontrolü
        $sonuc = $this->pos->odeme();
        $this->assertInstanceOf('SanalPos\Est\Sonuc', $sonuc);
        $this->assertFalse($sonuc->basariliMi());
    }

    public function testAuthOdemeBasarili() 
    {
        $ornekBasariliSonuc = [
                "orderid"     => "qwaszx",
                "transid"     => "10177-TfgE-1-1544",
                "groupid"     => "qwaszx",
                "response"    => "Error",
                "return_code" => 00,
                "error_msg"   => "",
                "host_msg"    => "",
                "auth_code"   => "",
                "result"      => "",
                "transaction_time" => [
                        "tm_sec"   => 32,
                        "tm_min"   => 31,
                        "tm_hour"  => 19,
                        "tm_mday"  => 26,
                        "tm_mon"   => 5,
                        "tm_year"  => 110,
                        "tm_wday"  => 6,
                        "tm_yday"  => 176,
                        "unparsed" => ""
                    ]
            ];

        // Mocklar
        $est = \Mockery::mock('est');
        $est->shouldReceive('pay')->once()->andReturn($ornekBasariliSonuc);

        $this->pos = new \SanalPos\Est\Pos($est, 'ISYERIID', 'KULLANICI', 'PAROLA', 'test');

        $this->pos->krediKartiAyarlari('5431111111111111', '1013', '123');
        $this->pos->siparisAyarlari(10.00, 'SIPARISID', 1);

        // Döngü türü kontrolü
        $sonuc = $this->pos->odeme();
        $this->assertInstanceOf('SanalPos\Est\Sonuc', $sonuc);
        $this->assertTrue($sonuc->basariliMi());
    }

}