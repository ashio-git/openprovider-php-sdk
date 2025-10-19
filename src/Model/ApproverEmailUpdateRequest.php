<?php

declare(strict_types=1);

namespace Ashio\Model;

class ApproverEmailUpdateRequest
{
    public string $approver_email;

    public function __construct(string $approverEmail)
    {
        $this->approver_email = $approverEmail;
    }

    /**
     * @return array<string,mixed>
     */
    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        return [
            'approver_email' => $this->approver_email,
        ];
    }
}
