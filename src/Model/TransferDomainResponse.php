<?php

namespace Ashio\Model;

class TransferDomainResponse
{
    /**
     * @var string|null
     */
    public $auth_code;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $error;

    /**
     * @var string|null
     */
    public $expiration_date;

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $renewal_date;

    /**
     * @var bool|null
     */
    public $required;

    /**
     * @var string|null
     */
    public $status;

    /**
     * @var string|null
     */
    public $type;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->auth_code = $data['auth_code'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->error = $data['error'] ?? null;
        $this->expiration_date = $data['expiration_date'] ?? null;
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->renewal_date = $data['renewal_date'] ?? null;
        $this->required = $data['required'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->type = $data['type'] ?? null;

        return $this;
    }
}
