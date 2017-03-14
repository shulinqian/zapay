<?php
/**
 * @author: helei
 * @createTime: 2016-07-28 18:04
 * @description: 微信的策略基类
 */

namespace thinkweb\zapay\Common\Yinlian;

use thinkweb\zapay\Common\BaseData;
use thinkweb\zapay\Common\BaseStrategy;
use thinkweb\zapay\Common\PayException;
use thinkweb\zapay\Common\WxConfig;
use thinkweb\zapay\Common\Yinlian\sdk\AcpService;
use thinkweb\zapay\Common\Yinlian\sdk\SDKConfig;
use thinkweb\zapay\Common\YinlianConfig;
use thinkweb\zapay\Utils\ArrayUtil;
use thinkweb\zapay\Utils\Curl;
use thinkweb\zapay\Utils\DataParser;

/**
 * Class WxBaseStrategy
 * 银联策略基类
 *
 * @package Payment\Common\Weixin
 * anthor helei
 */
abstract class YinlianBaseStrategy implements BaseStrategy
{

    /**
     * 支付宝的配置文件
     * @var WxConfig $config
     */
    protected $config;

    /**
     * 支付数据
     * @var BaseData $reqData
     */
    protected $reqData;

    /**
     * WxBaseStrategy constructor.
     * @param array $config
     * @throws PayException
     */
    public function __construct(array $config)
    {
        try {
            $this->config = new YinlianConfig($config);
        } catch (PayException $e) {
            throw $e;
        }
    }

    /**
     * 获取支付对应的数据完成类
     * @return BaseData
     * @author helei
     */
    abstract protected function getBuildDataClass();

    /**
     * @param array $data
     * @author helei
     * @throws PayException
     * @return array|string
     */
    public function handle(array $data){
        $buildClass = $this->getBuildDataClass();
        try {
            /** @var \thinkweb\zapay\Common\Yinlian\Data\Charge\B2bChargeData reqData */
            $this->reqData = new $buildClass($this->config, $data);
        } catch (PayException $e) {
            throw $e;
        }

        $this->reqData->setSign();
        $uri = SDKConfig::getSDKConfig()->frontTransUrl;
        $html_form = AcpService::createAutoFormHtml( $this->reqData->getData(), $uri );
        return $html_form;
    }

    /**
     * 处理微信的返回值并返回给客户端
     * @param array $ret
     * @return mixed
     * @author helei
     */
    protected function retData(array $ret)
    {
        return $ret;
    }

    /**
     * 检查微信返回的数据是否被篡改过
     * @param array $retData
     * @return boolean
     * @author helei
     */
    protected function signVerify(array $retData)
    {
        $retSign = $retData['sign'];
        $values = ArrayUtil::removeKeys($retData, ['sign', 'sign_type']);

        $values = ArrayUtil::paraFilter($values);

        $values = ArrayUtil::arraySort($values);

        $signStr = ArrayUtil::createLinkstring($values);

        $signStr .= "&key=" . $this->config->md5Key;

        $string = md5($signStr);

        return strtoupper($string) === $retSign;
    }
}