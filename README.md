AliECS_PHP_SDK
==============

阿里云ECS PHP开发包 ，PHP开发者可以用它来更方便的写ECS在线管理等工具。

每个ECS CLASS中都需要各类参数。调用时请参阅http://developers.oss.aliyuncs.com/API/ECS-API-Reference-Full.pdf的说明。

案例：hexPanel( https://i.hexdata.cn/ https://github.com/hexdata/hexportal )


示例用法:

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

授权:

    The MIT License (MIT)

    Copyright (c) 2013 enj0y ( dev.enj0y@e.hexdata.cn )

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
