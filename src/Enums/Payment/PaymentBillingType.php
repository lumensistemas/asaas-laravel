<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Enums\Payment;

/**
 * Billing type for a payment.
 *
 * Request (POST/PUT) accepts: UNDEFINED, BOLETO, CREDIT_CARD, PIX
 * Response may also include: DEBIT_CARD, TRANSFER, DEPOSIT
 */
enum PaymentBillingType: string
{
    case Undefined = 'UNDEFINED';
    case Boleto = 'BOLETO';
    case CreditCard = 'CREDIT_CARD';
    case Pix = 'PIX';
    case DebitCard = 'DEBIT_CARD';
    case Transfer = 'TRANSFER';
    case Deposit = 'DEPOSIT';
}
