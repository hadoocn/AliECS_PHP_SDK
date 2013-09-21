AliECS_PHP_SDK
==============

阿里云ECS PHP开发包 ，PHP开发者可以用它来更方便的写ECS在线管理等工具。


测试用法:

    <?php
    require_once "ecs.sdk.class.php";

    $ecs=new ECS( array(
	      'accessKeyID' => '你的Key',
	      'accessKeySec' => '你的密钥',
	      'accessGetway' => 'http://ecs.aliyuncs.com'
    ) ));

    print_r($ecs->describeInstanceAttribute( array(
	      'InstanceId' => 'AY120723082752d3832041'    // 你的云机机编号（注意与主机名可能不一致）
    ) ));

    print_r($ecs->describeInstanceTypes());

    ?>
