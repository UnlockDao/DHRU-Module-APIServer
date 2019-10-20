<?php

$APIMODULE['samtoolname'] = 'samtool';
$APIMODULE['samtoolvisiblename'] = 'samtool.org';
$APIMODULE['samtoolnotes'] = '';
function samtool_activate()
{
    GatewayField('samtool', 'text', 'apikey', '', 'APi KEY', '60', '');
    GatewayField('samtool', 'text', 'username', '', 'Username', '30', '');
    GatewayField('samtool', 'system', 'apiurl', 'https://samtool.org/', 'APi url', '500', '');
}

function samtool_accoutinfo($VAL)
{
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON"); // we recommend json format (More information http://php.net/manual/en/book.json.php)
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->removeindex = true;
    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
    $request = $api->action('accountinfo');

    if ($request[gsmhubsite]) {
        $_scripttype = 'gsmhub';
    } else if($request[apiversion]=='2.0.0') {
        $_scripttype = 'gsmtool';
    }else{
        $_scripttype = '';
    }
    if ($request['SUCCESS']) {
        $return['Account email'] = $request['SUCCESS'][0]['AccoutInfo']['mail'];
        $return['Credits available'] = $request['SUCCESS'][0]['AccoutInfo']['credit'];
        $return['currency'] = $request['SUCCESS'][0]['AccoutInfo']['currency'];

        $return['scripttype'] = $_scripttype;
        if ($request['SUCCESS'][0]['AccoutInfo']['currency']) {
            $return['currency'] = $request['SUCCESS'][0]['AccoutInfo']['currency'];
        }
        return $return;
    } elseif ($request['ERROR']) {
        $return['ERROR'] = $request['ERROR'][0]['MESSAGE'];
    } else {
        $return['ERROR'] = 'Could not communicate with the api';
    }
    return $return;
}

