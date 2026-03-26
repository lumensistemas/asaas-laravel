<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Enums\Webhook;

enum WebhookSendType: string
{
    case Sequentially = 'SEQUENTIALLY';
    case NonSequentially = 'NON_SEQUENTIALLY';
}
