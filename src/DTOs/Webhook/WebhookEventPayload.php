<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Webhook;

use LumenSistemas\Asaas\DTOs\Bill\BillData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentData;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;

final readonly class WebhookEventPayload
{
    public function __construct(
        public string $id,
        public WebhookEvent $event,
        public ?PaymentData $payment = null,
        public ?BillData $bill = null,
    ) {}

    /**
     * @param array{
     *     id: string,
     *     event: string,
     *     payment?: null|array{
     *         id: string,
     *         customer: string,
     *         billingType: string,
     *         value: float|int,
     *         netValue: float|int,
     *         status: string,
     *         dueDate: string,
     *         deleted: bool,
     *         object?: null|string,
     *         dateCreated?: null|string,
     *         subscription?: null|string,
     *         installment?: null|string,
     *         checkoutSession?: null|string,
     *         paymentLink?: null|string,
     *         originalValue?: null|float|int,
     *         interestValue?: null|float|int,
     *         description?: null|string,
     *         originalDueDate?: null|string,
     *         paymentDate?: null|string,
     *         clientPaymentDate?: null|string,
     *         canBePaidAfterDueDate?: null|bool,
     *         externalReference?: null|string,
     *         invoiceUrl?: null|string,
     *         invoiceNumber?: null|string,
     *         nossoNumero?: null|string,
     *         bankSlipUrl?: null|string,
     *         transactionReceiptUrl?: null|string,
     *         creditDate?: null|string,
     *         estimatedCreditDate?: null|string,
     *         anticipated?: null|bool,
     *         anticipable?: null|bool,
     *         installmentNumber?: null|int,
     *         pixTransaction?: null|string,
     *         pixQrCodeId?: null|string,
     *         postalService?: null|bool,
     *         daysAfterDueDateToRegistrationCancellation?: null|int,
     *         discount?: null|array{value: float|int, dueDateLimitDays: int, type: 'FIXED'|'PERCENTAGE'},
     *         interest?: null|array{value: float|int},
     *         fine?: null|array{value: float|int, type?: null|'FIXED'|'PERCENTAGE'},
     *         split?: null|array<int, array<string, mixed>>,
     *         creditCard?: null|array<string, mixed>,
     *         chargeback?: null|array<string, mixed>,
     *         escrow?: null|array<string, mixed>,
     *         refunds?: null|array<string, mixed>,
     *         callback?: null|array<string, mixed>,
     *     },
     *     bill?: null|array{
     *         id: string,
     *         status: string,
     *         value: float|int,
     *         identificationField: string,
     *         dueDate: string,
     *         scheduleDate: string,
     *         canBeCancelled: bool,
     *         discount?: null|float|int,
     *         interest?: null|float|int,
     *         fine?: null|float|int,
     *         paymentDate?: null|string,
     *         fee?: null|float|int,
     *         description?: null|string,
     *         companyName?: null|string,
     *         transactionReceiptUrl?: null|string,
     *         externalReference?: null|string,
     *         failReasons?: list<string>,
     *     },
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            event: WebhookEvent::from($data['event']),
            payment: isset($data['payment']) ? PaymentData::fromArray($data['payment']) : null,
            bill: isset($data['bill']) ? BillData::fromArray($data['bill']) : null,
        );
    }

    public static function fromJson(string $json): self
    {
        /**
         * @var array{
         *     id: string,
         *     event: string,
         *     payment?: null|array{
         *         id: string,
         *         customer: string,
         *         billingType: string,
         *         value: float|int,
         *         netValue: float|int,
         *         status: string,
         *         dueDate: string,
         *         deleted: bool,
         *     },
         *     bill?: null|array{
         *         id: string,
         *         status: string,
         *         value: float|int,
         *         identificationField: string,
         *         dueDate: string,
         *         scheduleDate: string,
         *         canBeCancelled: bool,
         *     },
         * } $decoded
         */
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($decoded);
    }
}
