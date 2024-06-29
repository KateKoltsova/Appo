<?php

namespace App\Services\Contracts;

interface PayService
{
    public function getHtml(int $total, int $orderId, string $expired_at, string $resultUrl, string $description): string;

    public function getResponse(int $orderId): mixed;
}
