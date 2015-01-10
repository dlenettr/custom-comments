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
author="MaRZoCHi"     : Sadece o kullanıcıya ait yorumlar
approve="yes"         : Sadece onaylanmış yorumar ( no: onay bekleyen, kullanılmazsa: hepsi )
template="last_comm"  : Yorum gösterimi için şablon dosyası
days="3"			  : Son 3 gün içinde yazılan yorumlar
from="0"              : Başlangıç
limit="10"            : Limit ( limit-from kadar yorum gösterilir )
order="date"          : Sıralama kriteri ( date - Tarih, postid - Makale ID'si, author - Kullanıcı Adı, rand - Karışık )
sort="desc"			  : Sıralama metodu ( asc: Artan, desc: Azalan )
~~~

Tarihçe
-----------------------
* 10.01.2015 (v1.0)

[Mehmet Hanoğlu]:https://github.com/marzochi
[http://dle.net.tr]:http://dle.net.tr
