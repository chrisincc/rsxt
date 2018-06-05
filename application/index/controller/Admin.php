<?php
namespace app\index\controller;


use think\Controller;
use think\View;
use app\index\model\Users;
use app\index\model\Profiles;
use app\index\model\Recruits;
use app\index\model\Departments;
use app\index\model\Positions;
use app\index\model\Position_posts;
use app\index\model\Evidences;
use app\index\model\Admins;

class Admin extends Controller
{


    public function showAdminHome()
    {
        //$test=session('user.user_id');
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{

            return view('adminhome');



        }

        //return  '<h1>这是user控制器下showUserHome测试界面！</h1>';
    }

    public function getPositionPostList()
    {
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')) {

            //$test=count($_POST);
            if (count($_POST) <= 2) {
                //初次刷新
                $newPositionPost=new Position_posts();
                $newPosition = new Positions();
                $newRecruit = new Recruits();
                $newDepartment = new Departments();
                $newProfile=new Profiles();
                //$positionPostList=0;
                $count=$newPositionPost->count();
                $positionPostList = $newPositionPost->page($_POST['page'], $_POST['limit'])->select();

                if (!empty($positionPostList)) {

                    foreach ($positionPostList as $item) {
                        $data = $item->toArray();
                        //通过招聘信息表，查询招聘标题
                        $title = $newRecruit->where('recruit_id', $item->recruit_id)->column('title');
                        $data = $data + array('title' => $title[0]);
                        //通过岗位信息列表，查询岗位名称
                        $oldPosition= $newPosition->where('position_id', $item->position_id)->find();
                        $data = $data + array('position_name' => $oldPosition->position_name);
                        //通过部门列表，查询用人部门
                        $department_name = $newDepartment->where('department_id', $oldPosition->department_id)->column('department_name');
                        $data = $data + array('department_name' => $department_name[0]);
                        //通过用户资料列表，查询用户姓名
                        $username = $newProfile->where('user_id', $item->user_id)->column('username');
                        $data = $data + array('username' => $username[0]);

                        $dataList[] = $data;
                    }


                    return json(array('code' => 0, 'msg' => '', 'count' => $count, 'data' => $dataList));
                } else {
                    return json(array('code' => 200, 'msg' => '尚未有任何应聘信息'));
                }
            } else {

                $newPositionPost=new Position_posts();
                $newPosition = new Positions();
                $newRecruit = new Recruits();
                $newDepartment = new Departments();
                $newProfile=new Profiles();
                //$positionPostList=0;
                //$count=$newPositionPost->count();
                //$positionPostList = $newPositionPost->page($_POST['page'], $_POST['limit'])->select();

                if ($_POST['key']['department_id']!=''){

                    //postion_posts和position联合查询
                    $positionPostList=$newPositionPost->table(array('think_position_posts'=>'positionPosts','think_positions'=>'positions'))
                                                      ->where('positionPosts.recruit_id='.$_POST['key']['recruit_id'])
                                                      ->where('positionPosts.position_id=positions.position_id')
                                                      ->where('positions.department_id='.$_POST['key']['department_id'])
                                                      ->page($_POST['page'],$_POST['limit'])->select();

                    $count=$newPositionPost->table(array('think_position_posts'=>'positionPosts','think_positions'=>'positions'))
                        ->where('positionPosts.recruit_id='.$_POST['key']['recruit_id'])
                        ->where('positionPosts.position_id=positions.position_id')
                        ->where('positions.department_id='.$_POST['key']['department_id'])
                        ->page($_POST['page'],$_POST['limit'])
                        ->count();
                }else{

                    $positionPostList=$newPositionPost->where('recruit_id',$_POST['key']['recruit_id'])
                                                      ->page($_POST['page'],$_POST['limit'])->select();
                    $count=$newPosition->where('recruit_id',$_POST['key']['recruit_id'])
                                       ->count();

                }


                if (!empty($positionPostList)){

                    foreach ($positionPostList as $item) {
                        $data = $item->toArray();
                        //通过招聘信息表，查询招聘标题
                        $title = $newRecruit->where('recruit_id', $item->recruit_id)->column('title');
                        $data = $data + array('title' => $title[0]);
                        //通过岗位信息列表，查询岗位名称
                        $oldPosition= $newPosition->where('position_id', $item->position_id)->find();
                        $data = $data + array('position_name' => $oldPosition->position_name);
                        //通过部门列表，查询用人部门
                        $department_name = $newDepartment->where('department_id', $oldPosition->department_id)->column('department_name');
                        $data = $data + array('department_name' => $department_name[0]);
                        //通过用户资料列表，查询用户姓名
                        $username = $newProfile->where('user_id', $item->user_id)->column('username');
                        $data = $data + array('username' => $username[0]);

                        $dataList[] = $data;
                    }


                    return json(array('code' =>0, 'msg' => '', 'count' => $count,'data'=>$dataList));
                }else{
                    return json(array('code' => 200, 'msg' => '尚未有任何应聘信息'));
                }

            }
        }else {

            return json(array('code' => 200, 'msg' => '请登录后再进行相关操作'));
        }
    }

    public function editPositionPost()
    {
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')) {

            $test=count($_POST);
            if ($_POST['active']==='pass'){

                //检查应聘信息是否存在
                $positionPost=$_POST['positionPost'];
                $newPositionPost=new Position_posts();
                $oldPositionPost=$newPositionPost->where('application_id',$positionPost['application_id'])->find();
                if (!empty($oldPositionPost)){
                    $oldPositionPost->application_status='通过审核';
                    if ($oldPositionPost->save()){
                        return json(array('code' =>200, 'msg' => '该应聘者已通过审核！'));
                    } else{
                        return json(array('code' =>0, 'msg' => '审核信息修改失败，请稍后再试！'));
                    }
                } else{
                    return json(array('code' =>0, 'msg' => '您所修改的审核信息不存在。'));
                }


            }

        }else {

            return json(array('code' => 200, 'msg' => '请登录后再进行相关操作'));
        }
    }

	public function showAdminHomeMain()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('adminmain');
        }

    }

    public function showUsers()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('users');
        }

    }

    public function getUsersList()
    {
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){

            //$test=count($_POST);
            if (count($_POST)<=2){
                $newUser=new Users();
                $userList=$newUser->field('user_id,username,email')->page($_POST['page'],$_POST['limit'])->select();

                if (!empty($userList)){
                    return json(array('code' =>0, 'msg' => '','count'=>count($userList),'data'=>$userList));
                }else{
                    return json(array('code' => 200, 'msg' => '尚未有任何用户注册'));
                }
            }else{
                $newUser=new Users();
                $key=$_POST['key']['email'];
                $userList=$newUser->where('email','like','%'.$key.'%')
                                  ->field('user_id,username,email')
                                  ->page($_POST['page'],$_POST['limit'])
                                  ->select();

                if (!empty($userList)){
                    return json(array('code' =>0, 'msg' => '','count'=>count($userList),'data'=>$userList));
                }else{
                    return json(array('code' => 200, 'msg' => '未找到包含该关键字的用户信息'));
                }
            }

        }
        else {

            return json(array('code' => 200, 'msg' => '尚未有任何用户注册'));
        }
    }

    public function reset_password()
    {


        if(request()->isPost()and $log_admin_id=session('admin.admin_id')){

            $initial_password='654321';
            $newUser = new Users();
            $oldUser=$newUser->where('user_id',$_POST['user_id'])->find();
            if(!empty($oldUser)){
                $oldUser->password=md5($initial_password);
                if ($oldUser->allowField(true)->save()) {

                    return json(array('code' =>0, 'msg' => '密码重置成功，初试密码为：'.$initial_password));
                }
                return json(array('code' => 200, 'msg' => '密码重置错误，请稍后再试！'));

            } else {
                return json(array('code' => 200, 'msg' => '密码重置错误，请稍后再试！'));
            }


        }
        return json(array('code' => 200, 'msg' => '请登录后再进行密码重置操作'));

    }

    public function showadmin()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('admins');
        }

    }

    public function getAdminList()
    {
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){

            //$test=count($_POST);
            if (count($_POST)<=2){
                //初次刷新
                $newAdmin=new Admins();
                $adminList=$newAdmin->field('admin_id,admin_name,email,status,authority')->page($_POST['page'],$_POST['limit'])->select();

                if (!empty($adminList)){
                    return json(array('code' =>0, 'msg' => '','count'=>count($adminList),'data'=>$adminList));
                }else{
                    return json(array('code' => 200, 'msg' => '尚未有任何管理员'));
                }
            }else{
                //查询刷新
                $newAdmin=new Admins();
                $key=$_POST['key']['email'];
                $adminList=$newAdmin->where('email','like','%'.$key.'%')
                    ->field('admin_id,admin_name,email,status,authority')
                    ->page($_POST['page'],$_POST['limit'])
                    ->select();

                if (!empty($adminList)){
                    return json(array('code' =>0, 'msg' => '','count'=>count($adminList),'data'=>$adminList));
                }else{
                    return json(array('code' => 200, 'msg' => '未找到包含该关键字管理员信息信息'));
                }
            }

        }
        else {

            return json(array('code' => 200, 'msg' => '尚未有任何管理员'));
        }
    }

    public function edit_admin()
    {


        if(request()->isPost()and $log_admin_id=session('admin.admin_id')){

            $data=json_decode($_POST['data'],true);
            if (!$data['authority']==='超级'){
                return json(array('code' => 200, 'msg' => '您没有权限进行该操作！'));
            }
            if ($_POST['active']==='reset_password'){

                $initial_password='654321';
                $newAdmin = new Admins();
                $oldAdmin=$newAdmin->where('admin_id',$data['admin_id'])->find();
                if(!empty($oldAdmin)){

                    if ($oldAdmin->password===md5($initial_password)){
                        return json(array('code' =>0, 'msg' => '密码重置成功，初试密码为：'.$initial_password));
                    }

                    $oldAdmin->password=md5($initial_password);
                    if ($oldAdmin->allowField(true)->save()) {

                        return json(array('code' =>0, 'msg' => '密码重置成功，初试密码为：'.$initial_password));
                    }
                    return json(array('code' => 200, 'msg' => '密码重置错误，请稍后再试！'));

                } else {
                    return json(array('code' => 200, 'msg' => '密码重置错误，请稍后再试！'));
                }

            }
            if ($_POST['active']==='enable' and $data['status']==='禁用'){

                $newAdmin = new Admins();
                $oldAdmin=$newAdmin->where('admin_id',$data['admin_id'])->find();
                if(!empty($oldAdmin)){
                    $oldAdmin->status='启用';
                    if ($oldAdmin->allowField(true)->save()) {

                        return json(array('code' =>0, 'msg' => '用户启用成功！'));
                    }
                    return json(array('code' => 200, 'msg' => '用户启用失败，请稍后再试！'));

                } else {
                    return json(array('code' => 200, 'msg' => '用户启用失败，请稍后再试！'));
                }
            }

            if ($_POST['active']==='disable' and $data['status']==='启用'){

                $newAdmin = new Admins();
                $oldAdmin=$newAdmin->where('admin_id',$data['admin_id'])->find();
                if(!empty($oldAdmin)){
                    $oldAdmin->status='禁用';
                    if ($oldAdmin->allowField(true)->save()) {

                        return json(array('code' =>0, 'msg' => '用户禁用成功！'));
                    }
                    return json(array('code' => 200, 'msg' => '用户禁用失败，请稍后再试！'));

                } else {
                    return json(array('code' => 200, 'msg' => '用户禁用失败，请稍后再试！'));
                }
            }



        }
        return json(array('code' => 200, 'msg' => '请登录后再进行密码重置操作'));

    }

    public function showAddAdmin()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('addAdmin');
        }

    }

    public function addAdmin()
    {
        //return json(array('code' => 0, 'msg' => '测试成功，进入register成功'));
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){
            $data=input('post.');
            //检验用户名是否重复
            $newAdmin=new Admins();
            $oldAdmin=$newAdmin->where('email',$data['email'])->find();
            if (empty($oldAdmin)){
                $admin_array=array(
                    'email'=>$data['email'],
                    'password'=>md5('654321'),
                    'admin_name'=>$data['admin_name'],
                    'status'=>'启用',
                    'authority'=>'普通'
                );
                if ($newAdmin->save($admin_array)){
                    return json(array('code' =>200, 'msg' => '管理员创建成功，初试密码为：654321'));
                } else{
                    return json(array('code' =>0, 'msg' => '管理员创建失败，请稍后再试！'));
                }
            } else{
                return json(array('code' =>0, 'msg' => '您所创建的管理员已存在。'));
            }
        }
        return json(array('code' =>0, 'msg' => '请登录后再进行该操作'));

    }

    public function showRecruitMain()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('recruitMain');
        }

    }

    public function getRecruitList()
    {
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){

            //$test=count($_POST);
            if (count($_POST)<=2){
                //初次刷新
                $newRecruit=new Recruits();
                $recruitList=$newRecruit->page($_POST['page'],$_POST['limit'])->select();

                if (!empty($recruitList)){

                    return json(array('code' =>0, 'msg' => '','count'=>count($recruitList),'data'=>$recruitList));
                }else{
                    return json(array('code' => 200, 'msg' => '尚未有任何招聘信息'));
                }
            }else{
                //查询刷新
                $newRecruit=new Recruits();
                $key=$_POST['key']['title'];
                $recruitList=$newRecruit->where('title','like','%'.$key.'%')
                    ->page($_POST['page'],$_POST['limit'])
                    ->select();

                if (!empty($recruitList)){
                    return json(array('code' =>0, 'msg' => '','count'=>count($recruitList),'data'=>$recruitList));
                }else{
                    return json(array('code' => 200, 'msg' => '未找到包含该关键字管理员信息信息'));
                }
            }

        }
        else {

            return json(array('code' => 200, 'msg' => '尚未有任何管理员'));
        }
    }

    public function getRecruit()
    {
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){



                $newRecruit=new Recruits();
                $recruitList=$newRecruit->where('recruit_id',$_POST['recruit_id'])->select();

                if (!empty($recruitList)){

                    return json(array('code' =>200, 'msg' => '','data'=>$recruitList));
                }else{
                    return json(array('code' => 0, 'msg' => '尚未有任何招聘信息'));
                }


        }
        else {

            return json(array('code' => 200, 'msg' => '请以管理员的身份登录后再进行该操作'));
        }
    }

    public function showAddRecruit()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('addRecruit');
        }

    }

    public function addRecruit()
    {
        //return json(array('code' => 0, 'msg' => '测试成功，进入register成功'));
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){
            $data=input('post.');
            //检验用户名是否重复
            $newRecruit=new Recruits();
            $oldRecruit=$newRecruit->where('title',$data['title'])->find();
            if (empty($oldRecruit)){
                $Recruit_array=array(
                    'admin_id'=>$log_admin_id,
                    'title'=>$data['title'],
                    'begin_time'=>$data['begin_time'],
                    'end_time'=>$data['end_time'],
                    'recruit_status'=>'待发布',
                    'position_count'=>0,
                    'position_post_count'=>0
                );
                if ($newRecruit->save($Recruit_array)){
                    return json(array('code' =>200, 'msg' => '新招聘信息创建成功'));
                } else{
                    return json(array('code' =>0, 'msg' => '新招聘信息失败，请稍后再试！'));
                }
            } else{
                return json(array('code' =>0, 'msg' => '您所创建的招聘信息已存在。'));
            }
        }
        return json(array('code' =>0, 'msg' => '请登录后再进行该操作'));

    }

    public function showEditRecruit()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{

            return view('editRecruit', ['recruit_id'=>$_GET['recruit_id']]);

            //return view('editRecruit');
        }

    }

    public function modifyRecruit()
    {
        //return json(array('code' => 0, 'msg' => '测试成功，进入register成功'));
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){
            $data=input('post.');
            //检验用户名是否重复
            $newRecruit=new Recruits();
            $oldRecruit=$newRecruit->where('recruit_id',$data['recruit_id'])->find();
            if (!empty($oldRecruit)){

                if ($oldRecruit->save($data)){
                    return json(array('code' =>200, 'msg' => '招聘信息修改成功'));
                } else{
                    return json(array('code' =>0, 'msg' => '招聘信息修改失败，请稍后再试！'));
                }
            } else{
                return json(array('code' =>0, 'msg' => '您所创建的招聘信息不存在。'));
            }
        }
        return json(array('code' =>0, 'msg' => '请登录后再进行该操作'));

    }

    public function del_recruit()
    {
        //return json(array('code' => 0, 'msg' => '测试成功，进入register成功'));
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){
            $data=input('post.');
            //检验用户名是否重复
            $newRecruit=new Recruits();
            $oldRecruit=$newRecruit->where('recruit_id',$data['recruit_id'])->find();
            if (!empty($oldRecruit)){

                if ($oldRecruit->save($data)){
                    return json(array('code' =>200, 'msg' => '招聘信息修改成功'));
                } else{
                    return json(array('code' =>0, 'msg' => '招聘信息修改失败，请稍后再试！'));
                }
            } else{
                return json(array('code' =>0, 'msg' => '您所创建的招聘信息不存在。'));
            }
        }
        return json(array('code' =>0, 'msg' => '请登录后再进行该操作'));

    }

    public function showPositionMain()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('positionMain');
        }

    }

    public function getPositionList()
    {
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')) {

            //$test=count($_POST);
            if (count($_POST) <= 2) {
                //初次刷新
                $newPosition = new Positions();
                $newRecruit = new Recruits();
                $newDepartment = new Departments();
                $positionList = $newPosition->page($_POST['page'], $_POST['limit'])->select();
                $count=$newPosition->count();
                if (!empty($positionList)) {

                    foreach ($positionList as $item) {
                        $data = $item->toArray();
                        $title = $newRecruit->where('recruit_id', $item->recruit_id)->column('title');
                        $department_name = $newDepartment->where('department_id', $item->department_id)->column('department_name');
                        $data = $data + array('title' => $title[0], 'department_name' => $department_name[0]);
                        $dataList[] = $data;
                    }


                    return json(array('code' => 0, 'msg' => '', 'count' => $count, 'data' => $dataList));
                } else {
                    return json(array('code' => 200, 'msg' => '尚未有任何岗位信息'));
                }
            } else {

                $newPosition=new Positions();
                $newRecruit=new Recruits();
                $newDepartment=new Departments();
                if ($_POST['key']['department_id']!=''){

                    $positionList=$newPosition->where('recruit_id',$_POST['key']['recruit_id'])
                                              ->where('department_id',$_POST['key']['department_id'])
                                              ->page($_POST['page'],$_POST['limit'])->select();
                    $count=$newPosition->where('recruit_id',$_POST['key']['recruit_id'])
                                       ->where('department_id',$_POST['key']['department_id'])
                                       ->count();
                }else{

                    $positionList=$newPosition->where('recruit_id',$_POST['key']['recruit_id'])
                                              ->page($_POST['page'],$_POST['limit'])->select();
                    $count=$newPosition->where('recruit_id',$_POST['key']['recruit_id'])
                                       ->count();
                }


                if (!empty($positionList)){

                    foreach ($positionList as $item){
                        $data=$item->toArray();
                        $title=$newRecruit->where('recruit_id',$item->recruit_id)->column('title');
                        $department_name=$newDepartment->where('department_id',$item->department_id)->column('department_name');
                        $data=$data+array('title'=>$title[0],'department_name'=>$department_name[0]);
                        $dataList[]=$data;
                    }


                    return json(array('code' =>0, 'msg' => '', 'count' => $count,'data'=>$dataList));
                }else{
                    return json(array('code' => 200, 'msg' => '尚未有任何岗位信息'));
                }


            }
        }else {

            return json(array('code' => 200, 'msg' => '请登录后再进行相关操作'));
        }
    }

    public function get_recruit_department_list()
    {
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){

            //$test=count($_POST);
            if ($_POST['key']==='recruit'){

                $newRecruit=new Recruits();
                $recruitList=$newRecruit->where('recruit_status','待发布')->select();

                if (!empty($recruitList)){

                    return json(array('code' =>200, 'msg' => '','data'=>$recruitList));
                }else{
                    return json(array('code' => 0, 'msg' => '尚未有任何招聘信息'));
                }
            }else if($_POST['key']==='department'){

                //如果招聘信息列表存在，则搜索职位列表获得用人部门信息
                $newDepartment=new Departments();
                $department_list=$newDepartment->table('think_departments one_department,think_positions one_position')
                    ->where('one_position.department_id = one_department.department_id')
                    ->where('one_position.recruit_id = '.$_POST['recruit_id'])
                    ->distinct(true)
                    ->field('one_department.department_id as department_id,one_department.department_name as department_name')
                    ->select();
                if (empty($department_list)){//不存在招聘信息列表
                    return json(array('code' =>0, 'msg' => '','data'=>''));
                }
                else{//存在招聘列表


                    return json(array('code' =>200, 'msg' => '信息列表存在','data'=>$department_list));

                }
            }

        }
        else {

            return json(array('code' => 200, 'msg' => '尚未有任何管理员'));
        }
    }

    public function edit_position()
    {
        //return json(array('code' => 0, 'msg' => '测试成功，进入register成功'));
        //$test=0;
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){

            if ($_POST['active']==='save'){

                $position=$_POST['position'];
                //检查部门是否发生变化
                $newDepartment=new Departments();
                $oldDepartment=$newDepartment->where('department_name',$position['department_name'])->find();
                if (empty($oldDepartment)){
                    $newDepartment->department_name=$position['department_name'];
                    $newDepartment->save();
                    $position['department_id']=$newDepartment->department_id;
                }else{
                    $position['department_id']=$oldDepartment->department_id;
                }


                //检验岗位是否存在
                $newPosition=new Positions();
                $oldPosition=$newPosition->where('position_id',$position['position_id'])->find();
                if (!empty($oldPosition)){

                    if ($oldPosition->allowField(true)->save($position)){
                        return json(array('code' =>200, 'msg' => '岗位信息修改成功'));
                    } else{
                        return json(array('code' =>0, 'msg' => '岗位信息修改失败，请稍后再试！'));
                    }
                } else{
                    return json(array('code' =>0, 'msg' => '您所修改的岗位信息不存在。'));
                }
            }
            if ($_POST['active']==='del'){

                $position=$_POST['position'];
                //检验岗位是否存在
                $newPosition=new Positions();
                $oldPosition=$newPosition->where('position_id',$position['position_id'])->find();
                if (!empty($oldPosition)){

                    if ($oldPosition->delete()){
                        return json(array('code' =>200, 'msg' => '岗位信息删除成功'));
                    } else{
                        return json(array('code' =>0, 'msg' => '岗位信息删除失败，请稍后再试！'));
                    }
                } else{
                    return json(array('code' =>0, 'msg' => '您所需要删除的岗位信息不存在。'));
                }
            }

        }else{
            return json(array('code' =>0, 'msg' => '请登录后再进行该操作'));
        }


    }

    public function showAddPositions()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('addPosition');
        }

    }

    public function showPositionBatchUpload()
    {
        //$test=0;
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            if (request()->isGet()){
                $newRecruit=new Recruits();
                $oldRecruit=$newRecruit->where('recruit_id',$_GET['recruit_id'])->find();
                if (!empty($oldRecruit)){
                    return view('PositionBatchUpload',['recruit'=>$oldRecruit]);
                }

            }

        }

    }

    public function positionUpload()
    {
        $test = 0;
        if (request()->isPost() and $log_admin_id = session('admin.admin_id')) {

            $newDepartment = new Departments();
            $filename = $_FILES["file"]["tmp_name"];
            $file = fopen($filename, 'r');
            $count = 0;
            while ($tmp_data = fgetcsv($file)) { //每次读取CSV里面的一行内容
                if (empty($tmp_data[0]) or $tmp_data[1] === '岗位名称' or $tmp_data[2] === '人数') {//如果是空行则跳出本次循环,跳过表头
                    continue;
                }
                if (count($tmp_data) === 6 and $tmp_data[0] !== '' and $tmp_data[1] !== '' and $tmp_data[2] !== '') {//检查是否符合模板
                    $data = array(
                        'recruit_id' => $_POST['recruit_id']
                    , 'department_id' => 0
                    , 'position_name' => $tmp_data[1]
                    , 'position_number' => $tmp_data[2]
                    , 'major_demand' => $tmp_data[3]
                    , 'education_demand' => $tmp_data[4]
                    , 'note' => $tmp_data[5]
                    );
                    //根据用人部门信息搜索用人部门ID
                    $oldDepartment = $newDepartment->where('department_name', $tmp_data[0])->find();
                    if (!empty($oldDepartment)) {//存在该部门，则读取部门ID
                        $data['department_id'] = $oldDepartment->department_id;

                    } else {//如果不存在该部门，则新增该部门

                        $newDepartment->department_name = $tmp_data[0];
                        $newDepartment->setAttr('department_id', null)->isUpdate(false)->save();
                    }

                    $datalist[] = $data;

                }
            }
            $test = 1;
            $newPosition = new Positions();
            foreach ($datalist as $key => $item) {
                //查找岗位是否存在，如果不存在则新建岗位
                $oldPosition=$newPosition->where($item)->find();
                //$oldPosition = $newPosition->where('recruit_id', $item['recruit_id'])
                //    ->where('department_id', $item['department_id'])
                //    ->where('position_name', $item['position_name'])
                //    ->select();
                if (empty($oldPosition)) {
                    $newPosition->setAttr('position_id', null)->isUpdate(false)->save($item);
                    $count++;

                }

            }

            return json(array('code' =>200, 'msg' => '新增或更新岗位'.$count.'个','data'=>''));


        }else{
            return json(array('code' =>0, 'msg' => '请登录后再进行该操作！','data'=>''));
        }
    }

    public function showAddSinglePosition()
    {
        if (empty(session('admin.admin_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            if (request()->isGet()){
                $newRecruit=new Recruits();
                $oldRecruit=$newRecruit->where('recruit_id',$_GET['recruit_id'])->find();
                if (!empty($oldRecruit)){
                    return view('addSinglePosition',['recruit'=>$oldRecruit]);
                }

            }

        }

    }

    public function addsinglePosition()
    {
        //return json(array('code' => 0, 'msg' => '测试成功，进入register成功'));
        if (request()->isPost()and $log_admin_id=session('admin.admin_id')){
            $data=input('post.');
            //检验部门是否存在
            $newDepartment = new Departments();
            $oldDepartment=$newDepartment->where('department_name',$data['department_name'])->find();
            if (!empty($oldDepartment)) {//存在该部门，则读取部门ID
                $data=$data+array('department_id'=>$oldDepartment->department_id);
                //$data['department_id'] = $oldDepartment->department_id;

            } else {//如果不存在该部门，则新增该部门

                $newDepartment->department_name = $data['department_name'];
                $newDepartment->setAttr('department_id', null)->isUpdate(false)->save();
            }
            unset($data['department_name']);
            $newPosition=new Positions();
            $oldPosition=$newPosition->where($data)->find();
            if (empty($oldPosition)){

                if ($newPosition->save($data)){
                    return json(array('code' =>200, 'msg' => '新岗位信息创建成功'));
                } else{
                    return json(array('code' =>0, 'msg' => '新岗位信息失败，请稍后再试！'));
                }
            } else{
                return json(array('code' =>0, 'msg' => '您所创建的岗位信息已存在。'));
            }
        }
        return json(array('code' =>0, 'msg' => '请登录后再进行该操作'));

    }

    public function templateDownload()
    {
        //return json(array('code' => 0, 'msg' => '测试成功，进入register成功'));
        if ($log_admin_id=session('admin.admin_id')){
            //这个函数其实就是借用了php的file相关函数，从fopen,fread,fclose
            $filename=ROOT_PATH . 'public' . DS . 'downloads'.DS.'批量添加岗位模板.csv';
            $basename=pathinfo($filename);
            header("Content-Type: csv"); //指定下载文件类型的
            header("Content-Disposition:attachment;filename=".$basename["basename"]);
//指定下载文件的描述信息
            header("Content-Length:".filesize($filename));  //指定文件大小的
            readfile($filename);

        }else{
            return json(array('code' =>0, 'msg' => '请登录后再进行该操作'));
        }


    }

    public function logout()
    {

        if($log_user_id=session('admin.admin_id')) {
            session("user", null);
            $this->redirect('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
        }
    }

	public function showTest()
    {
		
		
		//return view('Reg');showRecuitUp
		return  '<h1>这是admin控制器下测试界面！</h1>';
    }
	
}
?>