<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Test extends Controller
{
    //编写一个接口  http://www.tpshop.com/home/login/testapi
    public function testapi(){
        //接收参数
        $data=request()->param();
        //参数验证（略）
        //逻辑处理（略）

        //返回数据
        $res=[
            'code'  =>  10000,
            'msg'   =>  'success',
            'data'  =>  $data
        ];
        return json($res);
    }

    //模拟接口调用
    public function testrequest(){
        //发送请求调用接口
        //请求地址
        $url="http://www.tpshop.com/home/test/testapi";
        //发送post请求
        //请求参数
        $param=[
            'id' => 100,
            'page' =>10
        ];

        //调用curl_request发送请求
        $res=curl_request($url,true,$param);
        var_dump($res);die;
    }

    //调用京东短信接口
    public function testjd(){
        $res=sendmsg('18463287661','【创信】你的验证码是：5873，3分钟内有效！');
        var_dump($res);die;

    }
}
