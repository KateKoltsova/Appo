<?php

namespace App\Services\Contracts;

use Illuminate\Http\Client\Response as ClientResponse;

interface AuthTokenGenerator
{
    /**
     * Generate access and refresh tokens.
     *
     * @param array $data
     * @param string $type
     *
     * @return ClientResponse
     */
    public function generateTokens(array $data, string $type): ClientResponse;
}
