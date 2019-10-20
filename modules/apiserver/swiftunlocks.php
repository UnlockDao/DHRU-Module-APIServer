<?

$APIMODULE['swiftunlocksname'] = 'swiftunlocks';
$APIMODULE['swiftunlocksvisiblename'] = 'Swiftunlocks v2';
$APIMODULE['swiftunlocksnotes'] = 'swiftunlocks api v2';
function swiftunlocks_activate() {
    GatewayField('swiftunlocks', 'text', 'apikey', '', 'APi KEY', '500', '');
    GatewayField('swiftunlocks', 'text', 'apiurl', 'http://swiftunlocks.com/api/', 'APi url', '500', '');
}
function swiftunlocks_services($VAL) {
    $send['key'] = $VAL['apikey'];
    $request = initCurl($VAL['apiurl'] . '/get-tools', $send);
    $request = json_decode($request, true);
    if (!$request['error']) {
        $Group = 1;
        $return['Group'][$Group]['Name'] = 'Swiftunlocks';
        $return['Group'][$Group]['ID'] = $Group;
        foreach ($request['out']['tools'] as $Tools => $SERVICES) {
            $return['Group'][$Group]['Tool'][$Tools]['ID'] = $SERVICES['id'];
            $return['Group'][$Group]['Tool'][$Tools]['Name'] = $SERVICES['name'];
            $return['Group'][$Group]['Tool'][$Tools]['Message'] = utf8_decode($SERVICES['description']);
            $return['Group'][$Group]['Tool'][$Tools]['Credits'] = $SERVICES['credits'];
            $return['Group'][$Group]['Tool'][$Tools]['Delivery.Unit'] = $SERVICES['delivery'];
            $return['Group'][$Group]['Tool'][$Tools]['Requires.Network'] = $SERVICES['network'] != '0' ? 'Required' : 'None';
            $return['Group'][$Group]['Tool'][$Tools]['Requires.Mobile'] = $SERVICES['brand_id'] != '0' ? 'Required' : 'None';
            $return['Group'][$Group]['Tool'][$Tools]['Requires.Provider'] = $SERVICES['network'] != '0' ? 'Required' : 'None';
            $return['Group'][$Group]['Tool'][$Tools]['Requires.PIN'] = $SERVICES['pin'] != '0' ? 'Required' : 'None';
            $return['Group'][$Group]['Tool'][$Tools]['Requires.KBH'] = $SERVICES['kbh'] != '0' ? 'Required' : 'None';
            $return['Group'][$Group]['Tool'][$Tools]['Requires.MEP'] = $SERVICES['mep'] != '0' ? 'Required' : 'None';
            $return['Group'][$Group]['Tool'][$Tools]['Requires.PRD'] = $SERVICES['prd'] != '0' ? 'Required' : 'None';
        }
    } elseif ($request['errno']) {
        $return['ERROR'] = $request['error'];
    } else {
        $return['ERROR'] = 'Could not communicate with the api';
    }
    return $return;
}
function swiftunlocks_mobiles($VAL) {
    foreach ($VAL['services'] as $Tool) {
        if ($Tool['requires_mobile'] != 'None') {
            echo "<strong>Getting models for : " . $Tool['name'] . "</strong><br/>";
            $send['key'] = $VAL['apikey'];
            $send['id'] = $Tool['id'];
            $request = initCurl($VAL['apiurl'] . '/get-tools', $send);
            $request = json_decode($request, true);
            $BID = null;
            foreach ($request['out']['tools'] as $gk => $Brand) {
                if ($Brand['custom'][0]['options']) {
                    $BID = 1;
                    $return['Brand'][$BID]['ID'] = $BID;
                    $return['Brand'][$BID]['Name'] = 'Brand';
                    $MID = null;
                    foreach ($Brand['custom'][0]['options'] as $gk => $Mobile) {
                        $MID = $Mobile[0];
                        $return['Brand'][$BID]['Mobile'][$MID]['ID'] = $MID;
                        $return['Brand'][$BID]['Mobile'][$MID]['Name'] = $Mobile[1];
                    }
                }
            }
        }
    }
    return $return;
}
function swiftunlocks_mep($VAL) {
    $send['key'] = $VAL['apikey'];
    $request = initCurl($VAL['apiurl'] . '/get-mep', $send);
    $request = json_decode($request, true);
    //print_r($request);
    if ($request['errno'] == '0') {
        foreach ($request['out']['meps'] as $key => $MEP) {
            $return[$key] = $MEP['code'];
        }
    } else {
        $return['ERROR'] = nl2br($request['error']);
    }
    return $return;
}
function swiftunlocks_send($VAL) {
    $send['key'] = $VAL['apikey'];
    $send['id'] = $VAL['API_ID'];
    $send['imei'] = $VAL['IMEI'];
    $send['brand'] = $VAL['API_MODEL_ID'];
    $send['prd'] = $VAL['PRD'];
    $request = initCurl($VAL['apiurl'] . '/place-order', $send);
    $request = json_decode($request, true);
    //print_r($request);
    //exit();
    if ($request['out']['id']) {
        $return['SUCCESS'] = true;
        $return['MESSAGE'] = htmlspecialchars($request['out']['msg']);
        $return['ID'] = $request['out']['id'];
    } elseif ($request['errno']) {
        $return['ERROR'] = $request['error'];
        $return['MESSAGE'] = $request['error'];
    }
    return $return;
}
function swiftunlocks_get($VAL) {
    echo $VAL['IMEI'];
    $send['key'] = $VAL['apikey'];
    $send['id'] = $VAL['API_ORDER_ID'];
    $request = initCurl($VAL['apiurl'] . '/get-order', $send);
    $request = json_decode($request, true);
    //print_r($request);
    if ($request['out']['order']['status'] != 'Processing') {
        if ($request['out']['order']['code']) {
            $return['SUCCESS'] = true;
            $return['MESSAGE'] = $request['out']['order']['status'];
            $return['CODES'] = $request['out']['order']['code'];
        } else {
            $return['ERROR'] = $request['out']['order']['reason'];
            $return['MESSAGE'] = $request['out']['order']['reason'];
        }
    }
    return $return;
}

?>