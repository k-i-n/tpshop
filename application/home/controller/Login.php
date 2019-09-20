<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Login extends Controller
{
    /**
     * 登录
     *
     * @return \think\Response
     */
    public function login()
    {
        //临时关闭模板布局
        $this->view->engine->layout(false);
        return view();
    }

    //注册页面
    public function register()
    {
        //临时关闭模板布局
        $this->view->engine->layout(false);
        return view();
    }



    //注册发送短信验证码
    public function sendcode()
    {
        //接收手机号参数
        $phone = request()->param('phone');

        //发送短信

        $code = mt_rand(1000, 9999);
        $content = "【创信】你的验证码是：{$code}，3分钟内有效！";
        //调用发送短信的函数
        //$result = sendmsg($phone, $content);
        $result = true; //开发测试阶段 不用真正发短信
        if($result === true){
            //保存发送的验证码(同时记录验证码和手机号的对应关系)
            //可以放session  也可以放缓存cache
            //session('register_code_' . $phone, $code);
            cache('register_code_' . $phone, $code, 300);
            cache('register_time_'.$phone, time());
            //发送成功
            $res = [
                'code' => 10000,
                'msg' => '发送成功',
                'data' => $code //开发测试阶段 不用真正发短信
            ];
            return json($res);
        }else{
            //发送失败
            $res = [
                'code' => 10002,
                'msg' => '发送失败'
            ];
            return json($res);
        }
    }

    //手机号注册 表单提交
    public function phone()
    {
        //接收参数
        $data = request()->param();
        //参数验证
        $rule = [
            'phone|手机号码' => 'require|regex:1[3-9]\d{9}|unique:user',
            'code|验证码' => 'require|length:4|integer',
            'password|密码' => 'require|confirm:repassword'
        ];
        //使用默认的错误提示  $msg不写了
        $validate = new \think\Validate($rule);
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        //校验短信验证码  $data['code']
        $code = cache('register_code_'.$data['phone']);
        if($code != $data['code']){
            $this->error('验证码错误');
        }
        //验证码 使用成功一次 失效
        cache('register_code_'.$data['phone'], null);
        //添加数据到用户表
        $data['username'] = $data['phone'];
        $data['password'] = encrypt_password($data['password']);
        $data['is_check'] = 1; //手机号注册 不需要激活
        \app\home\model\User::create($data, true);
        //页面跳转
        $this->success('注册成功', 'login');
    }

    //登录 表单提交
    public function dologin()
    {
        //接收数据
        $data = request()->param();
        //参数检测  略
        //查询用户表
        $password = encrypt_password($data['password']);
        //① 先根据用户名查询，再比对密码
        /*$user = \app\home\model\User::where('email', $data['username'])->whereOr('phone', $data['username'])->find();
        if($user && $user['password'] == $password && $user['is_check'] == 1){
            //用户存在 登录成功
            //设置登录标识
            session('user_info', $user->toArray());
            $this->success('登录成功', 'home/index/index');
        }else{
            //用户名或密码错误
            $this->error('用户名或密码错误');
        }*/
        //② 用户名密码一起查询用户表
        //$user = \app\home\model\User::where('email', $data['username'])->whereOr('phone', $data['username'])->where('password', $password)->find(); //这么写会有问题
        $user = \app\home\model\User::where(function($query)use($data){
            $query->where('email', $data['username'])->whereOr('phone', $data['username']);
        })->where('password', $password)->where('is_check', 1)->find();
        if($user){
            //用户存在 登录成功
            //设置登录标识
            session('user_info', $user->toArray());
            $this->success('登录成功', 'home/index/index');
        }else{
            //用户名或密码错误
            $this->error('用户名或密码错误');
        }
    }

    //退出
    public function logout()
    {
        //清空session
        session(null);
        //跳转到登录页
        $this->redirect('home/login/login');
    }


}
