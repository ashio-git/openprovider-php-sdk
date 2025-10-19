<?php

declare(strict_types=1);

namespace Ashio\Model;

class CsrDecodeRequest
{
    public string $csr;

    public function __construct(string $csr)
    {
        $this->csr = $csr;
    }

    /**
     * @return array{csr:string}
     */
    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        return ['csr' => $this->csr];
    }
}
