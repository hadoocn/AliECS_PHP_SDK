<?php

/*
  The php sdk class for alibaba cloud ecs api.
  Author: enj0y
  Email: hackes@outlook.com
  Project page: https://github.com/thislancorp/AliECS_PHP_SDK/
  Latest ECS Api reference: http://developers.oss.aliyuncs.com/API/ECS-API-Reference-Full.pdf
 */
Class ECS{
    protected static $accessKeyID=null, $accessKeySec=null, $accessGetway="http://ecs.aliyuncs.com", $data=null, $exception=null, $version='2013-01-10', $debug=false;
    
    protected static function exception( $errCode ) {
        return self::$exception[ $errCode ];
    }
    
    protected static function jsonParse( $contents ) {
        return json_decode($contents, true);
    }

    protected static function percentEncode($str){
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
    
    protected static function sign($parameters, $accessKeySecret){
        // 将参数Key按字典顺序排序
        ksort($parameters);

        // 生成规范化请求字符串
        $canonicalizedQueryString = '';
        foreach($parameters as $key => $value){
            $canonicalizedQueryString .= '&' . self::percentEncode($key). '=' . self::percentEncode($value);
        }

        // 生成用于计算签名的字符串 stringToSign
        $stringToSign = 'GET&%2F&' .self::percentencode(substr($canonicalizedQueryString, 1));

        // 计算签名，注意accessKeySecret后面要加上字符'&'
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    protected static function curl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    protected static function nonce(){
        return uniqid();
    }
    
    protected static function gmdateTZ(){
        return 'Y-m-d\TH:i:s\Z';
    }
    
    protected static function httpParams($data){
        return http_build_query($data);
    }
    
    protected static function auth($params=array(), $curl=true, $deJson=true){
        // $params 请求数据，$curl 是否需要请求出去,默认true,为false时会返回签名的URL
        self::$data = array(
            // 公共参数
            'Format' => 'JSON',
            'Version' => self::$version,
            'AccessKeyId' => self::$accessKeyID,
            'SignatureVersion' => '1.0',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce'=> self::nonce(),
            'TimeStamp' => date(self::gmdateTZ()), 
        );
        foreach($params as $k => $v ){
            self::$data[$k]=$v;
        }
        self::$data['Signature'] = self::sign(self::$data, self::$accessKeySec);
        $url= self::$accessGetway .'/?' . self::httpParams(self::$data);
        if($curl===true && self::$debug == false ){
            if($deJson){
                $dataJson = self::jsonParse(self::curl($url));
                if( @$dataJson['Code'] && @$dataJson['Message']){
                    $dataJson['ErrorMsg'] = self::exception($dataJson['Code']);
                }
                return $dataJson;
            }else{
                return self::curl($url);
            }
        }else{
            return $url;
        }
    }
    
    function __construct( $data ){
        if($data['accessKeyID'] != "")self::$accessKeyID = $data['accessKeyID'];
        if($data['accessKeySec'] != "")self::$accessKeySec = $data['accessKeySec'];
        if($data['accessGetway'] != "")self::$accessGetway = $data['accessGetway'];
        if(@$data['debug'])self::$debug=true;
        if(self::$data === null)self::$data=array();
        date_default_timezone_set("GMT");
        if(self::$exception===null)
            self::$exception = array(
                'UnsupportedOperation' => '（您的账户）不支持该操作',
                'NoSuchVersion' => '无此版本',
                'MissingParameter' => '缺失参数',
                'InvalidParameter' => '无效参数',
                'Throttling' => '服务器忙',
                'InvalidAccessKeyId.NotFound' => '无效accessKeyId.未找到此Id',
                'Forbidden' => '限制访问',
                'SignatureDoesNotMatch' => '签名错误',
                'SignatureNonceUsed' => 'Nonce已被使用过',
                'IdempotentParameterMismatch' => 'Request uses a client token in a previous request but is not identical to that request.',
                'IncorrectInstanceStatus' => '实例状态不支持此操作',
                'InstanceMountedSnapshot' => '实例已经挂载过快照，请先卸载',
                'InvalidSecurityGroupStatus' => '安全组当前状态不支持本操作',
                'InvalidSecurityGroup.InUse' => '当前安全组被实例或其它安全组所引用，不能删除',
                'SecurityGroupLimitExceeded' => '安全组数量超限',
                'SecurityGroupRuleLimitExceeded' => '安全组规则数量超限',
                'SecurityGroupInstanceLimitExceed' => '安全组内实例数量超限',
                'InvalidSnapshot.InUse' => '无效快照，被使用中',
                'InvalidInstanceId.NotFound' => '实例不存在',
                'InvalidInstanceId.Malformed' => '实例ID格式非法',
                'InvalidInstanceType.NotFound' => '实例规格未被找到',
                'InvalidRegionId.NotFound' => '无此数据中心',
                'InvalidZoneId.NotFound' => '无此可用区',
                'InvalidDiskId.NotFound' => '无此磁盘',
                'InvalidDiskId.Malformed' => '磁盘ID格式非法',
                'InvalidDisk.NotReady' => '磁盘未准备好进行此操作',
                'InvalidDiskType.NotFound' => '无此磁盘类型',
                'InvalidSnapshotId.NotFound' => '无此快照',
                'InvalidSnapshotId.Malformed' => '快照ID格式非法',
                'InvalidSnapshot.Unbootable' => '此快照无法引导，是不是为数据盘打的快照？',
                'InvalidSnapshot.NotReady' => '快照还未做好进行此操作的准备',
                'InvalidPassword.Malformed' => '无效密码',
                'InvalidPublicIpAddress.NotFound' => '请求的公网IP不存',
                'InvalidPublicIpAddress.Malformed' => '请求的IP地址格式不对',
                'InvalidHostName.Malformed' => '主机名格式不对',
                'InvalidImageId.NotFound' => '请求的镜像模板不存在',
                'InvalidImageId.Malformed' => '镜像ID格式非法',
                'InvalidSecurityGroupId.Malformed' => '安全组ID格式非法',
                'InvalidSourceGroupId.NotFound' => '请求的来源安全组不存在',
                'InvalidSourceGroupId.Malformed' => '请求的来源来源安全组ID格式非法',
                'InvalidSecurityGroupDescription' => '请求的描述无效',
                'InvalidIpProtocol' => '无效IP协议',
                'InvalidDiskSize.Malformed' => '磁盘无限大小',
                'InvalidDiskSize.Exceeded' => 'The total disk size of the specified instance cannot exceed 5TB',
                'InvalidInternetMaxBandwidth.Malformed' => '请求的公网带宽格式非法',
                'InvalidSourceCidrIp.Malformed' => '请求的来源IP格式不对',
                'InvalidPortRange.Malformed' => '电信通每天要挨么',
                'InvalidPolicy.Malformed' => '无效响应规则',
                'InvalidNicType.Malformed' => '网络类型数据无效'
            );
    }
    

    public function createInstance( $data ){
        $data['Action']="CreateInstance";
        /*
        $data['RegionId']=$regionId;
        $data['ImageId']=$imageId;
        $data['InstanceType']=$instanceType;
        $data['SecurityGroupId']=$securityGroupId;
        if( $internetMaxBandwidthIn != -1 )$data['InternetMaxBandwidthIn']=$internetMaxBandwidthIn;
        if( $internetMaxBandwidthOut != -1 )$data['InternetMaxBandwidthOut']=$internetMaxBandwidthOut;
        if( $hostname != "" )$data['HostName']=$hostname;
        if( $password != "" )$data['Password']=$password;
        if( $zoneid != "" )$data['ZoneId']=$zoneid;
        if( $clientToken != "" )$data['ClientToken']=$clientToken;
        */
        return self::auth($data);
    }

    /**
     Start/PowerON the Given ECS Instance
     */
    public function startInstance( $data ){
        $data['Action']="StartInstance";
        /*
        $data['InstanceId']=$instanceId;
        */
        return self::auth($data);
    }

    /**
     Stop/PowerOFF the Given ECS Instance
     ForceStop means the electric break stopping.
     */
    public function stopInstance( $data ){
        $data['InstanceId']=$instanceId;
        /*
        $data['Action']="StopInstance";
        $data['ForceStop']=$forceStop?"true":"false";
        */
        return self::auth($data);
    }

    /**
     Reboot the Given ECS Instance
     ForceStop means the electric break restarting.
     */
    public function rebootInstance( $data ){
        $data['Action']="RebootInstance";
        /*
        $data['InstanceId']=$instanceId;
        $data['ForceStop']=$forceStop?"true":"false";
        */
        return self::auth($data);
    }

    /**
     Reset the Given ECS Instance
     ImageId is the VHD code.
     DiskType is "system"/"data" disk.
     */
    public function resetInstance( $data ){
        $data['Action']="ResetInstance";
        /*
        $data['InstanceId']=$instanceId;
        if($imageId!=="")$data['ImageId']=$imageId;
        $data['DiskType']=$diskType;
        */
        return self::auth($data);
    }

    public function modifyInstanceSpec( $data ){
        $data['Action']="ModifyInstanceSpec";
        /*
        $data['InstanceId']=$instanceId;
        $data['InstanceType']=$instanceType;
        if($internetMaxBandwidthOut!=-1)$data['InternetMaxBandwidthOut']=$internetMaxBandwidthOut;
        if($internetMaxBandwidthIn!=-1)$data['InternetMaxBandwidthIn']=$internetMaxBandwidthIn;
        */
        return self::auth($data);	
    }
    /**
     Modify the Given ECS Instance
        Password is the password wanna set to be.
        HostName is the new hostname.
        securityGroupId is the new Sec Group
     */
    public function modifyInstanceAttribute( $data ){
        $data['Action']="ModifyInstanceAttribute";
        /*
        $data['InstanceId']=$instanceId;
        if($password!=="")$data['Password']=$password;
        if($hostName!=="")$data['HostName']=$hostName;
        if($securityGroupId!=="")$data['SecurityGroupId']=$securityGroupId;
        */
        return self::auth($data);
    }

    /**
     List ECS Instances of the given Zone.
     ImageId is the VHD code.
     DiskType is "system"/"data" disk.
     */
    public function describeInstanceStatus( $data ){
        $data['Action']="DescribeInstanceStatus";
        /*
        $data['RegionId']=$regionId;
        $data['ZoneId']=$zoneId;
        if($pageNumber!==1)$data['PageNumber']=$pageNumber;
        if($pageSize!==10)$data['PageSize']=$pageSize;
        */
        return self::auth($data);
    }

    /**
     Describe the Given ECS Instance Attribute
     */
    public function describeInstanceAttribute( $data ){
        $data['Action']="DescribeInstanceAttribute";
        /*
        $data['InstanceId']=$instanceId;
        */
        return self::auth($data);
    }

    /**
     List disk(s) of the Given ECS Instance Attribute
     */
    public function describeInstanceDisks( $data ){
        $data['Action']="DescribeInstanceDisks";
        /*
        $data['InstanceId']=$instanceId;
        */
        return self::auth($data);
    }

    public function createImage( $data ){
        $data['Action']="CreateImage";
        /*
        $data['RegionId']=$regionId;
        $data['SnapshotId']=$snapshotId;
        $data['ImageVersion']=$imageVersion;
        $data['Description']=$description;
        $data['Visibility']=$visibility;
        */
        return self::auth($data);
    }

    /**
     List image(s) of the Given Region
     */
    public function describeImages( $data ){
        $data['Action']="DescribeImages";
        /*
        $data['RegionId']=$regionId;
        if($pageNumber!==1)$data['PageNumber']=$pageNumber;
        if($pageSize!==10)$data['PageSize']=$pageSize;
        */
        return self::auth($data);
    }

    /**
     Allocate a new PublicIpAddress for the Given ECS Instance Attribute
     Note:the instance must have no public ip or it will return error
     */
    public function allocatePublicIpAddress( $data ){
        $data['Action']="AllocatePublicIpAddress";
        /*
        $data['InstanceId']=$instanceId;
        */
        return self::auth($data);
    }
    
    /**
     Release the given PublicIpAddress
     */
    public function releasePublicIpAddress( $data ){
        $data['Action']="ReleasePublicIpAddress";
        /*
        $data['PublicIpAddress']=$publicIpAddress;
        */
        return self::auth($data);
    }
    
    /**
     Create a new SecurityGroup
     */
    public function createSecurityGroup( $data ){
        $data['Action']="CreateSecurityGroup";
        /*
        $data['RegionId']=$regionId;
        $data['Description']=$description;
        */
        return self::auth($data);
    }
    
    /**
     Authorize a given SecurityGroup the network access permission
     SecurityGroupId 安全组编码
     RegionId 安全组所属 Region ID
     IpProtocol IP 协议，取值：tcp|udp|icmp|gre|all；All表示同时支持四种协议
     PortRange IP 协议相关的端口号范围，tcp、udp 协议的默认端口号，取值范围为 1~65535；例如“1/200”意思是端口号范围为 1~200，若输入值为：“200/1”接口调用将报错。icmp 协议时端口号范围值为-1/-1，gre 协议时端口号范围值为-1/-1，当IpProtocol 为 all时端口号范围值为-1/-1；取值范围SourceGroupId String 否 授权同一Region内可访问目标安全组的源安全组编码
     SourceGroupId 或者SourceCidrIp 参数必须设置一项，如果两项都设置，则默认对
     SourceCidrIp 授权。指定了该字段之后，NicType 只能选择 intranet
     SourceCidrIp 授权可访问目标安全组的源 IP地址范围（采用 CIDR格式来指定 IP 地址范围），默认值为 0.0.0.0/0（表示不受限制），其他支持的格式如 10.159.6.18/12、10.159.6.186、或10.159.6.186-10.159.6.201（IP 区间）
     Policy 授权策略，参数值可为：accept（接受访问）默认值为：accept
     NicType 网络类型，取值：internet|intranet；默认值为 internet
     */
    public function authorizeSecurityGroup( $data ){
        $data['Action']="AuthorizeSecurityGroup";
        /*
        $data['SecurityGroupId']=$securityGroupId;
        $data['RegionId']=$regionId;
        $data['IpProtocol']=$ipProtocol;
        $data['PortRange']=$portRange;
        if($sourceGroupId!="")$data['SourceGroupId']=$sourceGroupId;
        if($sourceCidrIp!="")$data['SourceCidrIp']=$sourceCidrIp;
        $data['Policy']=$policy;
        $data['NicType']=$nicType;
        */
        return self::auth($data);
    }
    
    /**
     Describe a given SecurityGroup
     SecurityGroupId 安全组编码
     RegionId 安全组所属 Region ID
     NicType String 取值：internet|intranet 不指定时默认值为 internet
     */
    public function describeSecurityGroupAttribute( $data ){
        $data['Action']="DescribeSecurityGroupAttribute";
        /*
        $data['SecurityGroupId']=$securityGroupId;
        $data['RegionId']=$regionId;
        $data['NicType']=$nicType;
        */
        return self::auth($data);
    }
    
    
    /**
     List all SecurityGroup
     RegionId 安全组所属 Region ID
     PageNumber 当前页码，起始值为 1，默认值为 1
     PageSize 分页查询时设置的每页行数，最大值 50，默认值为 10
     */
    public function describeSecurityGroups( $data ){
        $data['Action']="DescribeSecurityGroups";
        /*
        $data['RegionId']=$regionId;
        if($pageNumber!=1)$data['PageNumber']=$pageNumber;
        if($pageSize!=10)$data['PageSize']=$pageSize;
        */
        return self::auth($data);
    }
    
    /**
     Revoke a given SecurityGroup
     SecurityGroupId 安全组编码
     RegionId 安全组所属 Region ID
     IpProtocol IP 协议，取值：tcp|udp|icmp|gre|all；All表示同时支持四种协议
     PortRange IP 协议相关的端口号范围，tcp、udp 协议的默认端口号，取值范围为 1~65535；例如“1/200”意思是端口号范围为 1~200，若输入值为：“200/1”接口调用将报错。icmp 协议时端口号范围值为-1/-1，gre 协议时端口号范围值为-1/-1，当IpProtocol 为 all时端口号范围值为-1/-1；取值范围SourceGroupId String 否 授权同一Region内可访问目标安全组的源安全组编码
     SourceGroupId 或者SourceCidrIp 参数必须设置一项，如果两项都设置，则默认对
     SourceCidrIp 授权。指定了该字段之后，NicType 只能选择 intranet
     SourceCidrIp 授权可访问目标安全组的源 IP地址范围（采用 CIDR格式来指定 IP 地址范围），默认值为 0.0.0.0/0（表示不受限制），其他支持的格式如 10.159.6.18/12、10.159.6.186、或10.159.6.186-10.159.6.201（IP 区间）
     Policy 授权策略，参数值可为：accept（接受访问）默认值为：accept
     NicType 网络类型，取值：internet|intranet；默认值为 internet
     */
    public function revokeSecurityGroup( $data ){
        $data['Action']="RevokeSecurityGroup";
        /*
        $data['SecurityGroupId']=$securityGroupId;
        $data['RegionId']=$regionId;
        $data['IpProtocol']=$ipProtocol;
        $data['PortRange']=$portRange;
        $data['SourceGroupId']=$sourceGroupId;
        $data['SourceCidrIp']=$sourceCidrIp;
        $data['Policy']=$policy;
        $data['NicType']=$nicType;
        */
        return self::auth($data);
    }
    
    /**
     Delete a given SecurityGroup
     */
    public function deleteSecurityGroup( $data ){
        $data['Action']="DeleteSecurityGroup";
        /*
        $data['SecurityGroupId']=$securityGroupId;
        $data['RegionId']=$regionId;
        */
        return self::auth($data);
    }

    /**
     List All Regions
     */
    public function describeRegions(){
        $data['Action']="DescribeRegions";
        return self::auth($data);
    }

    /**
     get Monitor Data of a given instance
     */
    public function getMonitorData( $data ){
        // The time and the instanceId can not request them all at the present time. 当前不支持查询某实例 在某时间的监控信息。
        $data['Action']="GetMonitorData";
        /*
        $data['RegionId']=$regionId;
        if($instanceId!==null)$data['InstanceId']=$instanceId;
        if($time!==null)$data['Time']=$time;
        if($pageNumber!==null)$data['PageNumber']=$pageNumber;
        if($pageSize!==null)$data['PageSize']=$pageSize;
        */
        return self::auth($data);
    }


    /**
     List all ECS type
     */
    public function describeInstanceTypes(){
        $data['Action']="DescribeInstanceTypes";
        return self::auth($data);
    }


} 

?>

