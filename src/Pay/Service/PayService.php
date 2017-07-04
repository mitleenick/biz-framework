<?php

namespace Codeages\Biz\Framework\Pay\Service;

interface PayService
{
    public function findEnabledPayments();

    public function createTrade($trade);

    public function notify($payment, $data);
}