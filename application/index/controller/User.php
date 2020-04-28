<?php
namespace app\index\controller;

use think\Request;
use app\index\model\User as UserModel;
use think\Session;

class User extends Base
{
    //渲染登录界面
    public function login()
    {
        $this -> alreadyLogin();  //防止用户重复登录
        $this -> view -> assign('title', '用户登录');
        $this -> view -> assign('keywords', '书友');
        $this -> view -> assign('desc', '书友，带你进入深层阅读世界');
        $this -> view -> assign('copyRight', '本案例仅为学习作品');
        return $this -> view -> fetch();
    }

    //验证登录
    public function checkLogin(Request $request)
    {
        //初始返回参数
        $status = 0;
        $result = '验证失败';
        $data = $request -> param();

        //创建验证规则
        $rule = [
            'name|用户名' => 'require',   //用户名密码验证码必填
            'password|密码' => 'require',
            'verify|验证码' => 'require|captcha',
        ];

        //自定义验证失败的提示信息
       /* $msg = [
            'name' => ['require'=>'用户名不能为空，请检查'],
            'password' => ['require'=>'密码不能为空，请检查'],
            'verify' => [
                'captcha'=>'验证码错误',
                'require'=>'验证码不能为空，请检查'],
        ];*/

        //进行验证
        //$result = $this -> validate($data,$rule,$msg);
        $result = $this -> validate($data,$rule);

        //如果验证通过则执行
        if($result === true){

            //构造查询条件
            $map = [
                'name' => $data['name'],
                'password' => md5($data['password']),
            ];

            //查询用户信息
            $user = UserModel::get($map);
            if($user == null){
                $result = '没有找到该用户';
            }else{
                $status = 1;
                $result = '验证通过，点击【确定】进入';
                //设置用户登录信息用session
                Session::set('user_id',$user->id);  //用户id
                Session::set('user_info',$user->getData());  //获取用户所有信息
            }

            //更新用户登录次数:自增1
            $user -> setInc('login_count');
        }

        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
    }

    //退出登录
    public function logout()
    {
        //退出前先更新登录时间字段,下次登录时就知道上次登录时间了
        UserModel::update(['login_time'=>time()],['id'=> Session::get('user_id')]);
        //注销session
        Session::delete('user_id');
        Session::delete('user_info');
        $this -> success('注销登录，正在返回','index/home');
    }

    //管理员列表
    public function  adminList()
    {
        $this -> view -> assign('title', '管理员列表');
        $this -> view -> assign('keywords', '书友');
        $this -> view -> assign('desc', '协同阅读平台');

        $this -> view -> count = UserModel::count();

        //判断当前是不是admin用户
        //先通过session获取到用户登陆名
        $userName = Session::get('user_info.name');
        if ($userName == 'admin') {
            $list = UserModel::all();  //admin用户可以查看所有记录,数据要经过模型获取器处理
        } else {
            //为了共用列表模板,使用了all(),其实这里用get()符合逻辑,但有时也要变通
            //非admin只能看自己信息,数据要经过模型获取器处理
            $list = UserModel::all(['name'=>$userName]);
        }

        $this -> view -> assign('list', $list);
        //渲染管理员列表模板
        return $this -> view -> fetch('admin_list');
    }

    //管理员状态变更
    public function setStatus(Request $request)
    {
        $user_id = $request -> param('id');
        $result = UserModel::get($user_id);
        if($result->getData('status') == 1) {
            UserModel::update(['status'=>0],['id'=>$user_id]);
        } else {
            UserModel::update(['status'=>1],['id'=>$user_id]);
        }
    }

    //渲染编辑管理员界面
    public function adminEdit(Request $request)
    {
        $user_id = $request -> param('id');
        $result = UserModel::get($user_id);
        $this->view->assign('title','编辑管理员信息');
        $this->view->assign('keywords','书友');
        $this->view->assign('desc','书友协同阅读平台');
        $this->view->assign('user_info',$result->getData());
        return $this->view->fetch('admin_edit');
    }

    //更新数据操作
    public function editUser(Request $request)
    {
        //获取表单返回的数据
//        $data = $request -> param();
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)){
                $data[$key] = $value;
            }
        }

        $condition = ['id'=>$data['id']] ;
        $result = UserModel::update($data, $condition);

        //如果是admin用户,更新当前session中用户信息user_info中的角色role,供页面调用
        if (Session::get('user_info.name') == 'admin') {
            Session::set('user_info.role', $data['role']);
        }


        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,请检查'];
        }
    }

    //删除操作
    public function deleteUser(Request $request)
    {
        $user_id = $request -> param('id');
        UserModel::update(['is_delete'=>1],['id'=> $user_id]);
        UserModel::destroy($user_id);

    }

    //恢复删除操作
    public function unDelete()
    {
        UserModel::update(['delete_time'=>NULL],['is_delete'=>1]);
    }

    //添加操作的界面
    public function  adminAdd()
    {
        $this->view->assign('title','添加管理员');
        $this->view->assign('keywords','书友');
        $this->view->assign('desc','书友协同阅读平台');
        return $this->view->fetch('admin_add');
    }

    //检测用户名是否可用
    public function checkUserName(Request $request)
    {
        $userName = trim($request -> param('name'));
        $status = 1;
        $message = '用户名可用';
        if (UserModel::get(['name'=> $userName])) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '用户名重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //检测用户邮箱是否可用
    public function checkUserEmail(Request $request)
    {
        $userEmail = trim($request -> param('email'));
        $status = 1;
        $message = '邮箱可用';
        if (UserModel::get(['email'=> $userEmail])) {
            //查询表中找到了该邮箱,修改返回值
            $status = 0;
            $message = '邮箱重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //添加操作
    public function addUser(Request $request)
    {
        $data = $request -> param();
        $status = 1;
        $message = '添加成功';

        $rule = [
            'name|用户名' => "require|min:3|max:10",
            'password|密码' => "require|min:3|max:10",
            'email|邮箱' => 'require|email'
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true) {
            $user= UserModel::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
        }


        return ['status'=>$status, 'message'=>$message];
    }
}
