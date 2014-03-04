<?php
/**
 * Think 函数库
 */
class Think {
    
    // 实例化对象
    private static $_instance = array();
    
    /**
     * 获取和设置配置参数 支持批量定义
     * @param string|array $name 配置变量
     * @param mixed $value 配置值
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function C($name=null, $value=null,$default=null) {
        static $_config = array();
            $_config = Yaf_Registry::get("config")->think->toArray();
        // 无参数时获取所有
        if (empty($name)) {
            return $_config;
        }
        // 优先执行设置获取或赋值
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                //$name = strtolower($name);
                if (is_null($value))
                    return isset($_config[$name]) ? $_config[$name] : $default;
                $_config[$name] = $value;
                return;
            }
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            $name[0]   =  strtolower($name[0]);
            if (is_null($value))
                return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
            $_config[$name[0]][$name[1]] = $value;
            return;
        }
        // 批量设置
        if (is_array($name)){
            $_config = array_merge($_config, array_change_key_case($name));
            return;
        }
        return null; // 避免非法参数
    }

    /**
     * 抛出异常处理
     * @param string $msg 异常消息
     * @param integer $code 异常代码 默认为0
     * @return void
     */
    public static function E($msg, $code=0) {
        //throw new Think\Exception($msg, $code);
        die($msg);
    }

    /**
     * 记录和统计时间（微秒）和内存使用情况
     * 使用方法:
     * <code>
     * G('begin'); // 记录开始标记位
     * // ... 区间运行代码
     * G('end'); // 记录结束标签位
     * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
     * echo G('begin','end','m'); // 统计区间内存使用情况
     * 如果end标记位没有定义，则会自动以当前作为标记位
     * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
     * </code>
     * @param string $start 开始标签
     * @param string $end 结束标签
     * @param integer|string $dec 小数位或者m
     * @return mixed
     */
    public static function G($start,$end='',$dec=4) {
        static $_info       =   array();
        static $_mem        =   array();
        if(is_float($end)) { // 记录时间
            $_info[$start]  =   $end;
        }elseif(!empty($end)){ // 统计时间和内存使用
            if(!isset($_info[$end])) $_info[$end]       =  microtime(TRUE);
            if(Think::C('MEMORY_LIMIT_ON') && $dec=='m'){
                if(!isset($_mem[$end])) $_mem[$end]     =  memory_get_usage();
                return number_format(($_mem[$end]-$_mem[$start])/1024);
            }else{
                return number_format(($_info[$end]-$_info[$start]),$dec);
            }

        }else{ // 记录时间和内存使用
            $_info[$start]  =  microtime(TRUE);
            if(Think::C('MEMORY_LIMIT_ON')) $_mem[$start]           =  memory_get_usage();
        }
    }

