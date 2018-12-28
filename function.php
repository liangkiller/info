    <?php
    //POST
    function curl_post($url,$curlPost,$headerArr,$HEADER=0,$getCookie=0){
            $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
            $ch = curl_init($url);//初始化curl
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HEADER, $HEADER);//设置header
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);//$data JSON类型字符串
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER , $headerArr );
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate,br');
            if ($ssl){
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , FALSE);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE);
            }
            $return = curl_exec($ch);//运行curl
            // 检查是否有错误发生
             if( curl_errno ( $ch )) {
                echo  'Curl error: '  .  curl_error ( $ch );
            }
            curl_close($ch);
            //print_r($return);exit;
            // 获得cookie
            if($HEADER && $getCookie) {
                preg_match_all('/Set-Cookie:(.*);/simU',$return,$str);
                $cookie = trim($str[1][0]).";".trim($str[1][1]); //获得COOKIE（SESSIONID）
                return $cookie;
            }
            if($HEADER){
                 print_r($return);
            }else{
                if(is_json($return)){
                    $eagle_alarm=json_decode($return);
                }else{
                    $eagle_alarm=$return;
                }
                return $eagle_alarm;
            }
    }
    //GET
    function curl_get($url,$headerArr,$HEADER=0){
            $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
            //echo "抓取:".$url."<br/>";
            $ch = curl_init($url);//初始化curl
            curl_setopt($ch, CURLOPT_HEADER, $HEADER);//设置header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER , $headerArr );
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
            if ($ssl){
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , FALSE);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE);
            }
            $return = curl_exec($ch);//运行curl
            // 检查是否有错误发生
             if( curl_errno ( $ch ))
            {
                echo  'Curl error: '  .  curl_error ( $ch );
            }
            curl_close($ch);
            //print_r($return);exit;
            if($HEADER){
                 return $return;
            }else{
                if(is_json($return)){
                    $eagle_alarm['data']=object_array(json_decode($return)->data);
                    //$eagle_alarm['code']=object_array(json_decode($return)->pageInfo);
                }else{
                    $eagle_alarm=$return;
                }
                return $eagle_alarm;
            }
    }
    //判断是否json
    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    //返回的数据对象，解析为数组
    function object_array($array)
    {
       if(is_object($array))
       {
        $array = (array)$array;
       }
       if(is_array($array))
       {
        foreach($array as $key=>$value)
        {
         $array[$key] = object_array($value);
        }
       }
       return $array;
    }
    //改变文件的编码
    function changeFileEncode($fromFile,$toFile,$toEncode="UTF-8"){
        // 变换文件如果不存在，退出
        if(!file_exists($fromFile)) {
            echo "no find ".$fromFile;
            return ;
        }
        // 文件转换
        $data = file_get_contents($fromFile);
        $fromEncode = mb_detect_encoding($data, array('UTF-8','GB18030','CP936','CP950','HZ','EUC-CN','BIG-5','EUC-TW'));
        $content = mb_convert_encoding($data, $toEncode, $fromEncode);
        // 输入文件如果存在先删除
        if(file_exists($toFile)){
            unlink($toFile);
        }
        // 写入输出文件
        file_put_contents($toFile, $content);
    }
    //改变字符串的编码
    function changeStringEncode($str,$toEncode="UTF-8"){
        $data = $str;
        $fromEncode = mb_detect_encoding($data, array('UTF-8','GB18030','CP936','CP950','HZ','EUC-CN','BIG-5','EUC-TW'));
        $content = mb_convert_encoding($data, $toEncode, $fromEncode);
        return $content;
    }
    //CSV文件转数组
    function csv_to_array($csv)
        {
            $len = strlen($csv);
            $table = array();
            $cur_row = array();
            $cur_val = "";
            $state = "first item";
            for ($i = 0; $i < $len; $i++)
            {
                //sleep(1000);
                $ch = substr($csv,$i,1);
                if ($state == "first item")
                {
                    if ($ch == '"') $state = "we're quoted hea";
                    elseif ($ch == ",") //empty
                    {
                        $cur_row[] = ""; //done with first one
                        $cur_val = "";
                        $state = "first item";
                    }
                    elseif ($ch == "\n")
                    {
                        $cur_row[] = $cur_val;
                        $table[] = $cur_row;
                        $cur_row = array();
                        $cur_val = "";
                        $state = "first item";
                    }
                    elseif ($ch == "\r") $state = "wait for a line feed, if so close out row!";
                    else
                    {
                        $cur_val .= $ch;
                        $state = "gather not quote";
                    }
                }
                elseif ($state == "we're quoted hea")
                {
                    if ($ch == '"') $state = "potential end quote found";
                    else $cur_val .= $ch;
                }
                elseif ($state == "potential end quote found")
                {
                    if ($ch == '"')
                    {
                        $cur_val .= '"';
                        $state = "we're quoted hea";
                    }
                    elseif ($ch == ',')
                    {
                        $cur_row[] = $cur_val;
                        $cur_val = "";
                        $state = "first item";
                    }
                    elseif ($ch == "\n")
                    {
                        $cur_row[] = $cur_val;
                        $table[] = $cur_row;
                        $cur_row = array();
                        $cur_val = "";
                        $state = "first item";
                    }
                    elseif ($ch == "\r") $state = "wait for a line feed, if so close out row!";
                    else
                    {
                        $cur_val .= $ch;
                        $state = "we're quoted hea";
                    }
                }
                elseif ($state == "wait for a line feed, if so close out row!")
                {
                    if ($ch == "\n")
                    {
                        $cur_row[] = $cur_val;
                        $cur_val = "";
                        $table[] = $cur_row;
                        $cur_row = array();
                        $state = "first item";
                    }
                    else
                    {
                        $cur_row[] = $cur_val;
                        $table[] = $cur_row;
                        $cur_row = array();
                        $cur_val = $ch;
                        $state = "gather not quote";
                    }
                }
                elseif ($state == "gather not quote")
                {
                    if ($ch == ",")
                    {
                        $cur_row[] = $cur_val;
                        $cur_val = "";
                        $state = "first item";
                    }
                    elseif ($ch == "\n")
                    {
                        $cur_row[] = $cur_val;
                        $table[] = $cur_row;
                        $cur_row = array();
                        $cur_val = "";
                        $state = "first item";
                    }
                    elseif ($ch == "\r") $state = "wait for a line feed, if so close out row!";
                    else $cur_val .= $ch;
                }
            }
            return $table;
        }
    //以;分隔的时间;监控IP，按时间排序
    function  cmp ( $a ,  $b )
    {
         $a  =  preg_replace ( '@;.* @' ,  '' ,  $a );
         $b  =  preg_replace ( '@;.* @' ,  '' ,  $b );
        return  strcasecmp ( $a ,  $b );
    }
    ?>
