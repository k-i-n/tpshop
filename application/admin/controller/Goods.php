<?php

namespace app\admin\controller;

use app\admin\model\Category;
use app\admin\model\Goodspics;
use think\Request;
use \app\admin\model\Goods as G;
use think\Validate;

class Goods extends Base
{
    //后台商品列表展示
    public function index(){
        $lists=G::order('id asc')->paginate(10);
        return view('index',['list'=>$lists]);
    }

    //后台商品添加页面
    public function create(){
        //查询所有一级商品分类
        $cate_one=Category::where('pid',0)->select();
        //查询商品类型数据，用于商品类型栏下拉列表展示
        $type=\app\admin\model\Type::select();
        return view('create',['cate_one'=>$cate_one,'type'=>$type]);
    }

    //后台商品添加逻辑
    public function save(Request $request){
        $data=$request->param();

        //对富文本编辑器字段，防范xss攻击
        $data['goods_introduce']=$request->param('goods_introduce','','remove_xss');
        //$data['goods_introduce']=remove_xss($POST['goods_introduce']);
        //参数检测，表单验证
        //1.定义验证规则数组
        $rule=[
            'goods_name'=>'require',
            'goods_price'=>'require|float|gt:0',
            'goods_number'=>'require|integer|gt:0',
            'cate_id'=>'require|integer|gt:0'
        ];
        //2.定义错误提示信息数组
        $msg=[
            'goods_name.require'=>'商品名称不能为空',
            'goods_price.require'=>'商品价格不能为空',
            'goods_price.float'=>'商品价格格式不正确',
            'goods_price.gt'=>'商品价格必须大于0',
            'goods_number.require'=>'商品数量不能为空',
            'goods_number.integer'=>'商品数量必须为整数',
            'goods_number.gt'=>'商品数量必须大于0',
            'cate_id.require'=>'商品分类必须选择'
        ];
        //3.实例化验证器类
        $validate=new Validate($rule,$msg);
        //4.调用check()方法进行验证，如果有错误则携带错误信息进行页面跳转
        if(!$validate->check($data)){
            $error=$validate->getError();
            $this->error($error);
        }

        //商品logo图片上传
        $data['goods_logo']=$this->upload_logo();

        //数据入库
        $goods=G::create($data,true);

        //商品相册图片上传
        $this->upload_pics($goods->id);
        //添加商品属性值
        $attr_value = $data['attr_value'];
        //结果数组
        $goods_attr_data = [];
        foreach($attr_value as $k=>$v){
            // $k 是 属性id； $v 是 包含多个属性值的数组
            foreach($v as $value){
                // $value就是一个属性值
                //组装一条数据 一个一维数组
                $row = [
                    'goods_id' => $goods->id,
                    'attr_id' => $k,
                    'attr_value' => $value
                ];
                $goods_attr_data[] = $row;
            }
        }
        //批量添加数据到tpshop_goods_attr表
        $goods_attr = new \app\admin\model\GoodsAttr();
        $goods_attr->saveAll($goods_attr_data);

        //成功跳转
        $this->success('添加成功','index');
    }

    //后台商品修改页面
    public function edit($id){
        //查询商品原始数据
        $info=G::find($id);

        //查询所有的一级分类
        $cate_one_all=Category::where('pid',0)->select();
        //查询所属的三级分类信息（其pid值是对应二级分类的id）
        $cate_three=Category::find($info['cate_id']);
        //查询所属的二级分类信息（其pid值是对应一级分类的id）
        $cate_two=Category::find($cate_three['pid']);
        //查询商品所属一级分类下的所有二级分类
        $cate_two_all=Category::where('pid',$cate_two['pid'])->select();
        //查询商品所属二级分类下的所有三级分类
        $cate_three_all=Category::where('pid',$cate_two['id'])->select();
        //查询相册图片
        $goodspics=Goodspics::where('goods_id',$id)->select();
        return view('edit',[
            'info'=>$info,
            'cate_one_all'=>$cate_one_all,
            'cate_two'=>$cate_two,
            'cate_two_all'=>$cate_two_all,
            'cate_three_all'=>$cate_three_all,
            'goodspics'=>$goodspics
            ]);
    }

