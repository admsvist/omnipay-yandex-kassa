<?php

namespace Omnipay\YandexKassa\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Throwable;

class PurchaseTokenRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('paymentToken', 'amount', 'currency', 'transactionId', 'description');

        return [
            'payment_token' => $this->getPaymentToken(),
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'description' => $this->getDescription(),
            'transactionId' => $this->getTransactionId(),
        ];
    }

    public function sendData($data)
    {
        try {
            $paymentResponse = $this->client->createPayment([
                'payment_token' => $data['payment_token'],
                'amount' => [
                    'value' => $data['amount'],
                    'currency' => $data['currency'],
                ],
                'description' => $data['description'],
                'metadata' => [
                    'transactionId' => $data['transactionId'],
                ],
            ], $this->makeIdempotencyKey());

            return $this->response = new PurchaseTokenResponse($this, $paymentResponse);
        } catch (Throwable $e) {
            throw new InvalidRequestException('Failed to request purchase: ' . $e->getMessage(), 0, $e);
        }
    }

    private function makeIdempotencyKey(): string
    {
        return md5(implode(',', array_merge(['create'], $this->getData())));
    }
}
