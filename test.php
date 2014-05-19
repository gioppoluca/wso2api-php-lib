<?php
include('wso2api.class.php');

echo '<pre>';

echo 'Alcune informazioni di debug:';

print "</pre>";


$wso2api = new Wso2API("http://10.118.8.67:9763",$user = 'admin', $password = 'admin', $debug = true);


switch ($_GET["test"]){
case "create":

/////////// create API test
$create_api_post = array('name'=>'WikipediaAPI',
						 'visibility'=>'public',
						 'version'=>'1.0.0',
						 'description'=>'If you want to monitor a MediaWiki installation',
						 'endpointType'=>'nonsecured',
						 'http'=>true,
						 'https'=>false,
						 'endpoint'=>'http://en.wikipedia.org/w/api.php',
						 'wsdl'=>'',
						 'tags'=>'wikipedia,mediawiki',
						 'tier'=>'Silver',
						 'thumbUrl'=>'https://upload.wikimedia.org/wikipedia/en/b/bc/Wiki.png',
						 'bizOwner'=>'Luca Gioppo',
						 'provider'=>'admin',
						 'context'=>'/wikipedia',
						 'tiersCollection'=>'Gold',
						);
					
$create_api_resources = array (array('resourceMethod'=>'GET',
									 'resourceMethodAuthType'=>'Application',
							         'resourceMethodThrottlingTier'=>'Unlimited',
							         'uriTemplate'=>'/*'));
$ret = $wso2api->create_api($create_api_post, $create_api_resources, $autopublish = true);

echo print_r($wso2api->error_message,true);
break;
case "listApi":
/////// List API
$ret = $wso2api->get_all_api_list();
//echo print_r($wso2api->error_message,true);
echo '<pre>';

echo 'result of call:';
if ($ret){
echo 'Response: ' . print_r($wso2api->response, TRUE);
}else{
echo 'Error message' . $wso2api->error_message;
echo 'Error message code' . $wso2api->error_code;
}
print "</pre>";

break;
case "getSwagger":
/////// List API
$apiname = 'citydynamics';
$apiversion = '1.0.0';
$ret = $wso2api->get_api_swagger($apiname, $apiversion);
echo 'result of call:';
if ($ret){
echo 'Response: ' . print_r($wso2api->response, TRUE);
}else{
echo 'Error message' . $wso2api->error_message;
echo 'Error message code' . $wso2api->error_code;
}
print "</pre>";
break;

}

?>