    //后台商品修改逻辑
    public function update(Request $request,$id){
        //接收数据
        $data=$request->param();

        //对富文本编辑器字段，防范xss攻击
        $data['goods_introduce']=$request->param('goods_introduce','','remove_xss');

        //参数检测，表单验证
        //1.定义验证规则数组
        $rule=[
            'goods_name'=>'require',
            'goods_price'=>'require|float|gt:0',
            'goods_number'=>'require|integer|gt:0',
            'cate_id'=>'require|integer|gt:0'
        ];
        //2.定义错误提示信息数组
        $msg=[
            'goods_name.require'=>'商品名称不能为空',
            'goods_price.require'=>'商品价格不能为空',
            'goods_price.float'=>'商品价格格式不正确',
            'goods_price.gt'=>'商品价格必须大于0',
            'goods_number.require'=>'商品数量不能为空',
            'goods_number.integer'=>'商品数量必须为整数',
            'goods_number.gt'=>'商品数量必须大于0',
            'cate_id.require'=>'商品分类必须选择'
        ];
        //3.实例化验证器类
        $validate=new Validate($rule,$msg);
        //4.调用check()方法进行验证，如果有错误则携带错误信息进行页面跳转
        if(!$validate->check($data)){
            $error=$validate->getError();
            $this->error($error);
        }
        //商品logo修改
        $file=$request->file('logo');
        if($file){
            $data['goods_logo']=$this->upload_logo();
        }

        //更新入库
        G::update($data,['id'=>$id],true);

        //继续上传相册图片
        $this->upload_pics($id);
        //页面跳转
        $this->success('更新成功','index');
    }

    //后台商品删除
    public function delete($id){
        //id参数检测
        if(!preg_match('/^\d+$/', $id) || $id == 0){
            $this->error('参数错误');
        }

        //执行删除操作
        G::destroy($id);

        //页面跳转
        $this->success('操作成功','index');
    }

    //商品logo图片上传
    private function upload_logo()
    {
        //获取到上传的文件
        $file=request()->file('logo');
        if(empty($file)){
            $this->error('没有上传logo图片');
        }
        //移动文件到指定目录下， public/uploads目录下
        $info=$file->validate(['size'=>5 * 1024 * 1024,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH.'public'.DS.'uploads');
        //判断结果处理
        if($info){
            //上传成功，获取文件访问路径，并返回
            $goods_logo=DS.'uploads'.DS.$info->getSaveName();
            //生成缩略图
            //调用open方法打开图片
            $image=\think\Image::open('.'.$goods_logo);
            //调用thumb方法生成缩略图,save保存缩略图
            $image->thumb(200,200)->save('.'.$goods_logo);
            return $goods_logo;
        }else{
            //上传失败，调用$file的getError方法，获取到具体的错误信息
            $error=$file->getError();
            $this->error($error);
        }
    }

    //商品相册图片上传
    private function upload_pics($goods_id)
    {
        //获取到上传的文件  数组
        $files=request()->file('goods_pics');
        //遍历数组，对每一个文件进行处理
        $goodspics_data=[];
        foreach($files as $file){
            //将每一个文件移动到指定目录（public/uploads目录）
            $info=$file->validate(['size'=>5*1024*1024,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH.'public'.DS.'uploads');
            if($info){
                //上传成功，拼接图片访问路径
                $pics_origin=DS.'uploads'.DS.$info->getSaveName();

                $temp=explode(DS,$info->getSaveName());
                $pics_big=DS.'uploads'.DS.$temp[0].DS.'thumb_800_'.$temp[1];
                $pics_sma=DS.'uploads'.DS.$temp[0].DS.'thumb_400_'.$temp[1];

                //生成缩略图
                $image=\think\Image::open('.'.$pics_origin);
                $image->thumb(800,800)->save('.'.$pics_big);
                $image->thumb(400,400)->save('.'.$pics_sma);
                //将上传的图片访问路径，保存到相册表
                $row=[
                    'goods_id'=>$goods_id,
                    'pics_big'=>$pics_big,
                    'pics_sma'=>$pics_sma,
                ];
                //将每一条数据，放到一个二维数组中，最后进行批量添加
                $goodspics_data[]=$row;
            }
        }
        //将所有的相册图片数据，添加到相册表
        $goodspics=new Goodspics();
        $goodspics->saveAll($goodspics_data);
    }

    //删除相册图片
    public function delpics($id)
    {
        //参数检测
        if(empty($id)){
            $res=[
                'code'=>10001,
                'msg'=>'参数错误'
            ];
            return json($res);
        }
        //将图片从相册表删除
        Goodspics::destroy($id);
        //返回结果
        $res=[
            'code'=>10000,
            'msg'=>'success'
        ];
        return json($res);
    }
}
