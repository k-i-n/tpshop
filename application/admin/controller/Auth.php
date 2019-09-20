<?php

namespace app\admin\controller;

use think\Request;
use app\admin\model\Auth as Au;

class Auth extends Base
{
   public function index(){
       //查询权限表数据
       $list=Au::select();
       //使用递归函数重新排序
       $list=getTree($list);
       return view('index',['list'=>$list]);
   }

   public function create(){
       //查询所有的一级权限，用于下拉列表展示
       $top_auth=Au::where('pid',0)->select();
       return view('create',['top_auth'=>$top_auth]);
   }

   public function save(Request $request){
       //接收数据
       $data=$request->param();
       //检测参数格式  表单验证 略

       //添加数据到权限表
       Au::create($data,true);
       //页面跳转
       $this->success('操作成功','index');
   }
}
