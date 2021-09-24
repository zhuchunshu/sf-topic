<?php


namespace App\Plugins\Topic\src\Controllers;

use App\Plugins\Topic\src\Models\Topic;
use App\Plugins\Topic\src\Models\TopicKeyword;
use App\Plugins\Topic\src\Models\TopicLike;
use App\Plugins\Topic\src\Models\TopicTag;
use App\Plugins\User\src\Models\User;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Utils\Arr;

#[Controller(prefix:"/api/topic")]
class ApiController
{
    #[PostMapping(path:"tags")]
    public function Tags(): array
    {
        $data = [];
        foreach (TopicTag::query()->get() as $key=>$value){
            $data = Arr::add($data,$key,[
                "text"=>$value->name,
                "value" => $value->id,
                "icons" => "&lt;span class=&quot;avatar avatar-xs&quot; style=&quot;background-image: url($value->icon)&quot;&gt;&lt;/span&gt;"
                ]);
        }
        return $data;
    }

    #[PostMapping(path:"keywords")]
    public function topic_keyword(): array
    {
        $data = TopicKeyword::query()->select('name','id')->get();
        $arr = [];
        foreach ($data as $key=>$value){
            $arr = Arr::add($arr,$key,["value"=>"$[".$value->name."]","html" => $value->name]);
        }
        return $arr;
    }

    #[PostMapping(path:"with_topic.data")]
    public function get_WithTopic_Data(){
        $topic_id = request()->input("topic_id");
        if(!$topic_id){
            return Json_Api(403,false,['请求的 帖子id不存在']);
        }
        if(!Topic::query()->where("id",$topic_id)->exists()) {
            return Json_Api(404,false,['ID为:'.$topic_id.'帖子不存在']);
        }

        $data = Topic::query()->where("id",$topic_id)->select("id","title","user_id","options","created_at")->with("user")->first();
        $user_avatar = super_avatar($data->user);
        $title = \Hyperf\Utils\Str::limit($data->title,20);
        $username = $data->user->username;
        $summary = \Hyperf\Utils\Str::limit(core_default(deOptions($data->options)["summary"],"未捕获到本文摘要"),40);
        return Json_Api(200,true,[
            "avatar" => $user_avatar,
            "title" => $title,
            "summary" => $summary,
            "username" => $username
        ]);
    }

    #[PostMapping("like.topic")]
    public function like_topic(){

        if(!auth()->check()){
            return Json_Api(403,false,['msg' => '未登录!']);
        }

        $topic_id = request()->input("topic_id");
        if(!$topic_id){
            return Json_Api(403,false,['msg' => '请求参数:topic_id 不存在!']);
        }
        if(!Topic::query()->where('id',$topic_id)->exists()) {
            return Json_Api(403,false,['msg' => 'id为:'.$topic_id."的帖子不存在"]);
        }
        if(TopicLike::query()->where(['topic_id'=>$topic_id,'user_id'=>auth()->id(),"type" => 'like'])->exists()) {
            return Json_Api(403,false,['msg' => '您赞过这个帖子了,无需重复点赞!']);
        }
        TopicLike::query()->create([
            "topic_id" => $topic_id,
            "user_id" => auth()->id(),
        ]);
        Topic::query()->where(['id'=>$topic_id])->increment("like");
        return Json_Api(200,true,['msg' =>'已赞!']);
    }

    #[PostMapping(path:"topic.data")]
    public function topic_data(){
        if(!auth()->check()){
            return Json_Api(403,false,['msg' => '未登录!']);
        }
        $topic_id = request()->input("topic_id");
        if(!$topic_id){
            return Json_Api(403,false,['msg' => '请求参数不足,缺少:topic_id']);
        }
        if(!Topic::query()->where("id",$topic_id)->exists()){
            return Json_Api(403,false,['msg' => 'id为:'.$topic_id."的帖子不存在"]);
        }
        $data = Topic::query()->where("id",$topic_id)
            ->with("user","tag")
            ->first();
        $options = deOptions($data->options);
        $data['options'] = $options;
        return Json_Api(200,true,$data);
    }
}