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

class User extends Controller
{
    public function register()
    {
		//return json(array('code' => 0, 'msg' => '测试成功，进入register成功'));
		$member = new Users();
        if(request()->isPost()){
			$data=input('post.');
			//$member=request()->except('repassword');
			//检验验证码是否正确
			$vercode=input('vercode');
			if(!captcha_check($vercode)){
				 return json(array('code' => 0, 'msg' => '验证码错误'));
			} /*else {
				 return json(array('code' => 0, 'msg' => '验证码正确'));
			}*/
			//检验密码是否重复
			$password = input('password');
			$repassword = input('repassword');
			/*$member->email=input('email');
			$member->username=input('username');
			$member->password=input('password');
			$member->islogin=input('islogin');*/
			//return json(array('code' => 0, 'msg' => '测试成功,post类型正确,Step1'.$member->password));
			if ($password != $repassword) {
				return json(array('code' => 0, 'msg' => '两次密码不相同'));
			}
			//return json(array('code' => 0, 'msg' => '测试成功,post类型正确,Step2'.$member->password));
			$user = $member->where('email', $data['email'])->find();
			//return json(array('code' => 0, 'msg' => '测试成功,post类型正确,Step3'.$member->password));
			if (!$user) {
				$data['password'] = md5($password);
				//$result=$member->save();//?????/
				
				//return json(array('code' => 0, 'msg' => '测试成功,post类型正确,Step4'.$member->password));
				
				//return json(array('code' => 0, 'msg' => '测试成功,用户在数据库保存成功'.$member->user_id));
				if ($member->allowField(true)->save($data)) {
                    session("user", $member);
					return json(array('code' =>200, 'msg' => '注册成功'));
				} else {
					return json(array('code' => 0, 'msg' => '注册失败'));
				}
			} else {
				return json(array('code' => 0, 'msg' => '该用户已存在'));
			}
			//return json(array('code' => 0, 'msg' => '测试成功,post类型正确'.$password));
		
		}
		//return json(array('code' => 0, 'msg' => '测试成功，post类型不对'));
		return view();
		

		
		
    }

	public function login()
    {
		//return json(array('code' => 0, 'msg' => '测试成功，进入login成功,Step1'));


        if(request()->isPost()){

            $data=input('post.');
			//检验验证码
			$vercode=input('vercode');
			
			//return json(array('code' => 0, 'msg' => '验证码错误'));
			if(!captcha_check($vercode)){
				 return json(array('code' => 0, 'msg' => '验证码错误'));
			}
			
			if (input('type')==='normal'){
                $password = input('password');
                $newUser = new Users();
                $oldUser = $newUser->where('email', $data['email'])->find();

                if($oldUser){

                    if($oldUser->password == md5($password)){
                        session("user", $oldUser);
                        return json(array('code' =>200, 'msg' => '登录成功'));
                    }else{
                        return json(array('code' => 0, 'msg' => '密码不正确'));
                    }

                }else{
                    return json(array('code' => 0, 'msg' => '该用户不存在'));

                }
            }
            if (input('type')==='admin'){
                $password = input('password');
                $newAdmin = new admins();
                $oldAdmin = $newAdmin->where('email', $data['email'])->find();

                if($oldAdmin){

                    if($oldAdmin->password == md5($password)){
                        session("admin", $oldAdmin);
                        return json(array('code' => 201, 'msg' => '登录成功'));
                    }else{
                        return json(array('code' => 0, 'msg' => '密码不正确'));
                    }

                }else{
                    return json(array('code' => 0, 'msg' => '该用户不存在'));

                }
            }


			
		
		}
		return json(array('code' => 0, 'msg' => '测试成功，到达最后一步'));

		
    }

    public function createProfiles()
    {

        if(request()->isPost() and $log_user_id=session('user.user_id')){


            $newProfile = new Profiles();
            $data=input('post.');

            //检查是否已存在个人资料
            $oldProfile=$newProfile->where('user_id',$log_user_id)->find();
            if (empty($oldProfile)){//不存在个人资料则新建
                $data=$data+array('user_id'=>$log_user_id);
                //$data[]=$add_data;
                if ($newProfile->setAttr('profile_id',null)->isUpdate(false)->save($data)) {
                    return json(array('code' => 200, 'msg' => '个人资料提交成功'));
                }
            }
            else{//已存在个人资料则修改
                //$oldProfile->sex='女';
                if ($oldProfile->allowField(true)->save($data)){
                    return json(array('code' => 200, 'msg' => '个人资料修改成功'));
                }
                return json(array('code' => 200, 'msg' => '和上次提交的资料一样'));

            }

        }
        return json(array('code' => 0, 'msg' => '个人资料提交失败'));
        //return view();

    }

