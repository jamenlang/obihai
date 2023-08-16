<?php

/*
this was intended to be ran on a set schedule with cron calling the script with parameters:

parameter #1 being the service provider (sp1 or sp2)
 and 
parameter #2 being to set DND to true or false to enable or disable DND.

/home/user# php dnd_example.php 1 true
*/

if(!isset($argv[1])){
        exit;
}


$obi_ip = '192.168.88.213';

$obi_usr = 'admin';
$obi_pwd = 'admin';

$service_provider_id = $argv[1];

$dnd_bool = $argv[2];


/*SP1 - (personal)*/
//these appear to be static identifiers for config options.
if($service_provider_id == 1){
        $dnd_enabled = '93116d71';

        $cfwd_unconditional_enabled = '13beda58';
        $cfwd_unconditional_number = '2942873a';

        $cfwd_onbusy_enabled = '5ed6ae41';
        $cfwd_onbusy_number = '745a5b23';

        $cfwd_onnoanswer_enabled = 'c42c130b';
        $cfwd_onnoanswer_number = 'd9afbfed';
        $cfwd_onnoanswer_rings = 'ada6403d';
}

/*SP2 - (work)*/
//these appear to be static identifiers for config options.
if($service_provider_id == 2){
        $dnd_enabled = '98633dd2';

        $cfwd_unconditional_enabled = 'e54b4439';
        $cfwd_unconditional_number = 'facef11b';

        $cfwd_onbusy_enabled = '720ee342';
        $cfwd_onbusy_number = '87929024';

        $cfwd_onnoanswer_enabled = '7288e08c';
        $cfwd_onnoanswer_number = '880c8d6e';
        $cfwd_onnoanswer_rings = '7f32aa1e';
}

/*OBITALK*/

$obdnd_enabled = '7367205e';

//multiple paramters are defined as ParameterList: ab2b58c5=true&c0af05a7=30769651757&7367205e=true


echo PHP_EOL;
echo PHP_EOL;
echo 'static start: ' . date('d/m/y G:i:s');
echo PHP_EOL;


$ch = curl_init();
// set url
curl_setopt($ch, CURLOPT_URL, "http://$obi_ip");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
curl_setopt($ch, CURLOPT_USERPWD, "$obi_usr:$obi_pwd");

// the get the real output
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_HTTPGET, 1);

//now that we're logged in.

curl_setopt_array($ch, array(
        CURLOPT_URL => "http://$obi_ip/result.html",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "ParameterList=" . $dnd_enabled . "%3D" . (($dnd_bool) ? 'true' : 'false'),
));

$response = curl_exec($ch);
$err = curl_error($ch);

//curl_close($ch);
if ($err) {
        echo "cURL Error #:" . $err;
} else {
        // echo $response;
        echo 'check dnd, should now be "' . (($dnd_bool) ? 'true' : 'false') . '"' . PHP_EOL;
}

curl_setopt_array($ch, array(
        CURLOPT_URL => "http://$obi_ip/callstatus.htm",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
));

$response = curl_exec($ch);
$err = curl_error($ch);


if(strstr($response,'Number of Active Calls: 0')){

        curl_setopt_array($ch, array(
                CURLOPT_URL => "http://$obi_ip/rebootgetconfig.htm",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
                echo "cURL Error #:" . $err;
        } else {
                // echo $response;
                echo 'no calls in progress, reboot requested.' . PHP_EOL;
        }
}else{
        file_put_contents('/tmp/callinprogress',1);
        echo 'calls are in progress. reboot skipped.' . PHP_EOL;
}

echo 'static end: ' . date('d/m/y G:i:s');
echo PHP_EOL;
