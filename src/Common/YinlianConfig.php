<?php
/**
 * @author: helei
 * @createTime: 2016-07-15 14:56
 * @description: 微信配置文件
 * @link      https://github.com/helei112g/payment/tree/paymentv2
 * @link      https://helei112g.github.io/
 */

namespace thinkweb\zapay\Common;


use thinkweb\zapay\Common\Yinlian\sdk\SDKConfig;
use thinkweb\zapay\Utils\ArrayUtil;
use thinkweb\zapay\Utils\StrUtil;

final class YinlianConfig extends ConfigInterface
{
    public $merId;

    public $frontTransUrl;
    public $backTransUrl;
    public $singleQueryUrl;
    public $batchTransUrl;
    public $fileTransUrl;
    public $appTransUrl;
    public $cardTransUrl;
    public $jfFrontTransUrl;
    public $jfBackTransUrl;
    public $jfSingleQueryUrl;
    public $jfCardTransUrl;
    public $jfAppTransUrl;
    public $qrcBackTransUrl;
    public $qrcB2cIssBackTransUrl;
    public $qrcB2cMerBackTransUrl;

    public $signMethod;
    public $version;
    public $ifValidateCNName;
    public $ifValidateRemoteCert;

    public $signCertPath;
    public $signCertPwd;
    public $validateCertDir;
    public $encryptCertPath;
    public $rootCertPath;
    public $middleCertPath;
    public $frontUrl;
    public $backUrl;
    public $secureKey;
    public $logFilePath;
    public $logLevel;
    /**
     * 初始化微信配置文件
     * WxConfig constructor.
     * @param array $config
     * @throws PayException
     */
    public function __construct(array $config)
    {
        try {
            if($config['envDev']){
                $ini_array = parse_ini_file(__DIR__  . '/Yinlian/acp_sdk_dev.ini', true);
            } else {
                $ini_array = parse_ini_file(__DIR__  . '/Yinlian/acp_sdk.ini', true);
            }

            $sdk_array = $ini_array["acpsdk"];

            $sdk_array['acpsdk.merId'] = $config['merId'];
            $sdk_array['acpsdk.frontUrl'] = $config['frontUrl'];
            $sdk_array['acpsdk.backUrl'] = $config['backUrl'];
            $sdk_array['acpsdk.signCert.path'] = $config['signCert'];
            $sdk_array['acpsdk.signCert.pwd'] = $config['signCertPwd'];
            $sdk_array['acpsdk.encryptCert.path'] = $config['encryptCert'];
            $sdk_array['acpsdk.middleCert.path'] = $config['middleCert'];
            $sdk_array['acpsdk.rootCert.path'] = $config['rootCert'];
            $sdk_array['acpsdk.log.file.path'] = RUNTIME_PATH . '/yinlianlogs';
            $this->initConfig($sdk_array);

            SDKConfig::getSDKConfig()->setConfig($this);
        } catch (PayException $e) {
            throw $e;
        }
    }

