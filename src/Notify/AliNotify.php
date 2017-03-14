<?php
/**
 * @author: helei
 * @createTime: 2016-07-20 16:21
 * @description: 支付宝回调通知
 *
 * @link      https://github.com/helei112g/payment/tree/paymentv2
 * @link      https://helei112g.github.io/
 */

namespace thinkweb\zapay\Notify;


use thinkweb\zapay\Common\AliConfig;
use thinkweb\zapay\Common\PayException;
use thinkweb\zapay\Config;
use thinkweb\zapay\Utils\ArrayUtil;
use thinkweb\zapay\Utils\Curl;
use thinkweb\zapay\Utils\RsaEncrypt;

class AliNotify extends NotifyStrategy
{
    /**
     * AliNotify constructor.
     * @param array $config
     * @throws PayException
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        try {
            $this->config = new AliConfig($config);
        } catch (PayException $e) {
            throw $e;
        }
    }

    /**
     * 获取移除通知的数据  并进行简单处理（如：格式化为数组）
     *
     * 如果获取数据失败，返回false
     *
     * @return array|boolean
     * @author helei
     */
    protected function getNotifyData()
    {
        $data = empty($_POST) ? $_GET : $_POST;
        if (empty($data) || ! is_array($data)) {
            return false;
        }

        return $data;
    }

    /**
     * 检查异步通知的数据是否合法
     *
     * 如果检查失败，返回false
     *
     * @param array $data  由 $this->getNotifyData() 返回的数据
     * @return boolean
     * @author helei
     */
    protected function checkNotifyData(array $data)
    {
        // 检查签名
        $flag = $this->verifySign($data);

        return $flag;
    }

    /**
     * 向客户端返回必要的数据
     * @param array $data 回调机构返回的回调通知数据
     * @return array|false
     * @author helei
     */
    protected function getRetData(array $data)
    {
        $notifyType = $this->notify_type;
        switch ($notifyType) {
            case Config::TRADE_NOTIFY:
                $retData = $this->getTradeData($data);
                break;
            case Config::REFUND_NOTIFY:
                $retData = $this->getRefundData($data);
                break;
            case Config::TRANSFER_NOTIFY:
                $retData = $this->getTransferData($data);
                break;
            default :
                $retData = false;
        }

        return $retData;
    }

    /**
     * 处理 通知类型是 trade_status_sync 的数据，其结果作为返回值，返回给客户端
     * @param array $data
     *
     *      * 以下数据为支付宝返回的数据 trade_status_sync 返回的数据
     * ```php
     * $data['discount']  折扣   支付宝系统会把discount的值加到交易金额上，如果需要折扣，本参数为负数。
     * $data['payment_type']  支付类型  只支持取值为1（商品购买）
     * $data['subject']  商品名称
     * $data['trade_no']   支付宝交易号  该交易在支付宝系统中的交易流水号。最长64位。
     * $data['buyer_email']  买家支付宝账号   可以是Email或手机号码
     * $data['gmt_create']  交易创建时间  格式为yyyy-MM-dd HH:mm:ss
     * $data['notify_type']  通知类型
     * $data['quantity']   购买数量
     * $data['out_trade_no']  商户网站唯一订单号
     * $data['seller_id']  卖家支付宝账户号  以2088开头的纯16位数字
     * $data['notify_time']   通知时间  格式为yyyy-MM-dd HH:mm:ss
     * $data['body']  商品描述
     *
     * $data['trade_status']   交易状态  https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.Y2aZ5i&treeId=62&articleId=104743&docType=1#s7
     *
     * $data['is_total_fee_adjust']  是否调整总价  该交易是否调整过价格。
     * $data['total_fee']  交易金额  该笔订单的总金额。
     * $data['gmt_payment']  交易付款时间  格式为yyyy-MM-dd HH:mm:ss
     * $data['seller_email']  卖家支付宝账号  可以是email和手机号码。
     * $data['gmt_close']   交易关闭时间   格式为yyyy-MM-dd HH:mm:ss
     * $data['price']  商品单价
     * $data['buyer_id']   买家支付宝账户号   以2088开头的纯16位数字
     * $data['notify_id']   通知校验ID
     * $data['use_coupon']  是否使用红包买家
     * $data['sign_type']   签名方式
     * $data['sign']    签名
     * $data['extra_common_param']   公用回传参数
     * ```
     *
     * @return array|bool
     * @author helei
     */
    protected function getTradeData(array $data)
    {
        if(isset($data['method']) && ($data['method'] == 'alipay.trade.wap.pay.return')){
            $status = Config::TRADE_STATUS_FAILD;
        } else {
            $status = $this->getTradeStatus($data['trade_status']);
            if (!isset($data['out_trade_no']) || !$data['out_trade_no']) {
                //订单都找不到，直接错误好了
                return false;
            }
        }


        $retData = [
            'channel'   => Config::ALI,
            'order_no'   => $data['out_trade_no'],
            'trade_state'   => $status,
            'notify_type'   => Config::TRADE_NOTIFY,// 通知类型为 支付行为
            'ret_data' => $data,
        ];
        return $retData;
    }

