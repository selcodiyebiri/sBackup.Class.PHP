Hakkında
=================

mySQL yedeğinizi almaya ve Yandex.Disk hesabınıza upload etmeye yarayan gelişmiş özellikli bir sınıftır. Bugün kendim için yazdığım sınıfımı sizlerle de paylaşmak istedim. Geliştirilebilir bir sınıftır.

Bilgilendirme
=================

Sınıf mySQL Veritabanına ulaşmak için mySQLi kullanmaktadır. Bu yüzden php sürümü PHP 5.x ve üstü olmalıdır.

Kullanımı
=================

```php
<?php
include "sBackup.class.php";

# Veritabanına bağlanıyoruz.
$backup = new sBackup('localhost', 'user', 'password', 'dbname');

# Yandex.Disk e upload edilmeyecek ise FALSE olmalıdır. Default olarak FALSE değeri vardır.
$backup->yandexUpload = TRUE;

# YandexUpload TRUE ise Yandex.Disk kullanıcı adımızı giriyoruz.
$backup->yandexUser = 'user';

# YandexUpload TRUE ise Yandex.Disk kullanıcı adımızın şifresini giriyoruz.
$backup->yandexPassword = 'password';

# YandexUpload TRUE ise Yandex.Disk içinde özel bir klasore kaydetmek istiyorsak onun adını giriyoruz.
$backup->yandexKlasor = 'backup';

# SQL dosyalarımızın geçiçi veya kalıcı olarak kaydedileceği klasor adıdır. Default olarak tmp/ klasoru adındadır.
$backup->cacheKlasoru = 'backup/';

# Sunucuya kaydedilen .sql dosyasının silinip silinmeyeceğini belirler. Default olarak FALSE değerindedir.
# YandexUpload FALSE ise bu değer siz TRUE belirleseniz bile FALSE olarak işlem yapacaktır.
$backup->cacheFilesDelete = TRUE;

# Değer FALSE olur ise dosyanın yükleneceği veritabanı içinde benzer tablo adları bulunmamalıdır. Default olarak TRUE değerindededir
$backup->dropTable = FALSE;

# Tabloların yedeklendiği fonksiyondur. Boş bırakılır ise tüm tabloları kaydedecektir.
# Eğer tek bir tabloyu kaydetmesini isterseniz dizi halinde tablo isimlerini gönderebilirsiniz. $backup->Backup(array('tablo', 'tablo2')); gb.
$backup->Backup();
?>
```
