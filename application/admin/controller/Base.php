<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    public function __construct(Request $request){
        //先实现父类的构造方法
        parent::__construct($request);

        if(!session('?manager_info')){
            $this->redirect('admin/login/login');
        }
        //调用getnav获取菜单权限
        $this->getnav();
    }

    //获取菜单权限
    public function getnav()
    {
        //从session中获取角色id
        $role_id=session('manager_info.role_id');
        //判断角色id == 1 超级管理员
        if(1 == $role_id){
            //超级管理员  直接查询权限表
            //查询顶级菜单权限
            $top_nav=\app\admin\model\Auth::where([
                'pid'=>0,
                'is_nav'=>1
            ])->select();
            //查询二级权限
            $second_nav=\app\admin\model\Auth::where([
                'pid'=>['>',0],
                'is_nav'=>1
            ])->select();
        }else{
            //其他管理员，先根据角色id查询角色表，取出role_auth_ids,再查权限表
            $role=\app\admin\model\Role::find($role_id);
            $role_auth_ids=$role['role_auth_ids'];
            //查询拥有的顶级菜单权限
            $top_nav=\app\admin\model\Auth::where([
                'pid'=>0,
                'is_nav'=>1,
                'id'=>['in',$role_auth_ids]
            ])->select();
            //查询拥有的二级权限
            $second_nav=\app\admin\model\Auth::where([
                'pid'=>['>',0],
                'is_nav'=>1,
                'id'=>['in',$role_auth_ids]
            ])->select();


        }
        //模板变量赋值
        $this->assign('top_nav',$top_nav);
        $this->assign('second_nav',$second_nav);
    }
}
