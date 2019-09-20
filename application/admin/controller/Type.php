<?php

namespace app\admin\controller;

use think\Request;

class Type extends Base
{
    //类型新增页面
    public function create(){
        return view();
    }

    //类型新增逻辑
    public function save(Request $request){
        //接收数据
        $data=$request->param();
        //检测参数
        if(empty($data['type_name'])){
            $this->error("类型名称不能为空");
        }
        //添加数据到数据表
        \app\admin\model\Type::create($data,true);

        //页面跳转
        $this->success('操作成功','index');
    }

    //类型列表
    public function index(){
        $list=\app\admin\model\Type::select();
        return view('index',['list'=>$list]);
    }
}
