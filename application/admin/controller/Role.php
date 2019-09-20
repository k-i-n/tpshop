<?php

namespace app\admin\controller;

use think\Request;
use app\admin\model\Role as Rol;
use app\admin\model\Auth;

class Role extends Base
{
    public function index(){
        //查询角色列表数据
        $list=Rol::select();
        return view('index',['list'=>$list]);
    }

    //为角色分配权限页面展示
    public function setauth($id)
    {
        //查询角色信息
        $role=Rol::find($id);
        //查询所有的顶级权限
        $top_auth=Auth::where('pid',0)->select();
        //查询所有的二级权限
        $second_auth=Auth::where('pid','>',0)->select();
        return view('setAuth',['role'=>$role,'top_auth'=>$top_auth,'second_auth'=>$second_auth]);
    }

    //为角色分配权限  表单提交
    public function saveauth()
    {
        //分别接受角色id和权限id
        $role_id=request()->param('role_id');
        //接受单个参数，参数值如果是数组，需要在名称后加/a变量修饰符
        $auth_id=request()->param('id/a');
        //将权限id从数组转化为字符串
        $role_auth_ids=implode(',',$auth_id);
        //将权限id修改到角色表
        Rol::update(['role_auth_ids'=>$role_auth_ids],['id'=>$role_id]);
        //页面跳转
        $this->success('操作成功','index');
    }
}
