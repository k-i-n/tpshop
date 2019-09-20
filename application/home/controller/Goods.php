<?php

namespace app\home\controller;

use app\home\model\Attribute;
use app\home\model\Category;
use app\home\model\Goodspics;
use think\Controller;
use think\Request;

class Goods extends Base
{
    //商品展示
    public function index($id){
        //查询当前分类的名称
        $cate=Category::find($id);
        //查询分类表中当前分类下的商品
        $list=\app\home\model\Goods::where('cate_id',$id)->paginate(2);
        return view('index',['cate'=>$cate,'list'=>$list]);
    }

    //商品详情
    public function detail($id){
        //查询商品基本信息
        $goods=\app\home\model\Goods::find($id);
        //查询商品相册图片
        $goodspics=Goodspics::where('goods_id',$id)->select();
        //查询当前商品所属的类型下，所有的属性名称
        $attribute=Attribute::where('type_id',$goods->type_id)->select();
        //查询商品的所有属性值
        $goodsattr = \app\home\model\GoodsAttr::where('goods_id', $id)->select();
        //将所有属性值 按照属性id 进行分组
        $new_goodsattr = [];
        foreach($goodsattr as $v){
            $new_goodsattr[$v['attr_id']][] = $v->toArray();
        }
        return view('detail',['goods'=>$goods,'goodspics'=>$goodspics,'attribute'=>$attribute,'new_goodsattr'=>$new_goodsattr]);
    }
}