    /**
     * 处理退款的返回数据，返回给客户端
     * @param array $data
     *
     * ```php
     *  $data['notify_time']   通知的发送时间。格式为yyyy-MM-dd HH:mm:ss
     *  $data['notify_type']   通知类型， batch_refund_notify
     *  $data['notify_id']   通知校验ID
     *  $data['sign_type']   DSA、RSA、MD5三个值可选，必须大写
     *  $data['sign']   签名
     *  $data['batch_no']   原请求退款批次号。
     *  $data['success_num']   退款成功总数
     *  $data['result_details']   退款结果明细  为了简洁不返回客户端
     * ```
     * @return array
     * @author helei
     */
    protected function getRefundData(array $data)
    {
        $retData = [
            'channel'   => Config::ALI,
            'refund_no'   => $data['batch_no'],
            'success_num'   => $data['success_num'],
            'notify_time'   => $data['notify_time'],
            'notify_type'   => Config::REFUND_NOTIFY,// 通知类型为 退款行为
        ];
        if ($this->config->version) {
            // 新版本
            $retData['amount'] = $data['total_amount'];
        } else {
            // 老版本
            $retData['amount'] = $data['total_fee'];
        }

        return $retData;
    }

    /**
     * 处理批量付款的通知类型
     * @param array $data
     *
     * ```php
     *  $data['notify_time']   通知的发送时间。格式为yyyy-MM-dd HH:mm:ss
     *  $data['notify_type']   通知类型， batch_refund_notify
     *  $data['notify_id']   通知校验ID
     *  $data['sign_type']   DSA、RSA、MD5三个值可选，必须大写
     *  $data['sign']   签名
     *  $data['batch_no']   转账批次号。
     *  $data['pay_user_id']   付款账号ID   以2088开头的16位纯数字组成。
     *  $data['pay_user_name']   付款账号姓名
     *  $data['pay_account_no']   付款账号。
     *  $data['success_details']   批量付款中成功付款的信息。
     *  $data['fail_details']   批量付款中未成功付款的信息。
     * ```
     *
     * @return array
     * @author helei
     */
    protected function getTransferData(array $data)
    {
        // 转账成功的信息  单条数据格式：流水号^收款方账号^收款账号姓名^付款金额^成功标识(S)^成功原因(null)^支付宝内部流水号^完成时间。
        $successData = explode('|', $data['success_details']);
        // 转账失败的信息  单条记录数据格式：流水号^收款方账号^收款账号姓名^付款金额^失败标识(F)^失败原因^支付宝内部流水号^完成时间。
        $failData = explode('|', $data['fail_details']);

        $retData = [
            'channel'   => Config::ALI,
            'trans_no'   => $data['batch_no'],
            'pay_name'   => $data['pay_user_name'],
            'pay_account'   => $data['pay_account_no'],
            'notify_time'   => $data['notify_time'],
            'notify_type'   => Config::REFUND_NOTIFY,// 通知类型为 退款行为
            'success'   => $successData,
            'fail'  => $failData,
        ];

        return $retData;
    }


    /**
     * 支付宝，成功返回 ‘success’   失败，返回 ‘fail’
     * @param boolean $flag 每次返回的bool值
     * @param string $msg 错误原因  后期考虑记录日志
     * @return string
     * @author helei
     */
    protected function replyNotify($flag, $msg = '')
    {
        if ($flag) {
            return 'success';
        } else {
            throw new \Exception($msg);
            return 'fail';
        }
    }

    /**
     * 返回统一的交易状态
     * @param $status
     * @return string
     * @author helei
     */
    protected function getTradeStatus($status){
        if (in_array($status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return Config::TRADE_STATUS_SUCC;
        } else {
            return Config::TRADE_STATUS_FAILD;
        }
    }

    /**
     * 检查支付宝数据 签名是否被篡改
     * @param array $data
     * @return boolean
     * @author helei
     */
    protected function verifySign(array $data)
    {
        $signType = strtoupper($data['sign_type']);
        $sign = $data['sign'];

        // 1. 剔除sign与sign_type参数
        $values = ArrayUtil::removeKeys($data, ['sign', 'sign_type']);
        //  2. 移除数组中的空值
        $values = ArrayUtil::paraFilter($values);
        // 3. 对待签名参数数组排序
        $values = ArrayUtil::arraySort($values);
        // 4. 将排序后的参数与其对应值，组合成“参数=参数值”的格式,用&字符连接起来
        $preStr = ArrayUtil::createLinkstring($values);

        if ($signType === 'MD5') {// 使用md5方式
            return md5($preStr . $this->config->md5Key) === $sign;
        } elseif ($signType === 'RSA') {// 使用rsa方式
            $publicKeyContent = file_get_contents($this->config->rsaAliPubPath);
            $rsa = new RsaEncrypt($publicKeyContent);
            return $rsa->rsaVerify($preStr, $sign);
        } else {
            return false;
        }
    }
}