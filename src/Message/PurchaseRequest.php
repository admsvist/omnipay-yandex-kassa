<?php
/**
 * Yandex.Kassa driver for Omnipay payment processing library
 *
 * @link      https://github.com/hiqdev/omnipay-yandex-kassa
 * @package   omnipay-yandex-kassa
 * @license   MIT
 * @copyright Copyright (c) 2019, HiQDev (http://hiqdev.com/)
 */

namespace Omnipay\YandexKassa\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Throwable;

/**
 * Class PurchaseRequest.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class PurchaseRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('amount', 'currency', 'transactionId', 'description');

        return [
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'description' => $this->getDescription(),
            'return_url' => $this->getReturnUrl(),
            'transactionId' => $this->getTransactionId(),
            'paymentToken' => $this->getPaymentToken(),
        ];
    }

    public function sendData($data)
    {
        $fields = array_filter([
            'amount' => [
                'value' => $data['amount'],
                'currency' => $data['currency'],
            ],
            'description' => $data['description'],
            'metadata' => [
                'transactionId' => $data['transactionId'],
            ],
            'payment_token' => $data['paymentToken'],
            'confirmation' => $data['return_url'] ? [
                'type' => 'redirect',
                'return_url' => $data['return_url'],
            ] : false,
        ]);

        try {
            $paymentResponse = $this->client->createPayment($fields, $this->makeIdempotencyKey());

            return $this->response = new PurchaseResponse($this, $paymentResponse);
        } catch (Throwable $e) {
            throw new InvalidRequestException('Failed to request purchase: ' . $e->getMessage(), 0, $e);
        }
    }

    private function makeIdempotencyKey(): string
    {
        return md5(implode(',', array_merge(['create'], $this->getData())));
    }
}
