<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use Auth;
use App\Handlers\ImageUploadHandler;
use \App\Models\User;
use App\Models\Link;

use App\Models\UserData;
use App\Jobs\UpdateMileageSort;
use App\Models\Mongo\Activity;
use OSS\OssClient;
use OSS\Core\OssException;

class TopicsController extends Controller
{   
    protected $accessKeyId;     //您从OSS获得的AccessKeyId
    protected $accessKeySecret; //您从OSS获得的AccessKeySecret
    protected $endpoint;        //您选定的OSS数据中心访问域名，例如http://oss-cn-hangzhou.aliyuncs.com>
    protected $bucket;          //您使用的存储空间名称，注意命名规范
    protected $ossClient;

    public function __construct()
    {   
	    $this->middleware('auth', ['except' => ['index', 'show']]);
	    $this->accessKeyId  =  env('ALIYUN_ACCESS_KEY_ID');
            $this->accessKeySecret  = env('ALIYUN_ACCESS_KEY_SECRET');
    	    $this->endpoint  = env('ALIYUN_END_POINT');
            $this->bucket = env('ALIYUN_BUCKET');
    }

    public function index(Request $request, Topic $topic)
    {
//           $user = UserData::find(330616)->toArray();
//        print_r($user);
//        
//        echo "====";
//        dispatch(new UpdateMileageSort($user));
//        // 推送任务到队列
//        for($i=1;$i++;$i<100){
//            
//        }
        
       //$aa = Activity::latest()->first();
//	print_r($aa);

	   

           try {
		   $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
		   $object = "cyclingData/58a66322f8cdca21f735b491";
		   $objectMeta = $ossClient->getObjectMeta($this->bucket, $object);
		   //dd($objectMeta,$objectMeta['content-length']/1024,$objectMeta['info']['url']);
} catch (OssException $e) {
	print $e->getMessage();
}

//dd($ossClient);

            $topics = $topic->withOrder($request->order)
                    ->with('user', 'category')
                    ->paginate(20);
            $user = new User();
            $active_users = $user->getActiveUsers();
            
            $link = new Link();
            $links = $link->getAllCached();
            
            return view('topics.index', compact('topics','active_users','links'));
    }

    public function show(Request $request,Topic $topic)
    {
        // URL 矫正
        if ( ! empty($topic->slug) && $topic->slug != $request->slug) {
            return redirect($topic->link(), 301);
        }
        return view('topics.show', compact('topic'));
    }

    public function create(Topic $topic)
    {
        $categories = Category::all();
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }

    public function store(TopicRequest $request,Topic $topic)
    {
            $topic->fill($request->all());
            $topic->user_id = Auth::id();
            $topic->save();
            return redirect()->to($topic->link())->with('success', '帖子创建成功！');
    }
    
    public function edit(Topic $topic)
    {
         $this->authorize('update', $topic);
        $categories = Category::all();
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }

    public function update(TopicRequest $request, Topic $topic)
    {
            $this->authorize('update', $topic);
            $topic->update($request->all());

            return redirect()->to($topic->link())->with('success', '帖子更新成功！');
   
    }

    public function destroy(Topic $topic)
    {
           $this->authorize('destroy', $topic);
           $topic->delete();
           return redirect()->route('topics.index')->with('success', '成功删除！');
    }
    
    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        // 初始化返回数据，默认是失败的
        $data = [
            'success'   => false,
            'msg'       => '上传失败!',
            'file_path' => ''
        ];
        // 判断是否有上传文件，并赋值给 $file
        if ($file = $request->upload_file) {
            // 保存图片到本地
            $result = $uploader->save($file, 'topics', \Auth::id(), 1024);
            // 图片保存成功的话
            if ($result) {
                $data['file_path'] = $result['path'];
                $data['msg']       = "上传成功!";
                $data['success']   = true;
            }
        }
        return $data;
    }
}
