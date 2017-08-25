<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Pay\Payment\AlipayInTimeGetway;
use Codeages\Biz\Framework\Pay\Payment\WechatGetway;
use Codeages\Biz\Framework\Pay\Status\ClosedStatus;
use Codeages\Biz\Framework\Pay\Status\ClosingStatus;
use Codeages\Biz\Framework\Pay\Status\PaidStatus;
use Codeages\Biz\Framework\Pay\Status\PayingStatus;
use Codeages\Biz\Framework\Pay\Status\PaymentTradeContext;
use Codeages\Biz\Framework\Pay\Status\RefundedStatus;
use Codeages\Biz\Framework\Pay\Status\RefundingStatus;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class PayServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/pay';
        $biz['autoload.aliases']['Pay'] = 'Codeages\Biz\Framework\Pay';

        $this->registerStatus($biz);
        $this->registerPayments($biz);
    }

    protected function registerPayments($biz)
    {
        $paymentPlatforms = array(
            'wechat' => array(
                'class' => WechatGetway::class,
                'icon' => '',
                'appid' => '',
                'mch_id' => '',
                'key' => '',
                'cert_path' => '',
                'key_path' => '',
            ),
            'alipay.in_time' => array(
                'class' => AlipayInTimeGetway::class,
                'icon' => '',
                'seller_email' => '',
                'partner' => '',
                'key' => '',
            ),
        );

        $biz['payment.platforms'] = $paymentPlatforms;

        foreach ($biz['payment.platforms'] as $key => $platform) {
            $biz["payment.{$key}"] = function () use ($platform) {
                return new $platform['class']($this);
            };
        }
    }


    private function registerStatus($biz)
    {
        $biz['payment_trade_context'] = function ($biz) {
            return new PaymentTradeContext($biz);
        };

        $statusArray = array(
            PayingStatus::class,
            ClosingStatus::class,
            ClosedStatus::class,
            PaidStatus::class,
            RefundingStatus::class,
            RefundedStatus::class,
        );

        foreach ($statusArray as $status) {
            $biz['payment_trade.'.$status::NAME] = function ($biz) use ($status) {
                return new $status($biz);
            };
        }
    }
}