    /**
     * 获取和设置语言定义(不区分大小写)
     * @param string|array $name 语言变量
     * @param mixed $value 语言值或者变量
     * @return mixed
     */
    public static function L($name=null, $value=null) {
        static $_lang = array();
        // 空参数返回所有定义
        if (empty($name))
            return $_lang;
        // 判断语言获取(或设置)
        // 若不存在,直接返回全大写$name
        if (is_string($name)) {
            $name   =   strtoupper($name);
            if (is_null($value)){
                return isset($_lang[$name]) ? $_lang[$name] : $name;
            }elseif(is_array($value)){
                // 支持变量
                $replace = array_keys($value);
                foreach($replace as &$v){
                    $v = '{$'.$v.'}';
                }
                return str_replace($replace,$value,isset($_lang[$name]) ? $_lang[$name] : $name);        
            }
            $_lang[$name] = $value; // 语言定义
            return;
        }
        // 批量定义
        if (is_array($name))
            $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
        return;
    }
    /**
     * 获取输入参数 支持过滤和默认值
     * 使用方法:
     * <code>
     * I('id',0); 获取id参数 自动判断get或者post
     * I('post.name','','htmlspecialchars'); 获取$_POST['name']
     * I('get.'); 获取$_GET
     * </code>
     * @param string $name 变量的名称 支持指定类型
     * @param mixed $default 不存在的时候默认值
     * @param mixed $filter 参数过滤方法
     * @return mixed
     */
    public static function I($name,$default='',$filter=null) {
        if(strpos($name,'.')) { // 指定参数来源
            list($method,$name) =   explode('.',$name,2);
        }else{ // 默认为自动判断
            $method =   'param';
        }
        switch(strtolower($method)) {
            case 'get'     :   $input =& $_GET;break;
            case 'post'    :   $input =& $_POST;break;
            case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
            case 'param'   :
                switch($_SERVER['REQUEST_METHOD']) {
                    case 'POST':
                        $input  =  $_POST;
                        break;
                    case 'PUT':
                        parse_str(file_get_contents('php://input'), $input);
                        break;
                    default:
                        $input  =  $_GET;
                }
                break;
            case 'request' :   $input =& $_REQUEST;   break;
            case 'session' :   $input =& $_SESSION;   break;
            case 'cookie'  :   $input =& $_COOKIE;    break;
            case 'server'  :   $input =& $_SERVER;    break;
            case 'globals' :   $input =& $GLOBALS;    break;
            default:
                return NULL;
        }
        if(empty($name)) { // 获取全部变量
            $data       =   $input;
            array_walk_recursive($data,'filter_exp');
            $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
            if($filters) {
                $filters    =   explode(',',$filters);
                foreach($filters as $filter){
                    $data   =   array_map_recursive($filter,$data); // 参数过滤
                }
            }
        }elseif(isset($input[$name])) { // 取值操作
            $data       =   $input[$name];
            is_array($data) && array_walk_recursive($data,'filter_exp');
            $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
            if($filters) {
                $filters    =   explode(',',$filters);
                foreach($filters as $filter){
                    if(function_exists($filter)) {
                        $data   =   is_array($data)?array_map_recursive($filter,$data):$filter($data); // 参数过滤
                    }else{
                        $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                        if(false === $data) {
                            return   isset($default)?$default:NULL;
                        }
                    }
                }
            }
        }else{ // 变量默认值
            $data       =    isset($default)?$default:NULL;
        }
        return $data;
    }
    /**
     * 设置和获取统计数据
     * 使用方法:
     * <code>
     * N('db',1); // 记录数据库操作次数
     * N('read',1); // 记录读取次数
     * echo N('db'); // 获取当前页面数据库的所有操作次数
     * echo N('read'); // 获取当前页面读取次数
     * </code>
     * @param string $key 标识位置
     * @param integer $step 步进值
     * @return mixed
     */
    public static function N($key, $step=0,$save=false) {
        static $_num    = array();
        if (!isset($_num[$key])) {
            $_num[$key] = (false !== $save)? self::S('N_'.$key) :  0;
        }
        if (empty($step))
            return $_num[$key];
        else
            $_num[$key] = $_num[$key] + (int) $step;
        if(false !== $save){ // 保存结果
            self::S('N_'.$key,$_num[$key],$save);
        }
    }
    /**
    * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
    * @param string $name 缓存名称
    * @param mixed $value 缓存值
    * @param string $path 缓存路径
    * @return mixed
    */
   public static function F($name, $value='', $path=DATA_PATH) {
       static $_cache  =   array();
       $filename       =   $path . $name . '.php';
       if ('' !== $value) {
           if (is_null($value)) {
               // 删除缓存
               if(false !== strpos($name,'*')){
                   return false; // TODO 
               }else{
                   unset($_cache[$name]);
                   return Storage::unlink($filename,'F');
               }
           } else {
               Storage::put($filename,serialize($value),'F');
               // 缓存数据
               $_cache[$name]  =   $value;
               return ;
           }
       }
       // 获取缓存数据
       if (isset($_cache[$name]))
           return $_cache[$name];
       if (Storage::has($filename,'F')){
           $value      =   unserialize(Storage::read($filename,'F'));
           $_cache[$name]  =   $value;
       } else {
           $value          =   false;
       }
       return $value;
   }
   /**
    * 缓存管理
    * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
    * @param mixed $value 缓存值
    * @param mixed $options 缓存参数
    * @return mixed
    */
   public static function S($name,$value='',$options=null) {
       static $cache   =   '';
       if(is_array($options) && empty($cache)){
           // 缓存操作的同时初始化
           $type       =   isset($options['type'])?$options['type']:'';
           $cache      =   Cache::getInstance($type,$options);
       }elseif(is_array($name)) { // 缓存初始化
           $type       =   isset($name['type'])?$name['type']:'';
           $cache      =   Cache::getInstance($type,$name);
           return $cache;
       }elseif(empty($cache)) { // 自动初始化
           $cache      =   Cache::getInstance();
       }
       if(''=== $value){ // 获取缓存
           return $cache->get($name);
       }elseif(is_null($value)) { // 删除缓存
           return $cache->rm($name);
       }else { // 缓存数据
           if(is_array($options)) {
               $expire     =   isset($options['expire'])?$options['expire']:NULL;
           }else{
               $expire     =   is_numeric($options)?$options:NULL;
           }
           return $cache->set($name, $value, $expire);
       }
   }
   /**
    * 根据PHP各种类型变量生成唯一标识号
    * @param mixed $mix 变量
    * @return string
    */
   public static function to_guid_string($mix) {
       if (is_object($mix)) {
           return spl_object_hash($mix);
       } elseif (is_resource($mix)) {
           $mix = get_resource_type($mix) . strval($mix);
       } else {
           $mix = serialize($mix);
       }
       return md5($mix);
   }
   /**
    * 取得对象实例 支持调用类的静态方法
    * @param string $class 对象类名
    * @param string $method 类的静态方法名
    * @return object
    */
   public static function instance($class,$method='') {
       $identify   =   $class.$method;
       if(!isset(self::$_instance[$identify])) {
           if(class_exists($class)){
               $o = new $class();
               if(!empty($method) && method_exists($o,$method))
                   self::$_instance[$identify] = call_user_func(array(&$o, $method));
               else
                   self::$_instance[$identify] = $o;
           }
           else
               self::halt(self::L('_CLASS_NOT_EXIST_').':'.$class);
       }
       return self::$_instance[$identify];
   }
}