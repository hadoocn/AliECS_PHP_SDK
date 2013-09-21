<?php
//ini_set('display_errors',true);
//error_reporting(E_ALL);

require_once "ecs.sdk.class.20120913.php";

$ecs=new ECS( array(
	'accessKeyID' => '你的Key',
	'accessKeySec' => '你的密钥',
	'accessGetway' => 'http://ecs.aliyuncs.com'
) );
print_r($ecs->describeInstanceAttribute( array(
	'InstanceId' => 'AY120723082752d3832041'    // 你的云机机编号（注意与主机名可能不一致）
) ));
print_r($ecs->describeInstanceTypes());
?>
