<?php

namespace app\home\model;

use think\Model;

class Cart extends Model
{
    //加入购物车
    public static function addCart($goods_id, $number, $goods_attr_ids)
    {
        // 已登录：添加到数据表； 未登录 添加到cookie
        if(session('?user_info')){
            //已登录 添加到数据表；
            $user_id = session('user_info.id');
            //查询是否已经添加过相同的记录（用户id、商品id、属性值ids完全相同）
            $where = [
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'goods_attr_ids' => $goods_attr_ids,
            ];
            $data = self::where($where)->find();
            if($data){
                //已经存在相同记录， 则累加购买数量
                $data->number += $number;
                $data->save();
            }else{
                //不存在相同记录， 则添加新记录
                $where['number'] = $number;
                self::create($where);
            }
        }else{
            //未登录 添加到cookie
            //获取cookie中已有的数据  得到数组结构
            $data = cookie('cart') ? unserialize(cookie('cart')) : [];
            //拼接要操作的数据 下标 $key
            $key = $goods_id . '-' . $goods_attr_ids;
            //查询是否已经添加过相同记录（商品id和属性值ids）
            if(isset($data[$key])){
                //存在相同记录，累加数量
                $data[$key] += $number;
            }else{
                //不存在相同记录，添加一个键值对
                $data[$key] = $number;
            }
            //重新保存到cookie
            cookie('cart', serialize($data), 86400*7);
        }
        return true;
    }

    //获取购物车所有数据  返回标准的二维数组
    public static function getAllCart()
    {
        //已登录：查询数据表；未登录：查询cookie
        if(session('?user_info')){
            $user_id = session('user_info.id');
            //查询当前用户的所有记录
            $data = self::where('user_id', $user_id)->select();
            //转化为标准的二维数组格式
            foreach($data as &$v){
                $v = $v->toArray();
            }
            unset($v);
            //$data = (new \think\Collection($data))->toArray();
        }else{
            //未登录
            $cart = cookie('cart') ? unserialize(cookie('cart')) : [];
            //一维数组 转化为标准的二维数组格式
            $data = [];
            foreach($cart as $k=>$v){
                // $k 商品id-商品属性ids  $v 数量
                $temp = explode('-', $k);
                $data[] = [
                    'id' => '', //额外添加一个id字段，后续会用到
                    'goods_id' => $temp[0],
                    'goods_attr_ids' => $temp[1],
                    'number' => $v
                ];
            }
        }
        return $data;
    }

    //登录时 迁移cookie中购物车数据到数据表
    public static function cookieTodb()
    {
        //从cookie取出购物车数据
        $data = cookie('cart') ? unserialize(cookie('cart')) : [];
        //遍历，每一条数据，添加到数据表
        foreach($data as $k=>$v){
            // $k  商品id-商品属性值ids  $v 数量
            $temp = explode('-', $k);
            $goods_id = $temp[0];
            $goods_attr_ids = $temp[1];
            $number = $v;
            //由于当前是已登录，可以直接调用addCart方法
            self::addCart($goods_id, $number, $goods_attr_ids);
        }
        //cookie中购物车数据删除
        cookie('cart', null);
        return true;
    }

    //修改购买数量
    public static function changeNum($goods_id, $goods_attr_ids, $number)
    {
        //已登录：修改数据表； 未登录 修改cookie
        if(session('?user_info')){
            //获取用户id
            $user_id = session('user_info.id');
            //修改条件
            $where = [
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'goods_attr_ids' => $goods_attr_ids
            ];
            //直接修改记录
            self::where($where)->update(['number' => $number]);
        }else{
            //未登录
            //先从cookie取数据
            $data = cookie('cart') ? unserialize(cookie('cart')) : [];
            //拼接要操作的数据 下标
            $key = $goods_id . '-' . $goods_attr_ids;
            //修改键值对
            $data[$key] = $number;
            //重新保存到cookie
            cookie('cart', serialize($data), 86400*7);
        }
    }

    //删除购物记录
    public static function delCart($goods_id, $goods_attr_ids)
    {
        //已登录 从数据表删除 ； 未登录 从cookie删除
        if(session('?user_info')){
            //获取用户id
            $user_id = session('user_info.id');
            //删除条件
            $where = [
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'goods_attr_ids' => $goods_attr_ids
            ];
            //直接删除
            self::where($where)->delete();
        }else{
            //从cookie删除
            $data = cookie('cart') ? unserialize(cookie('cart')) : [];
            //拼接下标
            $key = $goods_id . '-' . $goods_attr_ids;
            //从数组中删除
            unset($data[$key]);
            //重新保存到cookie
            cookie('cart', serialize($data), 86400*7);
        }
    }
}
