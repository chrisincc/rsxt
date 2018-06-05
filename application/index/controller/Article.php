<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Articles;

class Article extends Controller
{
    public function creatArticle()
    {
		$artilces01= new Articles;
		$artilces01->title  = $_POST['title'];
		$artilces01->contect = $_POST['contect'];
		$artilces01->tab= $_POST['tab'];
		$artilces01->creat_at=Date('Y-m-d H:i:s');
		$artilces01->updata_at=Date('Y-m-d H:i:s');
		
		$result=$artilces01->save();
		 
       if($result){
           
		   $this->error('新增成功');
		   //设置成功后跳转页面的地址，默认的返回页面是$_SERVER['HTTP_REFERER']
           //$this->redirect('article/showArticle');//success('新增成功', url('index'));
		   //$this->redirect('http://localhost/gz/thinkphp/public/index');
       } else {
            //错误页面的默认跳转页面是返回前一页，通常不需要设置
            $this->error('新增失败');
       }


    }
	
	public function showArticle()
    {
		
		
		return view('articles/creatarticle');
		return  '<h1>测试进行到一半了！</h1>';
    }
	public function showTest()
    {
		
		
		//return view('articles/creatarticle');
		return  '<h1>这是article控制器下的测试界面</h1>';
    }
}
?>