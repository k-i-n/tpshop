<?php

namespace app\admin\model;

use think\Model;

class Auth extends Model
{
    //获取器  查询数据时转化字段值内容
    public function getIsNavAttr($value)
    {
        //is_nav字段 1是，0否
        return $value ? '是' : '否';
    }
}
