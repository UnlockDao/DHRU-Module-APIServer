<?php
$APIMODULE['dhruopenaggregatorname'] = 'dhruopenaggregator';
$APIMODULE['dhruopenaggregatorvisiblename'] = 'Dhru Open Api Aggregator';
$APIMODULE['dhruopenaggregatornotes'] = 'Dhru Open Api Aggregator';
function dhruopenaggregator_activate() {
    GatewayField('dhruopenaggregator', 'text', 'server', '', 'Server Url', '100', '');
    GatewayField('dhruopenaggregator', 'text', 'apikey', '', 'APi KEY', '100', '');
}

function dhruopenaggregator_services($VAL) {
    $return['Group'][0]['ID'] = "dhruopenaggregator";
    $return['Group'][0]['Name'] = "dhruopenaggregator services";
    $return['Group'][0]['Tool'][0]['ID'] = "1";
    $return['Group'][0]['Tool'][0]['Name'] = "dhruopenaggregator services 1";
    $return['Group'][0]['Tool'][0]['Message'] = "";
    return $return;
}

function dhruopenaggregator_send($VAL) {
    $responce = initCurl($VAL['server'].'?apikey='.$VAL['apikey'].'&imei='.$VAL['IMEI']);
    $responce = json_decode($responce,TRUE);
    if ($responce['status'] == 'success') {
        $return['SUCCESS'] = true;
        $return['MESSAGE'] = 'IMEI SENT TO dhruopenaggregator';
        $return['ID'] = $VAL['IMEI'];
        $return['CODES'] = $responce['code'];
    }else{
        $return['ERROR'] = 'No Code Found';
        $return['MESSAGE'] = 'No Code Found';
    }
    return $return;
}