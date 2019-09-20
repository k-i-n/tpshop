<?php

namespace app\admin\controller;

use think\Request;
use app\admin\model\Category as Ca;

class Category extends Base
{
    /**
     * 获取子分类
     * 根据分类id查询子分类
     * @return \think\Response
     */
    public function getSubCate($id)
    {
        //参数判断
        if(empty($id)){
            $res=[
                'code'=>10001,
                'msg'=>'参数错误'
            ];
            return json($res);
        }
        //根据id查询子分类
        $list=Ca::where('pid',$id)->select();
        //返回数据
        $res=[
            'code'=>10000,
            'msg'=>'success',
            'data'=>$list
        ];
        return json($res);
    }
}
