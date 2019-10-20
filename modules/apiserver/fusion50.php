<?

$APIMODULE['fusion50name'] = 'fusion50';
$APIMODULE['fusion50visiblename'] = 'Other Fusion 50(Bulk Support)';
$APIMODULE['fusion50notes'] = 'For bulk submit version 3.1 RC4 required ';
function fusion50_activate() {
    GatewayField('fusion50', 'text', 'customname', '', 'Name', '60', '');
    GatewayField('fusion50', 'text', 'apikey', '', 'APi KEY', '60', '');
    GatewayField('fusion50', 'text', 'username', '', 'Username', '30', '');
    GatewayField('fusion50', 'text', 'apiurl', 'http://www.otherfusion.com/', 'APi url', '500', '');
    GatewayField('fusion50', 'yesno', 'bulk', '', 'Bulk Submit', '', '');
    GatewayField('fusion50', 'yesno', 'bulkget', '', 'Bulk Get', '', '');
}
function fusion50_accoutinfo($VAL) {
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON"); // we recommend json format (More information http://php.net/manual/en/book.json.php)
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
    $request = $api->action('accountinfo');

    if ($request['SUCCESS']) {
        $return['Account email'] = $request['SUCCESS'][0]['AccoutInfo']['mail'];
        $return['Credits available'] = $request['SUCCESS'][0]['AccoutInfo']['credit'];
        $return['apiversion'] = $request['apiversion'];
        return $return;
    } elseif ($request['ERROR']) {
        $return['ERROR'] = $request['ERROR'][0]['MESSAGE'];
    } else {
        $return['ERROR'] = 'Could not communicate with the api';
    }
    return $return;
}
function fusion50_services($VAL) {
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON"); // we recommend json format (More information http://php.net/manual/en/book.json.php)
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
    //$api->debug = true;
    $request = $api->action('imeiservicelist');
    if ($request['SUCCESS']) {
        foreach ($request['SUCCESS'][0]['LIST'] as $Group => $Tools) {
            $return['Group'][$Group]['Name'] = $Group;
            $return['Group'][$Group]['GroupType'] = $Tools['GROUPTYPE'];
            $return['Group'][$Group]['ID'] = $Group;
            foreach ($Tools['SERVICES'] as $Tools => $SERVICES) {
                $return['Group'][$Group]['Tool'][$Tools]['ID'] = $SERVICES['SERVICEID'];
                $return['Group'][$Group]['Tool'][$Tools]['ToolType'] = $SERVICES['SERVICETYPE'];
                $return['Group'][$Group]['Tool'][$Tools]['QNT'] = $SERVICES['QNT'];
                $return['Group'][$Group]['Tool'][$Tools]['SERVER'] = $SERVICES['SERVER'];
                $return['Group'][$Group]['Tool'][$Tools]['Name'] = $SERVICES['SERVICENAME'];
                $return['Group'][$Group]['Tool'][$Tools]['Message'] = utf8_decode($SERVICES['INFO']);
                $return['Group'][$Group]['Tool'][$Tools]['Credits'] = $SERVICES['CREDIT'];
                $return['Group'][$Group]['Tool'][$Tools]['Delivery.Unit'] = $SERVICES['TIME'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.Network'] = $SERVICES['Requires.Network'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.Mobile'] = $SERVICES['Requires.Mobile'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.Provider'] = $SERVICES['Requires.Provider'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.PIN'] = $SERVICES['Requires.PIN'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.KBH'] = $SERVICES['Requires.KBH'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.MEP'] = $SERVICES['Requires.MEP'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.PRD'] = $SERVICES['Requires.PRD'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.Type'] = $SERVICES['Requires.Type'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.Reference'] = $SERVICES['Requires.Reference'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.Locks'] = $SERVICES['Requires.Locks'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.SN'] = $SERVICES['Requires.SN'];
                $return['Group'][$Group]['Tool'][$Tools]['Requires.SecRO'] = $SERVICES['Requires.SecRO'];
            }
        }
    } elseif ($request['ERROR']) {
        $return['ERROR'] = $request['ERROR'][0]['MESSAGE'];
    } else {
        $return['ERROR'] = 'Could not communicate with the api';
    }
    //print_r($return);
    return $return;
}
function fusion50_mobiles($VAL) {
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    //$api = new DhruFusion(); $api->apiurl = $VAL['apiurl']; $api->username = $VAL['username']; $api->apikey = $VAL['apikey'];
    //$api->debug = true;
    //$request = $api->action('modellist');
    //print_r($request);
    foreach ($VAL['services'] as $Tool) {
        if ($Tool['requires_mobile'] != 'None') {
            echo "<strong>Getting models for : " . $Tool['name'] . "</strong><br/>";
            $api = new DhruFusion();
            $api->apiurl = $VAL['apiurl'];
            $api->username = $VAL['username'];
            $api->apikey = $VAL['apikey'];
            $request = $api->action('modellist', array('ID' => $Tool['id']));
            $BID = null;
            //print_r($request);
            foreach ($request['SUCCESS'][0]['LIST'] as $gk => $Brand) {
                $BID = $Brand['ID'];
                $return['Brand'][$BID]['ID'] = $BID;
                $return['Brand'][$BID]['Name'] = $Brand['NAME'];
                $MID = null;
                foreach ($Brand['MODELS'] as $gk => $Mobile) {
                    $MID = $Mobile['ID'];
                    $return['Brand'][$BID]['Mobile'][$MID]['ID'] = $MID;
                    $return['Brand'][$BID]['Mobile'][$MID]['Name'] = $Mobile['NAME'];
                }
            }
        }
    }
    //print_r($request);
    return $return;
}
function fusion50_provider($VAL) {
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    foreach ($VAL['services'] as $Tool) {
        if ($Tool['requires_provider'] != 'None') {
            echo "<strong>Getting providers for : " . $Tool['name'] . "</strong><br/>";
            $api = new DhruFusion();
            $api->apiurl = $VAL['apiurl'];
            $api->username = $VAL['username'];
            $api->apikey = $VAL['apikey'];
            //$api->debug = true;
            $request = $api->action('providerlist', array('ID' => $Tool['id']));
            if ($request['SUCCESS']) {
                $BID = null;
                foreach ($request['SUCCESS'][0]['LIST'] as $gk => $Country) {
                    $BID = $Country['ID'];
                    $return['Country'][$BID]['ID'] = $BID;
                    $return['Country'][$BID]['Name'] = $Country['NAME'];
                    $MID = null;
                    foreach ($Country['PROVIDERS'] as $gk => $Network) {
                        $MID = $Network['ID'];
                        $return['Country'][$BID]['Network'][$MID]['ID'] = $MID;
                        $return['Country'][$BID]['Network'][$MID]['Name'] = $Network['NAME'];
                    }
                }
            }
            unset($request);
        }
    }
    return $return;
}
function fusion50_assign_networks($VAL) {
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    //$api->debug = true;
    //$request = $api->action('providerlist');
    foreach ($VAL['services'] as $Tool) {
        if ($Tool['requires_provider'] != 'None') {
            $api = new DhruFusion();
            $api->apiurl = $VAL['apiurl'];
            $api->username = $VAL['username'];
            $api->apikey = $VAL['apikey'];
            $request = $api->action('getimeiservicedetails', array('ID' => $Tool['id']));
            if ($request['SUCCESS']) {
                saveApiAssignnetworks($request['SUCCESS'][0]['LIST']['assigned_provider'], $VAL['apiservers'], true, $Tool['id'], 'in');
            }
            $request = null;
        }
    }
    //return $return;
}
function fusion50_assign_mobiles($VAL) {
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    //$api->debug = true;
    //$request = $api->action('providerlist');
    foreach ($VAL['services'] as $Tool) {
        //print_r($Tool);
        if ($Tool['requires_mobile'] != 'None') {
            echo "<strong>Getting assign models for : " . $Tool['name'] . "</strong><br/>";
            $api = new DhruFusion();
            $api->apiurl = $VAL['apiurl'];
            $api->username = $VAL['username'];
            $api->apikey = $VAL['apikey'];
            $request = $api->action('getimeiservicedetails', array('ID' => $Tool['id']));
            if ($request['SUCCESS']) {
                $__Aassigned = trim($request['SUCCESS'][0]['LIST']['assigned_model']);
                saveApiAssignMobiles($__Aassigned, $VAL['apiservers'], true, $Tool['id'], 'in');
            }
            unset($request, $__Aassigned);
        }
    }
    //return $return;
}
function fusion50_mep($VAL) {
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
    //$api->debug = true;
    $request = $api->action('meplist');
    //print_r($request);
    if ($request['SUCCESS']) {
        if (is_array($request['SUCCESS'][0]['LIST'])) {
            foreach ($request['SUCCESS'][0]['LIST'] as $MEP) {
                $return[$MEP['ID']] = $MEP['NAME'];
            }
        }
    }
    //print_r($return);
    return $return;
}
function fusion50_send($VAL,$bulk=false) {
    global $debug_output;

    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';

    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    define("VERSION", $VAL['version']);
    $api = new DhruFusion();
    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
    //$api->debug = true;

    if($bulk){

        foreach($VAL['orders'] as $k=>$v){
            if (is_array($v['CUSTOMFIELDS'])) {
                foreach ($v['CUSTOMFIELDS'] as $customfield) {
                    $customfields[$customfield['name']] = $customfield['value'];
                }
            }
            $sendArr[$k]['customfield'] = base64_encode(json_encode($customfields));
            $sendArr[$k]['ID'] = $v['API_ID'];
            $sendArr[$k]['IMEI'] = $v['IMEI'];
            $sendArr[$k]['QNT'] = $VAL['QNT'];
            $sendArr[$k]['SERVER'] = $VAL['SERVER'];
            $sendArr[$k]['MODELID'] = $v['API_MODEL_ID'];
            $sendArr[$k]['PROVIDERID'] = $v['API_PROVIDER_ID'];
            $sendArr[$k]['Network'] = $v['API_PROVIDER_ID'];
            $sendArr[$k]['PIN'] = $v['PIN'];
            $sendArr[$k]['KBH'] = $v['KBH'];
            $sendArr[$k]['MEP'] = $v['API_MEP_ID'];
            $sendArr[$k]['PRD'] = $v['PRD'];
            $sendArr[$k]['TYPE'] = $v['TYPE'];
            $sendArr[$k]['LOCKS'] = $v['LOCKS'];
            $sendArr[$k]['REFERENCE'] = $v['REFERENCE'];
            $sendArr[$k]['SN'] = $v['SN'];
            $sendArr[$k]['SecRO'] = $v['SecRO'];
        }

        $request = $api->action('placeimeiorderbulk', $sendArr,true);

    }else{

        if (is_array($VAL['CUSTOMFIELDS'])) {
            foreach ($VAL['CUSTOMFIELDS'] as $customfield) {
                $customfields[$customfield['name']] = $customfield['value'];
            }
        }
        $sendArr['customfield'] = base64_encode(json_encode($customfields));
        $sendArr['ID'] = $VAL['API_ID'];
        $sendArr['IMEI'] = $VAL['IMEI'];
        $sendArr['MODELID'] = $VAL['API_MODEL_ID'];
        $sendArr['PROVIDERID'] = $VAL['API_PROVIDER_ID'];
        $sendArr['Network'] = $VAL['API_PROVIDER_ID'];
        $sendArr['PIN'] = $VAL['PIN'];
        $sendArr['KBH'] = $VAL['KBH'];
        $sendArr['MEP'] = $VAL['API_MEP_ID'];
        $sendArr['PRD'] = $VAL['PRD'];
        $sendArr['TYPE'] = $VAL['TYPE'];
        $sendArr['LOCKS'] = $VAL['LOCKS'];
        $sendArr['REFERENCE'] = $VAL['REFERENCE'];
        $sendArr['SN'] = $VAL['SN'];
        $sendArr['SecRO'] = $VAL['SecRO'];
        $request = $api->action('placeimeiorder', $sendArr);
    }



    if ($debug_output) {
        echo '<h2>Module Send Order Array </h2>';
        print_r($sendArr);
        echo '<hr />';
        print_r($request);
        echo '<hr />';
    }

    $sendArr = null;
    // print_r($request);

    if($bulk){
        unset($request['apiversion']);
        foreach($request as $k=>$v){
            if ($v['SUCCESS']) {
                $return[$k]['SUCCESS'] = true;
                $return[$k]['MESSAGE'] = htmlspecialchars($v['SUCCESS'][0]['MESSAGE']);
                $return[$k]['ID'] = $v['SUCCESS'][0]['REFERENCEID'];
            } elseif ($v['ERROR']) {
                $return[$k]['ERROR'] = $v['ERROR'][0]['MESSAGE'];
                $return[$k]['MESSAGE'] = $v['ERROR'][0]['MESSAGE'] . nl2br($v['ERROR'][0]['FULL_DESCRIPTION']);
            }
        }
    }else{
        if ($request['SUCCESS']) {
            $return['SUCCESS'] = true;
            $return['MESSAGE'] = htmlspecialchars($request['SUCCESS'][0]['MESSAGE']);
            $return['ID'] = $request['SUCCESS'][0]['REFERENCEID'];
        } elseif ($request['ERROR']) {
            $return['ERROR'] = $request['ERROR'][0]['MESSAGE'];
            $return['MESSAGE'] = $request['ERROR'][0]['MESSAGE'] . nl2br($request['ERROR'][0]['FULL_DESCRIPTION']);
        } else {
            //echo 'no responce';
        }
    }
    //print_r($return);
    return $return;
}
function fusion50_get($VAL,$bulk=false) {

    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
    // $api->debug = true;
    if($bulk){

        foreach($VAL['orders'] as $k=>$v){
            $SenArr[$k]['ID']=$v['API_ORDER_ID'];
        }

        $request = $api->action('getimeiorderbulk',
            $SenArr,true);
        unset($request['apiversion']);

        foreach($request as $k=>$request){
            if ($request['SUCCESS']) {
                if ($request['SUCCESS'][0]['STATUS'] == '4') {
                    $return[$k]['SUCCESS'] = true;
                    $return[$k]['MESSAGE'] = htmlspecialchars('Order Found');
                    $return[$k]['CODES'] = $request['SUCCESS'][0]['CODE'];
                }
                if ($request['SUCCESS'][0]['STATUS'] == '3') {
                    $return[$k]['ERROR'] = 'Refunded';
                    $return[$k]['MESSAGE'] = $request['SUCCESS'][0]['CODE'];
                }
            }
        }
    }else{

        $request = $api->action('getimeiorder', array('ID' => $VAL['API_ORDER_ID']));
        //print_r($request);
        if ($request['SUCCESS']) {
            if ($request['SUCCESS'][0]['STATUS'] == '4') {
                $return['SUCCESS'] = true;
                $return['MESSAGE'] = htmlspecialchars('Order Found');
                $return['CODES'] = $request['SUCCESS'][0]['CODE'];
            }
            if ($request['SUCCESS'][0]['STATUS'] == '3') {
                $return['ERROR'] = 'Refunded';
                $return['MESSAGE'] = $request['SUCCESS'][0]['CODE'];
            }
        }

    }
    //print_r($return);
    return $return;
}
function fusion50_sendfile($VAL) {
    global $debug_output;
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
    //$api->debug = true;
    /*
    if (is_array($VAL['CUSTOMFIELDS'])) {
    foreach ($VAL['CUSTOMFIELDS'] as $customfield) {
    if ($customfield['name'] == 'Provider ID') {
    $ProviderID = $customfield['value'];
    }
    }
    }
    */
    $sendArr['ID'] = $VAL['API_ID'];
    $sendArr['FILENAME'] = $VAL['FILENAME'];
    $sendArr['FILEDATA'] = $VAL['FILEDATA'];
    $request = $api->action('placefileorder', $sendArr);
    if ($debug_output) {
        echo '<h2>Module Send File Order Array </h2>';
        print_r($sendArr);
        echo '<hr />';
        print_r($request);
        echo '<hr />';
    }
    $sendArr = null;
    //print_r($request);
    if ($request['SUCCESS']) {
        $return['SUCCESS'] = true;
        $return['MESSAGE'] = htmlspecialchars($request['SUCCESS'][0]['MESSAGE']);
        $return['ID'] = $request['SUCCESS'][0]['REFERENCEID'];
    } else {
        $return['ERROR'] = $request['ERROR'][0]['MESSAGE'];
        $return['MESSAGE'] = $request['ERROR'][0]['MESSAGE'] . nl2br($request['ERROR'][0]['FULL_DESCRIPTION']);
    }
    return $return;
}
function fusion50_getfile($VAL) {
    global $debug_output;
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
    //$api->debug = true;
    $request = $api->action('getfileorder', array('ID' => $VAL['API_ORDER_ID']));
    if ($debug_output) {
        echo '<h2>Module Get File Order Array </h2>';
        print_r($request);
        echo '<hr />';
    }
    if ($request['SUCCESS']) {
        if ($request['SUCCESS'][0]['STATUS'] == '4') {
            $return['SUCCESS'] = true;
            $return['MESSAGE'] = htmlspecialchars('Order Found');
            $return['CODES'] = $request['SUCCESS'][0]['CODE'];
        }
        if ($request['SUCCESS'][0]['STATUS'] == '3') {
            $return['ERROR'] = 'Not Found';
            $return['MESSAGE'] = 'Not Found';
        }
    }
    //print_r($return);
    return $return;
}

?>