<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/8/22
 * Time: 14:18
 */

namespace vandles\lib;

class Enum {

    public static $TAKE_TYPE_SELF = 0;
    const TAKE_TYPE_DELIVER = 1;

    const ORDER_STATUS_UNPAY      = 0;
    const ORDER_STATUS_DELIVERING = 1;
    const ORDER_STATUS_FINISHED   = 2;
    const ORDER_STATUS_REFUND     = 9;

    const REFUND_APPLY_STATUS_UNREFUND       = 0;
    const REFUND_APPLY_STATUS_REFUNDING      = 1;
    const REFUND_APPLY_STATUS_REFUNDFINISHED = 2;
    const REFUND_APPLY_STATUS_REFUNDFAIL     = 3;

}