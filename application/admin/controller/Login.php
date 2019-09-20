<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Validate;
use \app\admin\model\Manager;

class login extends Controller
{
    //登录页面
    public function login(Request $request){
        if(request()->isGet()){
            $this->view->engine->layout(false);
            return view();
        }

        $data=$request->param();

        //参数检测
        $rule=[
            'username'=>'require',
            'password'=>'require|length:6,16'
        ];

        $msg=[
            'username.require'=>'用户名不能为空',
            'password.require'=>'密码不能为空',
            'password.length'=>'密码长度需在6到16位之间'
        ];

        $validate=new Validate($rule,$msg);

        if(!$validate->check($data)){
            $error=$validate->getError();
            $this->error($error);
        }

        //验证码校验
        if(!captcha_check($data['code'])){
            $this->error('验证码错误');
        }

        //根据用户名和密码一起查询
        $password=encrypt_password($data['password']);
        $user=Manager::where('username',$data['username'])->where('password',$password)->find();
        if($user){
            session('manager_info',$user->toArray());
            $this->success('登录成功','admin/index/index');
        }else{
            $this->error('用户名或密码错误');
        }

    }

    //登出页面
    public function logout(){
        //清空所有session
        session(null);
        //跳转到登录页面
        $this->redirect('login');
    }

}
