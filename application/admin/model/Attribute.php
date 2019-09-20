<?php

namespace app\admin\model;

use think\Model;

class Attribute extends Model
{
    //设置获取器，对字段进行自动转化 attr_type   attr_input_type
    //属性类型 attr_type字段
    public function getAttrTypeAttr($value)
    {
        //0 唯一属性；1 单选属性
        return $value ? "单选属性" : "唯一属性";
    }
    //录入方式 attr_input_type字段
    public function getAttrInputTypeAttr($value)
    {
        //0 input输入框；1 下拉列表 ；2 多选框
        $attr_input_type = ['input输入框', '下拉列表', '多选框'];
        return $attr_input_type[$value];
    }
}
