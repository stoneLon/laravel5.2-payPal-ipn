# laravel5.2-payPal-ipn
laravel5.2 for PayPal ipn<br />
https://github.com/stoneLon/laravel5.2-payPal-ipn
安装
===
```composer
composer require "stonelon/paypal-ipn:@dev"
```
在providers添加
```php
StoneLon\PaypalIpn\PaypalIpnServiceProvider::class,
```
在aliases添加
```php
'PaypalIpn' => \StoneLon\PaypalIpn\PaypalIpn::class,
```

使用
===
```php
$ipn = new PaypalIpn();
$ipn->use_sandbox = true;   //开启沙盒模式
if ($ipn->validateIpn($request->all())) {   //验证
    //do something
    //验证成功
    //通过$request->all()可获取返回的信息
    //注意判断返回金额是否和请求金额一致，防止用户修改金额
}
```
