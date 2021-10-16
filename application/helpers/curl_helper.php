<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define('CURL_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.123 Safari/537.36');

if ( ! function_exists('download_file')) {
    function download_file($url, $destination_path)
    {
        $fh = fopen($destination_path, 'w+');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_USERAGENT, CURL_AGENT);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_exec($ch);
        $status_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);
        fclose($fh);

        return $status_code;

        if($status_code >= 200 && $status_code < 400) {
            return true;
        } else {
            return false;
        }
    }
}
