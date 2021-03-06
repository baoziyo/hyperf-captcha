<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Baoziyoo\HyperfCaptcha;

use Hyperf\Contract\ConfigInterface;

class Captcha
{
    protected $config;

    protected $defaultConfig = [
        'charset' => '123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvWwXxYyZz',
        'length' => 4,
        'confusionCurve' => false,
        'randomNoise' => false,
        'useFont' => null,
        'fontSize' => 25,
        'fontColor' => null,
        'backgroundColor' => [255, 255, 255],
        'captchaWidth' => null,
        'captchaHeight' => null,
        'fonts' => [
            __DIR__ . '/assets/ttf/1.ttf',
            __DIR__ . '/assets/ttf/2.ttf',
            __DIR__ . '/assets/ttf/3.ttf',
            __DIR__ . '/assets/ttf/4.ttf',
            __DIR__ . '/assets/ttf/5.ttf',
            __DIR__ . '/assets/ttf/6.ttf',
        ],
    ];

    public function __construct($name = 'default')
    {
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        $config = $container->get(ConfigInterface::class);
        $configKey = sprintf('captcha.%s', $name);
        if (! $config->has($configKey)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $configKey));
        }

        $this->config = $this->defaultConfig + $config->get($configKey, []);
        $this->buildDefaultConfig();
        $this->config['useFont'] || $this->config['useFont'] = $this->config['fonts'][array_rand($this->config['fonts'])];
    }

    public function buildDefaultConfig()
    {
        $length = $this->config['length'];
        $fontSize = $this->config['fontSize'];

        $this->config['useFont'] = null;
        $this->config['captchaWidth'] = (int) ($length * $fontSize * 1.5 + $fontSize / 2);
        $this->config['captchaHeight'] = $fontSize * 2;
        $this->config['fontColor'] = [random_int(1, 150), random_int(1, 150), random_int(1, 150)];
    }

    public function generateCode($code = null)
    {
        if (! is_null($code)) {
            $length = strlen($code);
            $fontSize = $this->config['fontSize'];
            $this->config['length'] = $length;
            $this->config['captchaWidth'] || $this->config['captchaWidth'] = (int) ($length * $fontSize * 1.5 + $fontSize / 2);
            $this->config['captchaHeight'] || $this->config['captchaHeight'] = $fontSize * 2;
        } else {
            $code = substr(str_shuffle($this->config['charset']), 0, $this->config['length']);
        }

        // ??????????????????
        $image = imagecreate((int) $this->config['captchaWidth'], (int) $this->config['captchaHeight']);
        // ??????????????????
        $backgroundColor = imagecolorallocate($image, ...$this->config['backgroundColor']);
        // ??????????????????
        $fontColor = imagecolorallocate($image, ...$this->config['fontColor']);
        // ???????????????
        $this->writeRandomNoise($image);
        // ???????????????
        $this->writeConfusionCurve($image);

        // ????????????
        $leftLength = 0; // ????????????N?????????????????????
        foreach (str_split($code) as $char) {
            $leftLength += random_int((int) ($this->config['fontSize'] * 1.2), (int) ($this->config['fontSize'] * 1.4));
            imagettftext($image, $this->config['fontSize'], random_int(-50, 50), $leftLength, (int) ($this->config['fontSize'] * 1.5), $fontColor, $this->config['useFont'], $char);
        }

        ob_start();
        imagepng($image);
        $file = ob_get_clean();
        imagedestroy($image);

        return [
            'image' => $file,
            'code' => $code,
            'mime' => 'png',
            'base64' => 'data:png;base64,' . base64_encode($file),
        ];
    }

    private function writeRandomNoise(&$image): void
    {
        if (! $this->config['randomNoise']) {
            return;
        }

        $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
        for ($i = 0; $i < 10; ++$i) {
            $noiseColor = imagecolorallocate($image, random_int(150, 225), random_int(150, 225), random_int(150, 225));
            for ($j = 0; $j < 5; ++$j) {
                imagestring($image, 5, random_int(-10, $this->config['captchaWidth']), random_int(-10, $this->config['captchaHeight']), $codeSet[random_int(0, 29)], $noiseColor);
            }
        }
    }

    private function writeConfusionCurve(&$image): void
    {
        if (! $this->config['confusionCurve']) {
            return;
        }

        $py = 0;
        // ???????????????
        $A = random_int(1, $this->config['captchaHeight'] / 2); // ??????
        $b = random_int(-$this->config['captchaHeight'] / 4, $this->config['captchaHeight'] / 4); // Y??????????????????
        $f = random_int(-$this->config['captchaHeight'] / 4, $this->config['captchaHeight'] / 4); // X??????????????????
        $T = random_int($this->config['captchaHeight'], $this->config['captchaWidth'] * 2); // ??????
        $w = (2 * M_PI) / $T;
        $px1 = 0; // ???????????????????????????
        $px2 = random_int($this->config['captchaWidth'] / 2, $this->config['captchaWidth'] * 0.8); // ???????????????????????????
        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w !== 0) {
                $py = $A * sin($w * $px + $f) + $b + $this->config['captchaHeight'] / 2; // y = Asin(??x+??) + b
                $i = (int) ($this->config['fontSize'] / 5);
                while ($i > 0) {
                    imagesetpixel($image, $px + $i, $py + $i, $this->config['fontColor']);
                    --$i;
                }
            }
        }
        // ???????????????
        $A = random_int(1, $this->config['captchaHeight'] / 2); // ??????
        $f = random_int(-$this->config['captchaHeight'] / 4, $this->config['captchaHeight'] / 4); // X??????????????????
        $T = random_int($this->config['captchaHeight'], $this->config['captchaWidth'] * 2); // ??????
        $w = (2 * M_PI) / $T;
        $b = $py - $A * sin($w * $px + $f) - $this->config['captchaHeight'] / 2;
        $px1 = $px2;
        $px2 = $this->config['captchaWidth'];
        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w !== 0) {
                $py = $A * sin($w * $px + $f) + $b + $this->config['captchaHeight'] / 2; // y = Asin(??x+??) + b
                $i = (int) ($this->config['fontSize'] / 5);
                while ($i > 0) {
                    imagesetpixel($image, $px + $i, $py + $i, $this->config['fontColor']);
                    --$i;
                }
            }
        }
    }
}
