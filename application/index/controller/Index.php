<?php
namespace app\index\controller;

use think\View;
use think\Controller;
use app\index\model\Articles;

class Index extends Controller
{
    public function index()
    {
		$article=new Articles();
		$art_list=$article->order('updata_at desc')->limit(10)->select();
		
		/*foreach($art_list as $list){
			echo $list->title."****".$list->updata_at."<br>";
		}*/
		//return
		$this->assign('list',$art_list);//return this->fetch('index');
		
		
		return $this->fetch('index');
		//$view=new View();
        //return $view->fetch('index');
		//return view('articles/creatarticle');
		//return view('index');//ront@artical/creatarticle');front/view/articles.html
		//return  '<h1>测试进行到一半了！</h1>';
    }


}

?>