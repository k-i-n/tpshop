<?php

namespace app\home\controller;

use app\admin\model\Category;
use think\Controller;
use think\Request;

class Base extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        //查询商品分类信息
        $category=Category::select();
        //将数据集转化为标准二维数组
        $category=(new \think\Collection($category))->toArray();
        //实现分类树结构
        $category=get_cate_tree($category);
        $this->assign('category',$category);
    }
}
