<?php
/**
 * @author: helei
 * @createTime: 2016-07-27 15:28
 * @description: 支付宝批量付款接口
 */

namespace thinkweb\zapay\Trans;


use thinkweb\zapay\Common\Ali\AliBaseStrategy;
use thinkweb\zapay\Common\Ali\Data\TransData;

class AliTransfer extends AliBaseStrategy
{

    protected function getBuildDataClass()
    {
        return TransData::class;
    }
}