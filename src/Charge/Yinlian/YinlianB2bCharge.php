<?php
/**
 * @author: helei
 * @createTime: 2016-07-14 18:19
 * @description: 支付宝 手机网站支付 接口
 * @link      https://github.com/helei112g/payment/tree/paymentv2
 * @link      https://helei112g.github.io/
 */

namespace thinkweb\zapay\Charge\Yinlian;

use thinkweb\zapay\Common\Yinlian\Data\Charge\B2bChargeData;
use thinkweb\zapay\Common\Yinlian\YinlianBaseStrategy;


class YinlianB2bCharge extends YinlianBaseStrategy
{

    /**
     * 获取支付对应的数据完成类
     * @return string
     * @author helei
     */
    protected function getBuildDataClass()
    {
        // 以下两种方式任选一种
        return B2bChargeData::class;
    }
}