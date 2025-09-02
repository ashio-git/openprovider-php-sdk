<?php

namespace Vexura\Model;

/**
 * Class SslOrder
 * A data transfer object for an SSL order.
 */
class SslOrder
{
    public ?int $id = null;
    public ?string $common_name = null;
    public ?string $status = null;
    public ?string $order_date = null;
    public ?string $expiration_date = null;
    public ?int $product_id = null;
    public ?string $product_name = null;
    public ?string $brand_name = null;
    public ?string $active_date = null;
    public ?string $autorenew = null;
    public ?string $billing_handle = null;
    public ?string $technical_handle = null;
    public ?string $organization_handle = null;
    /** @var string[]|null */
    public ?array $host_names = null;
    public ?string $certificate = null;
    public ?string $csr = null;
    public ?string $email_approver = null;
    public ?string $email_reissue = null;
    /** @var SslOrderDomainValidationMethods[]|null */
    public ?array $domain_validation_methods = null;
    public ?SslOrderDomainValidationStatuses $domain_validation_statuses = null;
    public ?SslOrderOptions $options = null;
    /** @var SslOrderAdditionalData[]|null */
    public ?array $additional_data = null;

    /**
     * SslOrder constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->fromArray($data);
        }
    }

    /**
     * @param array<string,mixed> $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        if (isset($data['domain_validation_methods'])) {
            $this->domain_validation_methods = array_map(static fn($row) => (new SslOrderDomainValidationMethods())->fromArray($row), $data['domain_validation_methods']);
        }
        if (isset($data['domain_validation_statuses'])) {
            $this->domain_validation_statuses = (new SslOrderDomainValidationStatuses())->fromArray($data['domain_validation_statuses']);
        }
        if (isset($data['options'])) {
            $this->options = (new SslOrderOptions())->fromArray($data['options']);
        }
        if (isset($data['additional_data'])) {
            $this->additional_data = array_map(static fn($row) => (new SslOrderAdditionalData())->fromArray($row), $data['additional_data']);
        }
        return $this;
    }
}
