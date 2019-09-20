<?php

namespace app\admin\controller;

use think\Request;

class Attribute extends Base
{
    //新增属性页面
    public function create(){
        //查询所有商品类型，用于下拉列表展示
        $type=\app\admin\model\Type::select();
        return view('create',['type'=>$type]);
    }
    //新增属性逻辑
    public function save(Request $request){
        //接收参数
        $data=$request->param();
        //参数检测 表单验证 略

        //数据入库
        \app\admin\model\Attribute::create($data,true);
        //页面跳转
        $this->success('操作成功','index');
    }

    //属性列表
    public function index(){
        $list=\app\admin\model\Attribute::alias('t1')
        ->join('tpshop_type t2','t1.type_id=t2.id','left')
        ->field('t1.*,t2.type_name')
        ->select();
        return view('index',['list'=>$list]);
    }

    public function getattr($type_id)
    {
        $data=\app\admin\model\Attribute::where('type_id',$type_id)->select();
        foreach ($data as &$v){
            $v=$v->getData();
            //将可选值分割为数组，方便页面上遍历
            $v['attr_values']=explode(',',$v['attr_values']);
        }
        unset($v);
        //返回数据
        $res=[
            'code'=>10000,
            'msg'=>'success',
            'data'=>$data
        ];
        return json($res);
    }
}
