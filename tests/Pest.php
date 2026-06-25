<?php

declare(strict_types=1);

use LumenSistemas\Asaas\Tests\TestCase;

pest()
    ->extends(TestCase::class)
    ->in('Feature');

pest()
    ->extends(LumenSistemas\Asaas\Tests\IntegrationTestCase::class)
    ->in('Integration')
    ->group('integration');
