<?php

namespace Codeages\Biz\Framework\Pay\Payment;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Omnipay\Omnipay;

class WechatGetway extends AbstractGetway
{
    public function converterNotify($data)
    {
        $gateway = $this->createGetWay('WechatPay');
        $request = $gateway->completePurchase(array(
            'request_params' => $data
        ));
        $response = $request->send();
        $data = $request->getData();

        if ($response->isPaid()) {
            return array(
                array(
                    'status' => 'paid',
                    'cash_flow' => $data['transaction_id'],
                    'paid_time' => $this->timeConverter($data['time_end']),
                    'pay_amount' => $data['cash_fee'],
                    'cash_type' => $data['fee_type'],
                    'trade_sn' => $data['out_trade_no'],
                    'attach' => json_decode($data['attach'], true),
                    'notify_data' => $data,
                ),
                $this->getNotifyResponse()
            );
        }

        return array(
            array(
                'status' => 'failture',
                'notify_data' => $data,
            ),
            $this->getNotifyResponse()
        );
    }

    protected function timeConverter($time)
    {
        $year = substr($time, 0, 4);
        $month = substr($time, 4, 2);
        $day = substr($time, 6, 2);
        $hour = substr($time, 8, 2);
        $min = substr($time, 10, 2);
        $sec = substr($time, 12, 2);
        return strtotime("{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}");
    }

    protected function getNotifyResponse()
    {
        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }

    public function createTrade($data)
    {
        if (!ArrayToolkit::requireds($data, array(
            'pay_type',
            'goods_title',
            'goods_detail',
            'attach',
            'trade_sn',
            'amount',
            'notify_url',
            'create_ip',
            ))) {
            throw new InvalidArgumentException('trade args is invalid.');
        }

        if (!empty($data['pay_type']) && 'js' == $data['pay_type'] && empty($data['open_id'])) {
            throw new InvalidArgumentException('trade args is invalid.');
        }

        $payType = ucfirst($data['pay_type']);
        $gateway = $this->createGetWay("WechatPay_{$payType}");

        $order['body'] = $data['goods_title'];
        $order['detail'] = $data['goods_detail'];
        $order['attach'] = json_encode($data['attach']);
        $order['out_trade_no'] = $data['trade_sn'];
        $order['total_fee'] = $data['amount'];
        $order['notify_url'] = $data['notify_url'];
        $order['spbill_create_ip'] = $data['create_ip'];
        $order['fee_type'] = 'CNY';
        if ($data['pay_type'] == 'js') {
            $order['open_id'] = $data['open_id'];
        }

        $request  = $gateway->purchase($order);
        $response = $request->send();
        return $response->getData();
    }

    public function applyRefund($trade)
    {
        $payType = ucfirst($trade['platform']);
        $gateway = $this->createGetWay("WechatPay_{$payType}");

        $response = $gateway->refund([
            'transaction_id' => $trade['platform_sn'],
            'out_refund_no' => $trade['trade_sn'],
            'total_fee' => $trade['cash_amount'],
            'refund_fee' => $trade['cash_amount'],
        ])->send();

        if ($response->isSuccessful()) {
            return $response->getData();
        }


    }

    public function converterRefundNotify($data)
    {
        // TODO: Implement converterRefundNotify() method.
    }

    protected function createGetWay($type)
    {
        $config = $this->getSetting();
        $gateway = Omnipay::create($type);
        $gateway->setAppId($config['appid']);
        $gateway->setMchId($config['mch_id']);
        $gateway->setApiKey($config['key']);
        return $gateway;
    }

    protected function getSetting()
    {
        return array(
            'appid' => $this->biz['wx_app_id'],
            'mch_id' => $this->biz['wx_mch_id'],
            'key' => $this->biz['wx_mch_secret'],
        );
    }
}