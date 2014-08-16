<?php

/*
 *  Class sPagination
 *  @author Selçuk Çelik
 *  @blog http://selcuk.in
 *  @mail selcuk@msn.com
 *  @date 16.08.2014
 */

class sBackup
{
    /*
     * Yedeklenecek veritabanı dosyasının yandexe upload edilmesi ayarı.
     *
     * @return boolean
     */
    public $yandexUpload = FALSE;

    /*
     * Yedeklenecek veritabanın kaydedileceği bulut ortam yandex in webdav kullanıcı adı.
     *
     * @return string
     */
    public $yandexUser;

    /*
     * Yedeklenecek veritabanın kaydedileceği bulut ortam yandex in webdav şifresi.
     *
     * @return string
     */
    public $yandexPassword;

    /*
     * Yedeklenecek veritabanı dosyasının yandex.disk de kaydedileceği klasor adı.
     *
     * @return string
     */
    public $yandexKlasor = NULL;

    /*
     * Yedeklenecek veritabanın boş bir veri tabanı için olup olmadığı ayardır.
     *
     * @return boolean
     */
    public $dropTable = TRUE;

    /*
     * Yedeklenecek dosyanın geçiçi olarak kaydedileceği yerdir.
     *
     * @return string
     */
    public $cacheKlasoru = 'tmp/';

    /*
     * Cache dosyasının silinip silinmeyeceği ayardır.
     *
     * @return boolean
     */
    public $cacheFilesDelete = FALSE;

    private $dosyaAdi;
    private $db;
    private $mysqlHost;
    private $mysqlUser;
    private $mysqlPassword;
    private $mysqlDBName;

    /*
     * Yedeklenecek veritabanına bağlantı fonksiyonumuz.
     *
     * @param $mysqlHost
     * @param $mysqlUser
     * @param $mysqlPassword
     * @param $mysqlDBName
     */
    public function __construct($mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDBName)
    {
        ($this->yandexUpload == FALSE) ? $this->cacheFilesDelete = FALSE : NULL;

        $this->mysqlHost = $mysqlHost;
        $this->mysqlUser = $mysqlUser;
        $this->mysqlPassword = $mysqlPassword;
        $this->mysqlDBName = $mysqlDBName;

        try{
            $this->db = mysqli_connect($mysqlHost, $mysqlUser, $mysqlPassword);
            mysqli_select_db($this->db, $mysqlDBName);
            mysqli_query($this->db, "SET NAMES 'utf8'");
        }
        catch(Exception $e){
            echo $e->getMessage();
            echo "mySQL sunucusuna bağlanmada sıkıntı var.";
        }
    }

    /*
     * Yedeklenecek veritabanı fonksiyonumuz.
     *
     * @param $tables
     * @return boolean
     */
    public function Backup($tables = '*')
    {
        if($tables == '*')
        {
            $tables = array();
            $result = mysqli_query($this->db, 'SHOW TABLES');
            while($row = mysqli_fetch_row($result))
            {
                $tables[] = $row[0];
            }
        }
        else
        {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        $return = '';

        foreach($tables as $table)
        {
            $result = mysqli_query($this->db, 'SELECT * FROM '.$table);
            $num_fields = mysqli_num_fields($result);

            ($this->dropTable == TRUE) ? $return.= 'DROP TABLE '.$table.';' : NULL;
            $row2 = mysqli_fetch_row(mysqli_query($this->db, 'SHOW CREATE TABLE '.$table));
            ($this->dropTable == TRUE) ? $return.= "\n\n".$row2[1].";\n\n" : $return.= $row2[1].";\n\n";

            for ($i = 0; $i < $num_fields; $i++)
            {
                while($row = mysqli_fetch_row($result))
                {
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                    for($j=0; $j<$num_fields; $j++)
                    {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n","\\n",$row[$j]);
                        if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                        if ($j<($num_fields-1)) { $return.= ','; }
                    }
                    $return.= ");\n";
                }
            }
            $return.="\n\n\n";
        }

        $this->dosyaAdi = 'DB-BACKUP-'.time().'-'.uniqid().'.sql';
        $this->fileSave($this->dosyaAdi, $return);
        ($this->yandexUpload == TRUE) ? $this->yandexUpload($this->yandexKlasor) : NULL;
    }

    /*
     * Yedeklenecek veritabanı fonksiyonumuz.
     *
     * @param $klasorAdi
     * @return boolean
     */
    private function yandexUpload($klasorAdi)
    {
        $yandexWebDavUrl = $klasorAdi ? "https://webdav.yandex.com.tr/$klasorAdi/" : "https://webdav.yandex.com.tr/";
        $fileHandler = fopen($this->cacheKlasoru.$this->dosyaAdi, 'r');

        $ch = curl_init($yandexWebDavUrl . $this->dosyaAdi);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->yandexUser:$this->yandexPassword");
        curl_setopt($ch, CURLOPT_PUT, TRUE);
        curl_setopt($ch, CURLOPT_INFILE, $fileHandler);
        curl_exec($ch);

        fclose($fileHandler);

        ($this->cacheFilesDelete == TRUE) ? unlink($this->cacheKlasoru.$this->dosyaAdi) : NULL;


    }

    /*
     * Yedeklenecek veritabanı fonksiyonumuz.
     *
     * @param $dosyaAdi
     * @param $Veri
     * @return boolean
     */
    private function fileSave($dosyaAdi, $Veri)
    {
        $handle = fopen($this->cacheKlasoru.$dosyaAdi, 'w+');
        fwrite($handle, $Veri);
        fclose($handle);
    }

    function __destruct()
    {
        mysqli_close($this->db);
    }

}
?>
