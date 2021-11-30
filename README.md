验证码组件
------
用于生产验证码，支持自定义验证码字体，使用Composer安装

```
composer require baoziyoo/hyperf-captcha

php bin/hyperf.php vendor:publish baoziyoo/hyperf-captcha
```

生成
------
初始化配置后即可生成验证码，可以随机生成，也可以指定需要生成的验证码

```
$captcha = new Captcha();

// 随机生成验证码
$captcha = $captcha->generateCode();

// 生成指定验证码
$captcha = $captcha->generateCode('MyCode');
```
