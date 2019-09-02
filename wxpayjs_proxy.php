<?php
error_reporting(0);
set_time_limit(30);
date_default_timezone_set('PRC');

function https_request ($url, $post, $timeout = 30)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'content-type: text/xml'
    ));
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        error_log('[' . date('Y-m-d H:i:s') . '] ' . curl_error($curl) . "\r\n", 3, __DIR__ . '/log/proxy_errors.log');
        return false;
    }
    curl_close($curl);
    return $data;
}

echo https_request('http://17ping.myncic.com/index.php?c=wxpayjs&a=notify', file_get_contents('php://input'));

