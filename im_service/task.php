<?php
// 自动执行程序
error_reporting(0);
set_time_limit(3600);
date_default_timezone_set('Asia/Chongqing');

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://17m.myncic.com/?c=task&a=orderalert');
curl_setopt($curl, CURLOPT_TIMEOUT, 3600);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
curl_exec($curl);
if (curl_errno($curl)) {
    error_log('[' . date('Y-m-d H:i:s') . '] ' . curl_error($curl) . "\r\n", 3, __DIR__ . DIRECTORY_SEPARATOR . 'curl_errors.log');
}
curl_close($curl);