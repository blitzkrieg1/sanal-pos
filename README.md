PHP Sanal Pos
===

Birden fazla bankanın sistemini tek arayüzde toplayan pos kütüphanesi. Sadece EST ve Posnet destekleniyor şimdilik. Yani `Akbank`, `Finansbank`, `Halkbank`, `Türkiye İş Bankası`, `Anadolubank` ve `Yapı Kredi` bankaları.

POS'u mevcut bir müşterim için yazdığımdan, sadece auth methodu destekleniyor şimdilik. Zamanım oldukça tamamlamak isterim ancak ne kadar mümkün olur bilmiyorum.

Destek için issue açabilir, `ebuyukkaya@gmail.com` adresinden veya [@ekrembk](http://twitter.com/ekrembk) twitter hesabından bana ulaşabilirsiniz.

Kurulum
---

Composer ile sisteminize rahatlıkla ekleyebilirsiniz. Örnek composer.json

    {
        "require": {
            "ekrembk/sanal-pos": "dev-master"
        }
    }