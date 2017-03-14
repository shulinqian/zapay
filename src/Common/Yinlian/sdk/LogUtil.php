<?php
namespace thinkweb\zapay\Common\Yinlian\sdk;

class LogUtil
{
	private static $_logger = null;

    /**
     * @return PhpLog
     */
	public static function getLogger()
	{
		if (LogUtil::$_logger == null ) {
            url()
			$l = SDKConfig::getSDKConfig()->logLevel;
			if("INFO" == strtoupper($l))
				$level = PhpLog::INFO;
			else if("DEBUG" == strtoupper($l))
				$level = PhpLog::DEBUG;
			else if("ERROR" == strtoupper($l))
				$level = PhpLog::ERROR;
			else if("WARN" == strtoupper($l))
				$level = PhpLog::WARN;
			else if("FATAL" == strtoupper($l))
				$level = PhpLog::FATAL;
			else
				$level = PhpLog::OFF;
			LogUtil::$_logger = new PhpLog ( SDKConfig::getSDKConfig()->logFilePath, "PRC", $level );
		}
		return self::$_logger;
	}
}
