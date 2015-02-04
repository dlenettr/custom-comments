Custom Comments for DLE ( DataLife Engine )
===

* Yapımcı: [Mehmet Hanoğlu]
* Site   : [http://dle.net.tr]
* Lisans : MIT License
* DLE    : 10.3+

Custom Comments modülü ile sitenizdeki yorumları istediğiniz gibi yerleştirebilirsiniz. Bilinen custom tagı mantığıyla aynı şekilde hazırlandı.
Böylece kullanımı daha kolay anlaşılabilir. Şablon dosyası ve kod ile ilgili tüm bilgiler aşağıda mevcuttur.
Artık siteniz için son yorumlar modülü aramanıza gerek kalmayacak. Bir çok DLE sürümü ile uyumlu olarak çalışabilecek. Fakat ilk etapta 10.3 sürümü baz alınarak tasarlanmıştır.
Diğer sürümlerde meydana gelen uyumsuzlukları çözmek için bize geri bildirim gönderiniz.


Şablonda kullanılabilir taglar :
===
Makale Bilgileri :
---
~~~
{news-title limit="50"} - Makalenin başlığı ( Tam uzunluk: {news-title} )
{news-cat}              - Makale kategorisinin linki
{news-link}             - Makele URL'si
{news-id}               - Makale ID'si
~~~

Kullanıcı Bilgileri :
---
~~~
{author}            - Kullanıcı adı
{author-colored}    - Kullanıcı adı ( grup rengi ile )
{author-id}         - Kullanıcı ID'si
{author-url}        - Profil sayfa URL'si
{author-foto}       - Avatar URL'si
{author-news}       - Makale sayısı
{author-comm}       - Yorum Sayısı
{author-group}      - Grubu ( Renklendirme destekli )
{author-group-icon} - Grup ikonu
~~~

Yorum Bilgileri :
---
~~~
{approve}     - Onay durumu ( Onaylı:1, Onay bekliyor:0 )
{is_register} - Kayıtlı kullanıcı ise:1, değilse: 0
{email}       - Email adresi
{ip}          - IP adresi
{id}          - Yorum ID'si
{date}        - Tarih ( {date=Format} destekleniyor )
{text}        - Yorum metni HTML olarak
{text-prev}   - Yorum metni yazı olarak ilk 100 karakter
{comm-link}   - Yorum URL'si
~~~
Kontrol tagları :
---
~~~
[registered] Kayıtlı kullanıcı yorumu ise gözükür [/registered]
~~~

Comment kodu ve parametreleri
===
Comment kodu 
---
{comments ... }

Parametreler ve açıklamaları :
---
~~~
users="yes"           : Sadece kayıtlı kullanıcıların yaptığı yorumlar ( no: Ziyaretçilerin yaptığı yorumlar, kullanılmazsa: hepsi )
cache="yes"           : Önbellekleme kullan ( Varsayılan: no )
id="1-100,5"          : Yorum ID'leri 1-100 arasında ve 5 olanlar ( Tek yorum için de girilebilir id="10" )
news="1,2,3,4-10"     : Makale ID'leri 1-10 arasında olanlar yorumlar ( Tek makale için de girilebilir: news="205" )
category="1-20"       : Sadece kategori ID'leri 1-20 arasında olan makalelere yapılan yorumları listeler ( news, id parametreleri gibi kullanılabilir )
not-category="1,2,3"  : Sadece kategori ID'leri 1,2,3 dışında olan makalelere yapılan yorumları listeler ( news, id parametreleri gibi kullanılabilir )
author="MaRZoCHi"     : Sadece o kullanıcıya ait yorumlar
author="_THIS_"       : Kullanıcı profil sayfasında, sadece o kullanıcıya ait yorumlar
author="_CURRENT_"    : Giriş yapmış olan kullanıcıya ait yorumlar
approve="yes"         : Sadece onaylanmış yorumar ( no: onay bekleyen, kullanılmazsa: hepsi )
template="last_comm"  : Yorum gösterimi için şablon dosyası
days="3"			  : Son 3 gün içinde yazılan yorumlar
from="0"              : Başlangıç
limit="10"            : Limit ( limit-from kadar yorum gösterilir )
order="date"          : Sıralama kriteri ( date - Tarih, postid - Makale ID'si, author - Kullanıcı Adı, rand - Karışık )
sort="desc"			  : Sıralama metodu ( asc: Artan, desc: Azalan )
~~~

Örnek kodlar :
~~~
{comments users="yes" news="205" cache="no" approve="yes" template="last_comm" from="0" limit="10" order="date" sort="desc"}
{comments category="1-20" author="_THIS_" cache="no" approve="yes" template="last_comm" from="0" limit="10" order="date" sort="desc"}
{comments not-category="5" author="_CURRENT_" cache="yes" approve="yes" template="last_comm" from="0" limit="5" order="postid" sort="asc"}
~~~

Kurulum
---
1) Aç: index.php ( DLE 10.3 ve öncesi için )
   Aç: engine/modules/main.php ( DLE 10.4 ve sonrası için)

Her yerde çalışması için ( Kullanıcı profil sayfasında )
---
Bul :
~~~
echo $tpl->result['main'];
~~~

Üstüne Ekle :
~~~
// Custom comments - start
if ( stripos( $tpl->result['main'], "{comments" ) !== false ) {
	include ENGINE_DIR . "/modules/custom.comments.php";
	$tpl->result['main'] = preg_replace_callback ( "#\\{comments(.+?)\\}#i", "custom_comments", $tpl->result['main'] );
}
// Custom comments - end
~~~

Sadece main.tpl ve ilk include edilen .tpl dosyalarında çalışması için
---
Bul :
~~~
$config['http_home_url'] = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
~~~

Üstüne Ekle :
~~~
// Custom comments - start
if ( stripos( $tpl->copy_template, "{comments" ) !== false ) {
	include ENGINE_DIR . "/modules/custom.comments.php";
	$tpl->copy_template = preg_replace_callback ( "#\\{comments(.+?)\\}#i", "custom_comments", $tpl->copy_template );
}
// Custom comments - end
~~~

2) Temanızdaki bir CSS dosyasına ekleyin ( style.css veya engine.css )
~~~
.last-comment { margin: 0; padding: 3px 1px; list-style: none; border-bottom: 1px solid #CBDFE8; transition: .4s; }
.last-comment:hover { background: #f3f3f3; transition: .4s; }
.last-comment .foto { float: left; width: 85px; text-align: center; }
.last-comment .foto img { width: 50px; border-radius: 25px; border: 2px solid #ccc; }
.last-comment .foto span { font-size: 11px; }
.last-comment .info { float: right; width: 168px; margin-right: 2px; }
.last-comment .info a { color: #0261AE; }
.last-comment .info .comm { height: 50px; overflow: hidden; }
.last-comment .info .comm:after { content: "..."; }
.last-comment .info i { color: #666; float: right; margin-right: 5px; }
~~~

Değişiklikler
-----------------------
 Version 1.1
 ---
  + İlave makale bilgilerini çekme özelliği eklendi
     {news-read}   : Makalenin okunma sayısı
     {news-rating} : Makalenin değerlendirmesi
  * Makale başlıklarından slash hatası giderildi
  + Hataya sebep olan bazı parametreler için öntanımlı değerler girildi
  + Kullanıcı profili sayfasında o kullanıcıya ait yorumların gösterilmesi için _THIS_ değişkeni eklendi.
  + Mevcut kullanıcıya ait yorumların gösterilmesi için _CURRENT_ değişkeni eklendi.
  + category ve not-category parametreleri eklendi.
  + Yorum gösterimi için kullanıcı grubuna ait izin kontrolü eklendi.

Tarihçe
-----------------------
* 04.02.2015 (v1.1)
* 10.01.2015 (v1.0)

[Mehmet Hanoğlu]:https://github.com/marzochi
[http://dle.net.tr]:http://dle.net.tr
