# qiniu
基于ThinkPHP5框架的扩展，主要用户七牛云文件上传。

## Installation
qiniu is available on [Packagist](https://packagist.org/packages/phprookiehbb/qiniu).Just add this line to your `composer.json` file:

```json
"phprookiehbb/qiniu": "dev-master"
```

or run

```sh
composer require phprookiehbb/qiniu
```
### Config

```
首先需要在配置文件中添加如下配置：
'qiniu' => [
      'accessKey' => '',
      'secretKey' => '',
      'bucket' => ''
 ]
 或者在创建Qiniu时传入配置也可以
 例子：
 new Qiniu($accessKey = '',$secretKey = '',$bucket = '')
```

### Example

``` php
use Crasphb\Qiniu;
$qiniu = new Qiniu();
$res = $qiniu->upload();
```