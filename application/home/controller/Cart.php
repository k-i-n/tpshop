<?php

namespace app\home\controller;

use think\Request;

class Cart extends Base
{
    public function addcart(Request $request){
        $data=$request->param();
        //参数检测 略
        //处理数据 添加数据到购物车
        \app\home\model\Cart::addCart($data['goods_id'], $data['number'], $data['goods_attr_ids']);
        //查询商品相关信息
        $goods=\app\home\model\Goods::find($data['goods_id']);
        //查询属性名称属性值信息
        $attrs = \app\home\model\GoodsAttr::getAttrsByIds($data['goods_attr_ids']);
        return view('addcart', ['goods' => $goods, 'number'=>$data['number'], 'attrs'=>$attrs]);
    }
}