	public function showLogin()
    {
		
		
		return view('login');
		//return  '<h1>这是登录界面</h1>';
    }

	public function showReg()
    {
		
		
		return view('Reg');
		//return  '<h1>这是注册界面</h1>';
    }

	public function showUserHome()
    {
        //$test=session('user.user_id');
		if (empty(session('user.user_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{

            return view('userhome');



        }

		//return  '<h1>这是user控制器下showUserHome测试界面！</h1>';
    }

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

	public function showUserHomeMain()
    {
        if (empty(session('user.user_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{
            return view('usermain');
        }

    }

    public function showCreateProfile()
    {
        if (empty(session('user.user_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{

            return view('createProfile');

        }


        //return  '<h1>这是user控制器下showCreateProfile界面！</h1>';
    }

    public function showRepassword()
    {
        if (empty(session('user.user_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{

            return view('Repassword');

        }


        //return  '<h1>这是user控制器下showCreateProfile界面！</h1>';
    }

    public function showRecruitUp()
    {
        //$test=session('user.user_id');
        //return  '<h1>这是user控制器下showRecuitUp测试界面！</h1>';
        if (empty(session('user.user_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{

            return view('RecruitUp');



        }

        //return  '<h1>这是user控制器下showUserHome测试界面！</h1>';
    }

    public function recruitList(){

        if(request()->isPost() and $log_user_id=session('user.user_id')){


            $newRecruit = new Recruits();

            //检查是否已存招聘信息列表
            $recruit_list=$newRecruit->where('begin_time','<= time',date("Y-m-d H:i:s"))
                                     ->where('end_time','>= time',date("Y-m-d H:i:s"))
                                     ->where('recruit_status','=','已发布')
                                     ->select();
            if (empty($recruit_list)){//不存在招聘信息列表
                return json(array('code' =>0, 'msg' => '尚无招聘信息','data'=>''));
            }
            else{//存在招聘列表

                return json(array('code' => 200, 'msg' => '信息列表存在','data'=>$recruit_list));

            }

        }
        return json(array('code' =>0, 'msg' => '信息列表不存在','data'=>''));

    }

    public function departmentList(){

        if(request()->isPost() and $log_user_id=session('user.user_id')){

            $recruit_id=input('post.recruit_id');
            //检查是否存在招聘信息列表
            $newRecruit = new Recruits();
            $recruit_list=$newRecruit->where('begin_time','<= time',date("Y-m-d H:i:s"))
                                     ->where('end_time','>= time',date("Y-m-d H:i:s"))
                                     ->where('recruit_id',$recruit_id)
                                     ->select();
            if (empty($recruit_list)){//如果不存在返回空值
                return json(array('code' =>0, 'msg' => '要求的用人部门不存在','data'=>''));
            }

            //如果招聘信息列表存在，则搜索职位列表获得用人部门信息
            $newDepartment=new Departments();
            $department_list=$newDepartment->table('think_departments one_department,think_positions one_position')
                                        ->where('one_position.department_id = one_department.department_id')
                                        ->where('one_position.recruit_id = '.$recruit_id)
                                        ->distinct(true)
                                        ->field('one_department.department_id as department_id,one_department.department_name as department_name')
                                        ->select();
            if (empty($department_list)){//不存在招聘信息列表
                return json(array('code' =>0, 'msg' => '要求的用人部门不存在','data'=>''));
            }
            else{//存在招聘列表


                return json(array('code' => 200, 'msg' => '信息列表存在','data'=>$department_list));

            }

        }
        return json(array('code' =>0, 'msg' => '信息列表不存在','data'=>''));

    }

    public function positionList(){

        if(request()->isPost() and $log_user_id=session('user.user_id')){

            $recruit_id=input('post.recruit_id');
            $department_id=input('post.department_id');
            //检查是否存在招聘信息列表
            $newRecruit = new Recruits();
            $recruit_list=$newRecruit->where('begin_time','<= time',date("Y-m-d H:i:s"))
                                     ->where('end_time','>= time',date("Y-m-d H:i:s"))
                                     ->where('recruit_id',$recruit_id)
                                     ->select();
            if (empty($recruit_list)){//如果不存在返回空值
                return json(array('code' =>0, 'msg' => '要求的岗位不存在','data'=>''));
            }
            $newPosition = new Positions();
            $position_list=$newPosition->where('recruit_id',$recruit_id)
                                       ->where('department_id',$department_id)
                                       ->distinct(true)
                                       ->field('position_id,position_name')
                                       ->select();

            if (empty($position_list)){//如果不存在返回空值
                return json(array('code' =>0, 'msg' => '要求的岗位不存在','data'=>''));
            }

            else{//存在招聘列表


                return json(array('code' => 200, 'msg' => '信息列表存在','data'=>$position_list));

            }

        }
        return json(array('code' =>0, 'msg' => '信息列表不存在','data'=>''));

    }

    public function positionUp()
    {

        if(request()->isPost() and $log_user_id=session('user.user_id')){
            $data=input('post.');
            //验证所申请的岗位是否存在
            $newPosition=new Positions();
            $position_list=$newPosition->where('recruit_id',$data['recruit_id'])
                                        ->where('department_id',$data['department_id'])
                                        ->where('position_id',$data['position_id'])
                                        ->select();
            if (empty($position_list)){//如果不存在则返回错误信息
                return json(array('code' => 0, 'msg' =>'您申请的岗位不存在'));
            }
            //岗位信息存在则继续执行
            //查找已申请岗位列表，看看申请信息是否存在
            $newPosition_post=new Position_posts();
            $position_post_list=$newPosition_post->where('user_id',$log_user_id)->find();
            if (empty($position_post_list)){//如果不存在则新建信息
                $query_content=array(
                    'user_id'=>$log_user_id
                    ,'recruit_id'=>$data['recruit_id']
                    ,'application_status'=>'待审核'
                    ,'position_id'=>$data['position_id']

                );
                $newPosition_post->allowField(true)->save($query_content);
            }elseif ($position_post_list->status=="退回"){
                $query_content=array(
                    'recruit_id'=>$data['recruit_id']
                    ,'application_status'=>'待审核'
                    ,'position_id'=>$data['position_id']

                );
                $position_post_list->allowField(true)->save($query_content);
            }else{
                return json(array('code' => 0, 'msg' =>'该岗位申请为'.$position_post_list->status.',不能进行修改'));
            }
        }
        return json(array('code' => 200, 'msg' => '岗位申请递交成功，请等待我们的后续通知'));

    }

    public function logout()
    {

        if($log_user_id=session('user.user_id')) {
            session("user", null);
            $this->redirect('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
        }
    }

    public function repasswordUp()
    {

        $member = new Users();
        if(request()->isPost()and $log_user_id=session('user.user_id')){

            $vercode=input('vercode');
            if(!captcha_check($vercode)){
                return json(array('code' => 0, 'msg' => '验证码错误'));
            }
            //检验密码是否重复
            $old_password = input('old_password');
            $password=input('password');
            $repassword = input('repassword');

            if ($password != $repassword) {
                return json(array('code' => 0, 'msg' => '两次密码不相同'));
            }

            $user = $member->where('user_id', $log_user_id)
                           ->where('password',md5($old_password))
                           ->find();

            if (!empty($user)) {
                $user->password=md5($password);

                if ($user->allowField(true)->save()) {

                    return json(array('code' =>200, 'msg' => '密码修改成功'));
                }
            } else {
                return json(array('code' => 0, 'msg' => '密码不正确'));
            }


        }
        return json(array('code' => 0, 'msg' => '请登录后再进行密码修改操作'));





    }

    public function getProfile()
    {
        //$test=0;
        if (request()->isPost()and $log_user_id=session('user.user_id')){
            $position_post=new Position_posts();
            $position_post_status=$position_post->where('user_id',$log_user_id)
                                                ->field('application_status')
                                                ->find();

            $profile=new Profiles();
            $one_profile=$profile->where('user_id',$log_user_id)->select();
            if (!empty($one_profile)){

                if ($position_post_status!=='已退回' ){

                    return json(array('code' =>200, 'msg' => '温馨提示：您所申请的岗位在'.$position_post_status->application_status.'状态，在此次招聘期间不能进行修改个人信息表的操作！','data'=>$one_profile));
                }else{
                    return json(array('code' =>100, 'msg' => '温馨提示：您所申请的岗位在'.$position_post_status->application_status.'状态，在此次招聘期间不能进行修改个人信息表的操作！','data'=>$one_profile));

                }
            }
            else{
                return json(array('code' => 0, 'msg' => '尚未填写任何岗位信息'));
            }
        }
        else {

            return json(array('code' => 0, 'msg' => '请登录后再进行该操作'));
        }
    }

    public function showEvidenceUp()
    {
        //$test=session('user.user_id');
        //return  '<h1>这是user控制器下evidenceUp.html测试界面！</h1>';
        if (empty(session('user.user_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{

            return view('evidence_Up');



        }

        //return  '<h1>这是user控制器下showUserHome测试界面！</h1>';
    }

    public function evidenceUp(){
        //$test='';
        //return json(array('code' => 0, 'msg' => '测试上传接口evidenceUp'));
        if ($log_user_id=session('user.user_id')) {
            // 获取表单上传文件 例如上传了001.jpg
            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size' => 5 * 1024 * 1024, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $log_user_id, '');

            if ($info) {
                // 成功上传后存入数据库
                //查找文件信息是否存在
                $newEvidence = new Evidences();
                $saveEvidence = $newEvidence->where('user_id', $log_user_id)
                    ->where('evidence_name', $info->getFilename())
                    ->select();
                if (empty($saveEvidence)) {
                    $query_contect = array(
                        'user_id' => $log_user_id
                    , 'evidence_name' => $info->getFilename()
                    , 'evidence_full_name' => DS . 'uploads' . DS . $log_user_id . DS . $info->getSaveName()
                    , 'updata_at' => date('Y-m-d')
                    );
                    $saveEvidence = $newEvidence->allowField(true)->save($query_contect);
                    if (!empty($saveEvidence)) {
                        return json(array('code' => 200, 'msg' => '上传成功'));
                    } else {
                        return json(array('code' => 0, 'msg' => '上传失败'));
                    }
                } else {
                    return json(array('code' => 200, 'msg' => '文件覆盖成功'));
                }


            } else {
                // 上传失败获取错误信息
                //$test= $file->getError();
                return json(array('code' => 0, 'msg' => $file->getError()));
            }

            return json(array('code' => 0, 'msg' => '上传失败'));


        }


    }

    public function showEvidence()
    {
        //$test=session('user.user_id');
        //return  '<h1>这是user控制器下showRecuitUp测试界面！</h1>';
        if (empty(session('user.user_id'))) {
            //调回首页
            $this->redirect('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
            //$this->redirect($_SERVER['SERVER_NAME']);

        }
        else{

            return view('evidence');



        }

        //return  '<h1>这是user控制器下showUserHome测试界面！</h1>';
    }

    public function getEvidenceList()
    {
        $test=0;
        if (request()->isPost()and $log_user_id=session('user.user_id')){

            $newEvidence=new Evidences();
            $evidence_list=$newEvidence->where('user_id',$log_user_id)
                                ->field('evidence_id,evidence_name,updata_at')
                                ->select();

            if (!empty($evidence_list)){
                return json(array('code' =>0, 'msg' => '获取证明材料列表成功','count'=>count($evidence_list),'data'=>$evidence_list));
            }
        }
        else {

            return json(array('code' => 200, 'msg' => '您尚未提交任何证明材料'));
        }
    }

    public function editEvidence()
    {
        $count=0;
        $data=json_decode($_POST['evidence'],true);
        //return json(array('code' =>0, 'msg' => '修改证明材料列表成功'));
        if (request()->isPost()and $log_user_id=session('user.user_id')) {

            if ($_POST['active'] === 'del') {
                $evidence = new Evidences();
                $one_evidence=$evidence->where('evidence_id', $data['evidence_id'])->find();
                if (!empty($one_evidence)) {

                    $file=ROOT_PATH.$one_evidence->evidence_full_name;

                    if (unlink($file) and $one_evidence->delete()){
                        return json(array('code' => 0, 'msg' => '删除成功'));
                    }

                }
                return json(array('code' => 200, 'msg' => '该证明材料不存在'));
            }
            if ($_POST['active'] === 'del_list') {
                $evidence = new Evidences();

                $data=json_decode($_POST['evidence'],true);
                foreach ($data as $one){

                    $one_evidence=$evidence->where('evidence_id', $one['evidence_id'])->find();

                    if (!empty($one_evidence)){

                        $file=ROOT_PATH.$one_evidence->evidence_full_name;
                        if (unlink($file) and $one_evidence->delete()){
                            $count++;
                        }
                    }

                }
                return json(array('code' => 0, 'msg' => '成功删除证明材料文件'.$count.'个'));


            }
        }
    }

    public function getEvidence_pic()
    {
        $count=0;
        $data=json_decode($_POST['evidence'],true);
        //return json(array('code' =>0, 'msg' => '修改证明材料列表成功'));
        if (request()->isPost() and $log_user_id=session('user.user_id')) {

            $evidence = new Evidences();
            $one_evidence=$evidence->where('evidence_id', $data['evidence_id'])->find();
            if (!empty($one_evidence)) {

                return json(array('code' => 0, 'msg' => $one_evidence->evidence_full_name));
            }

            //$test=json_encode($res);

            return json(array('code' => 200, 'msg' => '该证明材料异常'));


        }
    }

	public function showTest()
    {
		
		
		//return view('Reg');showRecuitUp
		return  '<h1>这是user控制器下测试界面！</h1>';
    }
	
}
?>