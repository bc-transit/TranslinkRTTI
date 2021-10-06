<?php namespace translinkrtti\lib;

/**
 * Generic cURL requests class
 * 
 * This creates get and post requests on behalf of PHP.
 * 
 * @author Martyr2
 * @copyright 2021 The Coders Lexicon
 * @link https://www.coderslexicon.com
 */

 class CurlRequests 
 {
    /**
     * Executes a GET request on URL with specified headers
     *
     * @param string $url - URL to post to
     * @param array $headers - Optional headers to add to request
     * @param boolean $sslVerify - Optional flag to turn off SSL verification (keep on in production)
     * @return stdClass|false Returns stdClass with status code and content or false if failure
     */
    public static function get(string $url, array $headers = [], bool $sslVerify = true) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);

        $h = [];

        foreach ($headers as $headerName => $headerValue) {
            $h[] = "$headerName: $headerValue";
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $h);

        $content = curl_exec($ch);
        if ($content === false) return false;

        $sc = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $std = new \stdClass();
        $std->status_code = $sc;
        $std->content = $content;

        return $std;
    }

    /**
     * Executes a POST request on URL with body data and with any specified headers
     *
     * @param string $url - URL to post on
     * @param string|array $data - Encoded string of data or array of parameters to post as the body
     * @param array $headers - Optional headers to add to request
     * @param boolean $sslVerify - Optional flag to turn off SSL verification (keep on in production)
     * @return stdClass|false Returns stdClass with status code and content or false if failure
     */
    public static function post(string $url, $data, array $headers = [], bool $sslVerify = true) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);                                                                   

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  

        $headers['Content-Length'] = strlen($data);
        $h = [];

        foreach ($headers as $headerName => $headerValue) {
            $h[] = "$headerName: $headerValue";
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $h);

        $content = curl_exec($ch);
        if ($content === false) return false;

        $sc = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $std = new \stdClass();
        $std->status_code = $sc;
        $std->content = $content;

        return $std;
    }
}
