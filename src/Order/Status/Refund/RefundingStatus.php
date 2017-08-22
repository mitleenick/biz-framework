<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefundingStatus extends AbstractRefundStatus
{
    const NAME = 'refunding';

    public function getPriorStatus()
    {
        return array(AuditingStatus::NAME);
    }

    public function refunded($data = array())
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'status' => RefundedStatus::NAME
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $updatedOrderItemRefunds = array();
        foreach ($orderItemRefunds as $orderItemRefund) {
            $updatedOrderItemRefunds[] = $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => RefundedStatus::NAME
            ));

            $this->getOrderItemDao()->update($orderItemRefund['order_item_id'], array(
                'refund_status' => RefundedStatus::NAME
            ));
        }

        $orderRefund['orderItemRefunds'] = $updatedOrderItemRefunds;
        return $orderRefund;
    }
}