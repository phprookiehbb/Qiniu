<?php

/**
 * @Author: CraspHB彬
 * @Date:   2018-10-10 10:27:33
 * @Email:   646054215@qq.com
 * @Last Modified time: 2018-10-10 17:24:33
 */
namespace Crasphb;

require 'src/autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Cache;
use think\Request;
use think\Exception;

class Qiniu{

	// 用于签名的公钥和私钥
	protected $accessKey = '';
	protected $secretKey = '';
	protected $bucket = '';

	protected $auth;
    
    /**
     * 处置话变量并鉴权
     * @param string $accessKey [description]
     * @param string $secretKey [description]
     * @param string $bucket    [description]
     */
	public function __construct($accessKey = '',$secretKey = '',$bucket = ''){
        
        $config = [
        	'accessKey' => $accessKey,
        	'secretKey' => $secretKey,
        	'bucket' => $bucket,
        ];
        $qiniu = array_merge(config('qiniu') , array_filter($config));

		if(empty($qiniu['accessKey']) || empty($qiniu['secretKey'])){
        	throw new Exception('配置文件不正确，请检查配置',10001);
        }        

        $this->accessKey = $qiniu['accessKey'];
        $this->secretKey = $qiniu['secretKey'];
        $this->bucket = $qiniu['bucket'];

        try{
        	$this->auth = new Auth($this->accessKey , $this->secretKey);
        }catch(\Exception $e){
        	throw new Exception($e->getMessage(),10002);
        }
	}
    /**
     * 获取bucket
     * @return [type] [description]
     */
	public function getBucket(){
		return $this->bucket;
	}
	/**
	 * 设置bucket
	 * @param [type] $bucket [description]
	 */
	public function setBucket($bucket){
		$this->bucket = $bucket;
		Cache::set('upToken',null);
	}
	/**
	 * 文件上传
	 * @return [type] [description]
	 */
	public function upload($sign){

		$file = request()->file($sign);
	    if(!$file){
	    	throw new \Exception('请上传文件',10001);
	    }
	    $dir = ROOT_PATH . 'public/uploads';
	    if(!is_dir($dir)){
	    	mkdir($dir,0777,true);
	    }
        $info = $file->move($dir);
        if($info){
            $filePath = $dir . '/' .$info->getSaveName();
            $key = $info->getFilename(); 
			$uploadMgr = new UploadManager();
			$token = $this->getUpToken();
			// 调用 UploadManager 的 putFile 方法进行文件的上传。
			list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
			if($err !== null){
				throw new Exception('上传文件失败',10001);
			}   
			//删除本地图片
			@unlink($filePath);
			//返回图片名称key,用于拼接外链
			return $ret['key'];      
        }else{
            // 上传失败获取错误信息
           throw new Exception('上传文件失败',10001);
        }
	}
	/**
	 * 得到上传凭证
	 * @param  string $bucket  [空间名,为空代表配置中的值，填写标识更改bucket]
	 * @param  string $expires [过期时间]
	 * @return [type]          [description]
	 */
	public function getUpToken($bucket = '' , $expires = '3600'){
		$token = Cache::get('upToken');

		//从缓存中获取token
		if(!empty($token)){
			return $token;
		}
		if(empty($this->bucket) && empty($bucket)){
			throw new Exception('请先设置bucket',10001);
		}
		//更改bucket
		if(!empty($bucket)){
			$this->setBucket($bucket);
		}
		//获取upToken并设置缓存
		$upToken = $this->auth->uploadToken($this->bucket , null , $expires);
		Cache::set('upToken',$upToken,$expires);

		return $upToken;
	}
}
