AliECS_PHP_SDK
==============

阿里云ECS PHP开发包 ，PHP开发者可以用它来更方便的写ECS在线管理等工具。


测试用法:

    <?php
    ini_set('display_errors',true);
    error_reporting(E_ALL);

    require_once "ecs.sdk.class.php";

    $ecs=new ECS( array(
        'accessKeyID' => '你的accessKeyId',
        'accessKeySec' => '你的accessKeyScret',
        'accessGetway' => 'http://ecs.aliyuncs.com'
    ) );

    //查询实例属性
    print_r($ecs->describeInstanceAttribute( array(
        'InstanceId' => '云主机ID，与默认的主机名可能不同'
    ) ));

    //查询可用instanceType
    print_r($ecs->describeInstanceTypes());
    ?>
