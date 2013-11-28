wso2api-php-lib
===============

PHP library to call WSO2 API Manager to create and publish API.
This wraps the cURL calls available from API manager with simple calls.



Here is a sample of usage

```php
$wso2api = new Wso2API("http://10.118.8.73:9763");


$create_api_post = array('name'=>'WikipediaAPI',
						 'visibility'=>'public',
						 'version'=>'1.0.0',
						 'description'=>'If you want to monitor a MediaWiki installation',
						 'endpointType'=>'nonsecured',
						 'http'=>true,
						 'https'=>false,
						 'endpoint'=>'http://en.wikipedia.org/w/api.php',
						 'wsdl'=>'',
						 'wadl'=>'',
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
```