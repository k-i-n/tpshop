<?php

namespace app\home\model;

use think\Model;

class GoodsAttr extends Model
{
    //根据goods_attr_ids 查询属性名称属性值
    public static function getAttrsByIds($goods_attr_ids)
    {
        //查询属性值表tpshop_goods_attr 连表 tpshop_attribute
        //SELECT t1.*, t2.attr_name FROM `tpshop_goods_attr` t1 left join tpshop_attribute t2 on t1.attr_id = t2.id where t1.id in (18, 20);
        return self::alias('t1')
            ->field('t1.*, t2.attr_name')
            ->join('tpshop_attribute t2', 't1.attr_id=t2.id', 'left')
            ->where('t1.id', 'in', $goods_attr_ids)
            ->select();
    }

}
