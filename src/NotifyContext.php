<?php
/**
 * @author: helei
 * @createTime: 2016-07-14 17:42
 * @description: 暴露给客户端调用的接口
 * @link      https://github.com/helei112g/payment/tree/paymentv2
 * @link      https://helei112g.github.io/
 */

namespace thinkweb\zapay;



use thinkweb\zapay\Notify\AliNotify;
use thinkweb\zapay\Notify\NotifyStrategy;
use thinkweb\zapay\Notify\PayNotifyInterface;
use thinkweb\zapay\Notify\WxNotify;
use thinkweb\zapay\Common\PayException;
use thinkweb\zapay\Notify\YinlianNotify;

class NotifyContext
{
    /**
     * 支付的渠道
     * @var NotifyStrategy
     */
    protected $notify;


    /**
     * 设置对应的通知渠道
     * @param string $channel 通知渠道
     *  - @see Config
     * 
     * @param array $config 配置文件
     * @throws PayException
     * @author helei
     */
    public function initNotify($channel, array $config)
    {
        try{
            switch ($channel) {
                case Config::ALI:
                    $this->notify = new AliNotify($config);
                    break;
                case Config::WEIXIN:
                    $this->notify = new WxNotify($config);
                    break;
                case Config::YINLIAN:
                    $this->notify = new YinlianNotify($config);
                    break;
                default:
                    throw new PayException('当前仅支持：ALI WEIXIN YINLIANS个常量');
            }
        } catch (PayException $e) {
            throw $e;
        }

    }

    protected $notify_type;

    public function setNotify_type($type){
        $this->notify->setNotify_type($type);
    }

    /**
     * 通过环境类调用支付异步通知
     *
     * @param PayNotifyInterface $notify
     * @return array
     * @throws PayException
     * @author helei
     */
    public function notify(PayNotifyInterface $notify)
    {
        if (! $this->notify instanceof NotifyStrategy) {
            throw new PayException('请检查初始化是否正确');
        }

        return $this->notify->handle($notify);
    }
}