function samtool_services($VAL)
{
//    print_r($VAL);

    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON"); // we recommend json format (More information http://php.net/manual/en/book.json.php)
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->removeindex = true;
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
                $return['Group'][$Group]['Tool'][$Tools]['Requires.Custom'] = $SERVICES['Requires.Custom'];

            }
        }
    } elseif ($request['ERROR']) {
        $return['ERROR'] = $request['ERROR'][0]['MESSAGE'];
    } else {
        $return['ERROR'] = 'Could not communicate with the api';
    }

    if ($VAL['scripttype'] == 'gsmhub') {
        $request2 = $api->action('serverservicelist');
        $requestTypeList = $api->action('serverservicetypelist');
//        printJson($requestTypeList);
//        printJson($request2);
        if ($request2['SUCCESS']) {
            unset($return['ERROR']);
            foreach ($request2['SUCCESS'][0]['LIST'] as $Group => $Tools) {
//                printJson($Tools);
                if ($Group == 'GsmHub Products') continue;
                $return['Group'][$Group]['Name'] = $Group;
                $return['Group'][$Group]['GroupType'] = 'SERVER';
                $return['Group'][$Group]['ID'] = $Group;
                foreach ($Tools['SERVICES'] as $Tools => $SERVICES) {

                    /* cusotm fields */
                    $CustomFields = '';
                    $_CustomFields = $SERVICES['REQUIRED'] ? explode("|", $SERVICES['REQUIRED']) : "";
                    if (is_array($_CustomFields)) {
//                        printJson($_CustomFields);
                        $__CustomFields = $CustomFields = [];
                        foreach ($_CustomFields as $fildname) {
                            if ($fildname == 'USERTYPE') {
                                $__CustomFields[fieldtype] = 'dropdown';
                                $__CustomFields[fieldoptions] = 'NewUser,ExistingUser';
                            } else {
                                $__CustomFields[fieldtype] = 'text';
                                $__CustomFields[fieldoptions] = '';
                            }

                            $__CustomFields[type] = 'serviceimei';
                            $__CustomFields[fieldname] = $fildname;

                            $__CustomFields[description] = '';

                            $__CustomFields[regexpr] = '';
                            $__CustomFields[adminonly] = '';
                            $__CustomFields[required] = 'on';
                            $__CustomFields[enc] = '';
                            $CustomFields[] = $__CustomFields;
                        }
//                        printJson($CustomFields);
                    }
                    /* cusotm fields */

                    $_tool_id = $SERVICES['SERVICEID'];
                    $_this_tool_has_types = false;
                    $this_tool_types = [];
                    foreach ($requestTypeList['SUCCESS'][0]['LIST'] as $v) {
                        if ($v['tool_id'] == $_tool_id) {
                            $_this_tool_has_types = true;
                            $_this_tool_types[Name] = $v[name];
                            $_this_tool_types[Credits] = $v[price];
                            $_this_tool_types[TypeID] = $v[id];
                            $this_tool_types[] = $_this_tool_types;
                        }
                    }
                    if ($_this_tool_has_types) {
                        // make this tool as group .
//                        unset($return['Group'][$Group]);
                        $Group = $SERVICES['SERVICENAME'];
                        $return['Group'][$Group]['Name'] = $SERVICES['SERVICENAME'];
                        $return['Group'][$Group]['GroupType'] = 'SERVER';
                        $return['Group'][$Group]['ID'] = $Group;

                        // for each types as tools
                        foreach ($this_tool_types as $Tools => $v) {
                            $ADDITIONAL_NVP = unserialize($SERVICES[ADDITIONAL_NVP]);
                            $return['Group'][$Group]['Tool'][$Tools]['ID'] = $SERVICES['SERVICEID'];
                            $return['Group'][$Group]['Tool'][$Tools]['TypeID'] = $v['TypeID'];
                            $return['Group'][$Group]['Tool'][$Tools]['ToolType'] = 'SERVER';
                            $return['Group'][$Group]['Tool'][$Tools]['QNT'] = $ADDITIONAL_NVP[hide_quantity] ? 0 : 1;
                            $return['Group'][$Group]['Tool'][$Tools]['SERVER'] = 1;
                            $return['Group'][$Group]['Tool'][$Tools]['Name'] = $v['Name'];
                            $return['Group'][$Group]['Tool'][$Tools]['Message'] = html_entity_decode(utf8_decode($SERVICES['INFO']));
                            $return['Group'][$Group]['Tool'][$Tools]['Credits'] = $v[Credits];
                            $return['Group'][$Group]['Tool'][$Tools]['Delivery.Unit'] = $SERVICES['TIME'];
                            $return['Group'][$Group]['Tool'][$Tools]['Requires.Custom'] = $CustomFields;
                            $return['Group'][$Group]['Tool'][$Tools]['ADDITIONAL_NVP'] = $ADDITIONAL_NVP;
                        }
                        continue;
                    }

                    $ADDITIONAL_NVP = unserialize($SERVICES[ADDITIONAL_NVP]);
                    $return['Group'][$Group]['Tool'][$Tools]['ID'] = $SERVICES['SERVICEID'];
                    $return['Group'][$Group]['Tool'][$Tools]['ToolType'] = 'SERVER';
                    $return['Group'][$Group]['Tool'][$Tools]['QNT'] = $ADDITIONAL_NVP[hide_quantity] ? 0 : 1;
                    $return['Group'][$Group]['Tool'][$Tools]['SERVER'] = 1;
                    $return['Group'][$Group]['Tool'][$Tools]['Name'] = $SERVICES['SERVICENAME'];
                    $return['Group'][$Group]['Tool'][$Tools]['Message'] = html_entity_decode(utf8_decode($SERVICES['INFO']));
                    $return['Group'][$Group]['Tool'][$Tools]['Credits'] = $SERVICES[CREDIT];
                    $return['Group'][$Group]['Tool'][$Tools]['Delivery.Unit'] = $SERVICES['TIME'];
                    $return['Group'][$Group]['Tool'][$Tools]['Requires.Custom'] = $CustomFields;
                    $return['Group'][$Group]['Tool'][$Tools]['ADDITIONAL_NVP'] = $ADDITIONAL_NVP;
                }

            }
        }
//        printJson($return);
    }
    elseif ($VAL['scripttype'] == 'gsmtool'){
        include_once ROOTDIR . '/modules/apiserver/class/gsmtool.class.php';
        $api = new gsmtool();
        $request2 = $api->action("serverservices", $VAL['apikey'], $VAL['apiurl'], $VAL['username'], array());
        if($request2[Packages]){
            foreach ($request2[Packages][Package] as $k => $v){
                $Group = $v[Category];
                if(!trim($Group)){
                    //   break;
                }
//                echo $Group;
                $return['Group'][$Group]['Name'] = $Group;
                $return['Group'][$Group]['GroupType'] = 'SERVER';
                $return['Group'][$Group]['ID'] = $Group;
                $Tools = $v[PackageId];

                $return['Group'][$Group]['Tool'][$Tools]['ID'] = $Tools;
                $return['Group'][$Group]['Tool'][$Tools]['ToolType'] = 'SERVER';
//                $return['Group'][$Group]['Tool'][$Tools]['QNT'] = $ADDITIONAL_NVP[hide_quantity] ? 0 : 1;
                $return['Group'][$Group]['Tool'][$Tools]['SERVER'] = 1;
                $return['Group'][$Group]['Tool'][$Tools]['Name'] = $v[PackageTitle];
                $return['Group'][$Group]['Tool'][$Tools]['Message'] = html_entity_decode(utf8_decode($v[MustRead]));
                $return['Group'][$Group]['Tool'][$Tools]['Credits'] = $v[PackagePrice];
                $return['Group'][$Group]['Tool'][$Tools]['Delivery.Unit'] = $v[TimeTaken];
//                $return['Group'][$Group]['Tool'][$Tools]['Requires.Custom'] = $CustomFields;
//                $return['Group'][$Group]['Tool'][$Tools]['ADDITIONAL_NVP'] = $ADDITIONAL_NVP;
//                printJson($return);

            }
        }

        printJson($request2[Packages]);

//        print_r($request2);
//        exit();


    }
    printJson($return);
    return $return;
}

