<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('download_file')) {
    if (!defined('CURL_AGENT')) {
        $CI = &get_instance();
        define('CURL_AGENT', 'ocap-stats/1.0 (+' . $CI->config->item('base_url') . ')');
    }

    function download_file($url, $destination_path)
    {
        $fh = fopen($destination_path, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_USERAGENT, CURL_AGENT);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_exec($ch);
        $status_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);
        fclose($fh);

        if ($status_code >= 200 && $status_code < 400) {
            return true;
        } else {
            return false;
        }
    }
}
