<?php
namespace thinkweb\zapay\Common\Yinlian\sdk;;

class SDKConfig {
	
	private static $_config = null;

    /**
     * @return static
     */
	public static function getSDKConfig(){
		if (SDKConfig::$_config == null ) {
			SDKConfig::$_config = new SDKConfig();
		}
		return SDKConfig::$_config;
	}

	function __construct(){

	}
    protected $configs = [];
	function setConfig($configs){
        $this->configs = $configs;
    }

	public function __get($property_name){
		if($this->configs && isset($this->configs->$property_name)){
			return($this->configs->$property_name);
		}else{
			return(NULL);
		}
	}

}