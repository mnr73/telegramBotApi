<?php
namespace miladnazari\telegramBotApi;


class api{

    /*
    * $token -> bot token --- string
    * $getres -> get response --- bool - true or false
    * $conSec -> time out for connection is second --- ins
    * $resSec -> time out for response is second --- int
    */
    protected $token;
    protected $getres;
    protected $conSec;
    protected $resSec;
    /*
    * $param -> parameter of request. --- array
    * $action -> method of request. --- string
    */
    protected $param = array();
    protected $action;
    /*
    * $update -> get update in std class
    */
    protected $update;
    /*
    * $replay -> defult replay markup
    */
    protected $replay = [];
    /*
    * $error -> error messages
    */
    protected $error = "telegram bot api log\n";
    
    public function __construct($token = false, $getres = true, $conSec = 10, $resSec = 50){
        $this->token = $token;
        $this->getres = $getres;
        $this->conSec = $conSec;
        $this->resSec = $resSec;
    }
    
    function __destruct() {
        if(class_exists('\Log')){
            \Log::debug($this->error);
        }else{
            error_log($this->error);
        }
    }
    /*
    * change token -> this good for multi bot manage
    */
    public function changeToken($token){
        $this->token = $token;
        return $this;
    }
    /*
    * chage get result option
    */
    public function getres($getres){
        if(is_bool($getres))
            $this->getres = $getres;
        else{
            $this->error = "--- getres for response must be bool\n";
            return $this->error;
        }
        return $this;
    }
    /*
    * chage connection time out option
    */
    public function conSec($conSec){
        if(is_numeric($conSec))
            $this->conSec = $conSec;
        else{
            $this->error = "--- connection secend must be int\n";
            return $this->error;
        }
        return $this;
    }
    /*
    * chage response time out option
    */
    public function resSec($resSec){
        if(is_numeric($resSec))
            $this->resSec = $resSec;
        else{
            $this->error = "--- responce secend must be int\n";
            return $this->error;
        }
        return $this;
    }
    /*
    * set action -> method of telegram api
    */
    public function action($action){
        if(is_string($action))
            $this->action = $action;
        else{
            $this->error = "--- method must be string\n";
            return $this->error;
        }
        return $this;
    }
    /*
    * set parameters of telegram api
    */
    public function param($param){
        if(is_array($param)){
            if(isset($param['reply_markup'])){
                $param['reply_markup'] = json_encode($param['reply_markup']);
            }elseif(!empty($this->replay)){
                $param['reply_markup'] = json_encode($this->replay);
            }
            $this->param = $param;
        }
        else{
            $this->error = "--- parameter must be array\n";
            return $this->error;
        }
        return $this;
    }
    /*
    * set defult replay markup
    */
    public function setReplay($keys){
        if($keys === false) $this->replay = [];
        elseif(is_array($keys)) $this->replay = $keys;
        return $this;
    }
    /*
    * send requet to teleram server
    */
    public function shoot($getres = 100){
        $getres = $getres === 100 ? $this->getres : $getres;
        $url = 'https://api.telegram.org/bot'.$this->token.'/'.$this->action.'?'.http_build_query($this->param);
        
        $ch = curl_init();
        $option = array(
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => $this->conSec,
            CURLOPT_TIMEOUT => $this->resSec,
            CURLOPT_RETURNTRANSFER => $getres,
        );
        curl_setopt_array($ch, $option);
        $res = curl_exec($ch);
        curl_close($ch);
        if($res === false){
            $this->error = "--- shoot problem -> $url\n";
            http_response_code(500);
            return $this->error;
        }elseif($getres){
            return json_decode($res);
        }else{
            return true;
        }
    }
    /*
    * get shoot url
    */
    public function getShoot(){
        return $url = 'https://api.telegram.org/bot'.$this->token.'/'.$this->action.'?'.http_build_query($this->param);
    }
    /*
    * get bot id
    */
    public function botId(){
        return explode(':',$this->token)[0];
    }
    /*
    * get updates
    */
    public function getUpdate(){
        if(empty($this->update)){
            $this->update = file_get_contents('php://input');
            if(empty($this->update))
                return false;
            else
                $this->update = json_decode($this->update);
        }
        return $this->update;
    }
    /*
    * get text or caption
    * $msg = Message type
    */
    public function textOrCap($msg){
        if(isset($msg->text)){
            return $msg->text;
        }elseif(isset($msg->caption)){
            return $msg->caption;
        }else{
            return "";
        }
    }
    /*
    * get type and file id
    * $msg = Message type
    */
    public function typeOf($msg){
        $res = new stdClass();
        $doc = &$msg->document;
        if(isset($msg->text)){
            $res->type = 'text';
            $content = trim(preg_replace('/\s+/', ' ', $msg->text));
            $content = mb_substr($content,0,60);
            $res->fid = $content;
        }
        elseif(isset($msg->sticker))
        {
            $res->type = 'sticker';
            $res->fid = $msg->sticker->file_id;
        }
        elseif(isset($msg->photo))
        {
            $res->type = 'photo';
            $res->fid = end($msg->photo)->file_id;
        }
        elseif(isset($msg->voice))
        {
            $res->type = 'voice';
            $res->fid = $msg->voice->file_id;
        }
        elseif(isset($msg->audio))
        {
            $res->type = 'audio';
            $res->fid = $msg->audio->file_id;
        }
        elseif(isset($msg->video))
        {
            $res->type = 'video';
            $res->fid = $msg->video->file_id;
        }
        elseif(isset($msg->video_note))
        {
            $res->type = 'video_note';
            $res->fid = $msg->video_note->file_id;
        }
        elseif(isset($doc->mime_type))
        {
            $mtpart = explode('/',$doc->mime_type);
            if( $doc->mime_type == 'video/mp4' && @$doc->thumb->width < 100 )
            {
                $res->doctype = 'gif';
            }
            elseif($mtpart[0] == 'video') 
            {
                $res->doctype = 'video';
            }
            elseif($mtpart[0] == 'image') 
            {
                $res->doctype = 'photo';
            }
            elseif($mtpart[0] == 'application' && $mtpart[1] == 'vnd.android.package-archive') 
            {
                $res->doctype = 'apk';
            }
            $res->type = 'document';
            $res->fid = $msg->document->file_id;
        }
        elseif(isset($msg->game))
        {
            $res->type = 'game';
            $res->fid = $msg->game->title;
        }
        elseif(isset($msg->contact))
        {
            $res->type = 'contact';
            $res->fid = $msg->contact->phone_number;
        }
        elseif(isset($msg->location))
        {
            $res->type = 'location';
            $res->fid = $msg->location->longitude.'-'.$msg->location->latitude;
        }
        
        return $res;
    }
}