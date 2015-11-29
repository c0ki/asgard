<?php

namespace Core\LayoutBundle\Component\Helper;

class UrlHelper
{

    public function getContentUrl($url)
    {
        $result = array(
            'header' => array(),
            'content' => null,
            'error' => null);
        $url = 'https://www.google.fr/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

        $content = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if (!empty($error)) {
            $result['error'] = $error;
            return $result;
        }
        elseif ($info['http_code'] != 200) {
            $result['error'] = "Error: HTTP Code: {$info['http_code']}";
            return $result;
        }

        if (array_key_exists('content_type', $info)) {
            $result['header'][] = 'Content-Type: ' . $info['content_type'];
        }

        $infoUrl = parse_url($url);
        $baseUrl = $infoUrl['scheme'] . "://";
        $baseUrl .= $infoUrl['host'];
        if (array_key_exists('port', $infoUrl)) {
            $baseUrl .= $infoUrl['port'];
        }
        $baseUrl .= $infoUrl['path'];
        $content = str_replace('"/', '"' . $baseUrl, $content);
        $content = str_replace(':url(/', ':url(' . $baseUrl, $content);

        $result['content'] = $content;

        return $result;
    }

}