function samtool_send($VAL)
{
    global $debug_output;
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    define("VERSION", $VAL['version']);
    $api = new DhruFusion();
    $api->removeindex = true;

    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
//    $api->debug = true;
    if (is_array($VAL['CUSTOMFIELDS'])) {
        foreach ($VAL['CUSTOMFIELDS'] as $customfield) {
            $customfields[$customfield['name']] = $customfield['value'];
        }
    }
    $sendArr['customfield'] = base64_encode(json_encode($customfields));
    $sendArr['ID'] = $VAL['API_ID'];
    $sendArr['IMEI'] = $VAL['IMEI'];
    $sendArr['QNT'] = $VAL['QNT'];
    $sendArr['SERVER'] = $VAL['SERVER'];
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

//    printJson($VAL);

    if ($VAL[scripttype] == 'gsmhub' && $VAL[SERVER]) {

        if($VAL[API_TYPEID]){
            $customfields[service_type_id] = $VAL[API_TYPEID];
        }
        if($customfields){
            $sendArr[REQUIRED] = json_encode($customfields);
        }
        $sendArr[QUANTITY] = $VAL['QNT'];

//        printJson($sendArr);
        $request = $api->action('placeserverorder', $sendArr);
//       print_r($request);
//        printJson($request);

    }else if ($VAL[scripttype] == 'gsmtool' && $VAL[SERVER]){
        $request = $api->action('placeserverorder', $sendArr);
    } else {
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
    //print_r($request);
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
    //print_r($return);
    return $return;
}

function samtool_get($VAL)
{
//    printJson($VAL);
    include_once ROOTDIR . '/modules/apiserver/class/fusion.class.php';
    define("REQUESTFORMAT", "JSON");
    define('DHRUFUSION_URL', $VAL['apiurl']);
    define("USERNAME", $VAL['username']);
    define("API_ACCESS_KEY", $VAL['apikey']);
    $api = new DhruFusion();
    $api->removeindex = true;

    $api->apiurl = $VAL['apiurl'];
    $api->username = $VAL['username'];
    $api->apikey = $VAL['apikey'];
//    $api->debug = true;
    if($VAL[scripttype]=='gsmhub' && $VAL[SERVER]){
        $request = $api->action('getserverorder', array('ID' => $VAL['API_ORDER_ID']));
//        printJson($request);
    }else if ($VAL[scripttype] == 'gsmtool' && $VAL[SERVER]){

        include_once ROOTDIR . '/modules/apiserver/class/gsmtool.class.php';
        $api = new gsmtool();
        $request = $api->action("getserverorder", $VAL['apikey'], $VAL['apiurl'], $VAL['username'], array('orderId' => $VAL['API_ORDER_ID']));
        if($request[result][serverorder][0]){
            if($request[result][serverorder][0][statusId]==2){

                $request['SUCCESS'][0]['STATUS'] = 4;
                $request['SUCCESS'][0]['CODE'] = $request[result][serverorder][0][code];

            }
            if($request[result][serverorder][0][statusId]==3){

                $request['SUCCESS'][0]['STATUS'] = 3;
                $request['SUCCESS'][0]['CODE'] = $request[result][serverorder][0][code];
            }

        }
//        print_r($request);


    }else {

        $request = $api->action('getimeiorder', array('ID' => $VAL['API_ORDER_ID']));
    }

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
//    print_r($return);
//    printJson($return);

    return $return;
}


?>