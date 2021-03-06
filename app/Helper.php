<?php
namespace App;

class Helper
{
    /**
     * Handle Swoole befareStart event
     */
    public function beforeStart()
    {
        // 检查文件夹和随机播放歌单是否存在
        if(!file_exists(BASE_PATH . "/tmp/")) mkdir(BASE_PATH . "/tmp/");
        if(!file_exists(BASE_PATH . "/random.txt")) {
            $data = @file_get_contents("https://cdn.zerodream.net/download/music/random.txt");
            @file_put_contents(BASE_PATH . "/random.txt", $data);
        }
    }

    /**
     * @param $url
     * @return false|string
     */
    public function get_redirect_url($url)
    {
        $redirect_url = null;

        $url_parts = @parse_url($url);
        if (!$url_parts) return false;
        if (!isset($url_parts['host'])) return false; //can't process relative URLs
        if (!isset($url_parts['path'])) $url_parts['path'] = '/';

        $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
        if (!$sock) return false;

        $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ?'?'.$url_parts['query'] : '') . " HTTP/1.1\r\n";
        $request .= 'Host: ' . $url_parts['host'] . "\r\n";
        $request .= "Connection: Close\r\n\r\n";
        fwrite($sock, $request);
        $response = '';
        while(!feof($sock)) $response .= fread($sock, 8192);
        fclose($sock);
        var_dump($response);

        if (preg_match('/^Location: (.+?)$/m', $response, $matches)){
            return trim($matches[1]);
        } else {
            return $url;
        }
    }
}