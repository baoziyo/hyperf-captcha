<?php

declare(strict_types=1);

return [
    'default' => [
        // 字符集
        'charset' => '123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvWwXxYyZz',
        // 生成位数
        'length' => 4,
        // 混淆曲线
        'confusionCurve' => false,
        // 随机噪点
        'randomNoise' => false,
        // 指定字体
        'useFont' => null,
        // 字体大小
        'fontSize' => 25,
        // 字体颜色
        'fontColor' => null,
        // 背景颜色
        'backgroundColor' => null,
        // 图片宽度
        'captchaWidth' => null,
        // 图片高度
        'captchaHeight' => null,
        // 额外字体
        'fonts' => [],
    ],
];