    /**
     * 初始化配置文件参数
     * @param array $config
     * @throws PayException
     */
    public function initConfig(array $sdk_array)
    {
        $this->merId = array_key_exists("acpsdk.merId", $sdk_array)?$sdk_array["acpsdk.merId"] : null;

        $this->frontTransUrl = array_key_exists("acpsdk.frontTransUrl", $sdk_array)?$sdk_array["acpsdk.frontTransUrl"] : null;
        $this->backTransUrl = array_key_exists("acpsdk.backTransUrl", $sdk_array)?$sdk_array["acpsdk.backTransUrl"] : null;
        $this->singleQueryUrl = array_key_exists("acpsdk.singleQueryUrl", $sdk_array)?$sdk_array["acpsdk.singleQueryUrl"] : null;
        $this->batchTransUrl = array_key_exists("acpsdk.batchTransUrl", $sdk_array)?$sdk_array["acpsdk.batchTransUrl"] : null;
        $this->fileTransUrl = array_key_exists("acpsdk.fileTransUrl", $sdk_array)?$sdk_array["acpsdk.fileTransUrl"] : null;
        $this->appTransUrl = array_key_exists("acpsdk.appTransUrl", $sdk_array)?$sdk_array["acpsdk.appTransUrl"] : null;
        $this->cardTransUrl = array_key_exists("acpsdk.cardTransUrl", $sdk_array)?$sdk_array["acpsdk.cardTransUrl"] : null;
        $this->jfFrontTransUrl = array_key_exists("acpsdk.jfFrontTransUrl", $sdk_array)?$sdk_array["acpsdk.jfFrontTransUrl"] : null;
        $this->jfBackTransUrl = array_key_exists("acpsdk.jfBackTransUrl", $sdk_array)?$sdk_array["acpsdk.jfBackTransUrl"] : null;
        $this->jfSingleQueryUrl = array_key_exists("acpsdk.jfSingleQueryUrl", $sdk_array)?$sdk_array["acpsdk.jfSingleQueryUrl"] : null;
        $this->jfCardTransUrl = array_key_exists("acpsdk.jfCardTransUrl", $sdk_array)?$sdk_array["acpsdk.jfCardTransUrl"] : null;
        $this->jfAppTransUrl = array_key_exists("acpsdk.jfAppTransUrl", $sdk_array)?$sdk_array["acpsdk.jfAppTransUrl"] : null;
        $this->qrcBackTransUrl = array_key_exists("acpsdk.qrcBackTransUrl", $sdk_array)?$sdk_array["acpsdk.qrcBackTransUrl"] : null;
        $this->qrcB2cIssBackTransUrl = array_key_exists("acpsdk.qrcB2cIssBackTransUrl", $sdk_array)?$sdk_array["acpsdk.qrcB2cIssBackTransUrl"] : null;
        $this->qrcB2cMerBackTransUrl = array_key_exists("acpsdk.qrcB2cMerBackTransUrl", $sdk_array)?$sdk_array["acpsdk.qrcB2cMerBackTransUrl"] : null;

        $this->signMethod = array_key_exists("acpsdk.signMethod", $sdk_array)?$sdk_array["acpsdk.signMethod"] : null;
        $this->version = array_key_exists("acpsdk.version", $sdk_array)?$sdk_array["acpsdk.version"] : null;
        $this->ifValidateCNName = array_key_exists("acpsdk.ifValidateCNName", $sdk_array)?$sdk_array["acpsdk.ifValidateCNName"] : "true";
        $this->ifValidateRemoteCert = array_key_exists("acpsdk.ifValidateRemoteCert", $sdk_array)?$sdk_array["acpsdk.ifValidateRemoteCert"] : "false";

        $this->signCertPath = array_key_exists("acpsdk.signCert.path", $sdk_array)?$sdk_array["acpsdk.signCert.path"]: null;
        $this->signCertPwd = array_key_exists("acpsdk.signCert.pwd", $sdk_array)?$sdk_array["acpsdk.signCert.pwd"]: null;

        $this->validateCertDir = array_key_exists("acpsdk.validateCert.dir", $sdk_array)? $sdk_array["acpsdk.validateCert.dir"]: null;
        $this->encryptCertPath = array_key_exists("acpsdk.encryptCert.path", $sdk_array)? $sdk_array["acpsdk.encryptCert.path"]: null;
        $this->rootCertPath = array_key_exists("acpsdk.rootCert.path", $sdk_array)? $sdk_array["acpsdk.rootCert.path"]: null;
        $this->middleCertPath =  array_key_exists("acpsdk.middleCert.path", $sdk_array)?$sdk_array["acpsdk.middleCert.path"]: null;

        $this->frontUrl =  array_key_exists("acpsdk.frontUrl", $sdk_array)?$sdk_array["acpsdk.frontUrl"]: null;
        $this->backUrl =  array_key_exists("acpsdk.backUrl", $sdk_array)?$sdk_array["acpsdk.backUrl"]: null;

        $this->secureKey =  array_key_exists("acpsdk.secureKey", $sdk_array)?$sdk_array["acpsdk.secureKey"]: null;
        $this->logFilePath =  array_key_exists("acpsdk.log.file.path", $sdk_array)?$sdk_array["acpsdk.log.file.path"]: null;
        $this->logLevel =  array_key_exists("acpsdk.log.level", $sdk_array)?$sdk_array["acpsdk.log.level"]: null;
    }


}
