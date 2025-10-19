<?php

declare(strict_types=1);

namespace Ashio\Model;

class Customer
{
    public ?int $id = null;
    public ?string $handle = null;
    public ?string $companyName = null;
    public ?string $email = null;
    public ?ContactAddress $address = null;
    public ?ContactPhone $phone = null;
    public ?ContactName $name = null;
    public ?string $vat = null;
    public ?string $locale = null;
    public ?bool $isDeleted = null;
    public ?string $deletedAt = null;
    public ?int $resellerId = null;
    public ?CustomerAdditionalData $additionalData = null;
    /** @var CustomerTag[]|null */
    public ?array $tags = null;
    public ?string $comments = null;
    public ?string $commentsLastChangedAt = null;
    public ?string $emailVerificationStatus = null;
    /** @var CustomerExtensionAdditionalData[]|null */
    public ?array $extensionAdditionalData = null;
    public ?CustomerFax $fax = null;
    public ?string $inn = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->handle = $data['handle'] ?? null;
        $this->companyName = $data['company_name'] ?? null;
        $this->email = $data['email'] ?? null;
        if (isset($data['address'])) {
            $this->address = (new ContactAddress())->fromArray($data['address']);
        }
        if (isset($data['phone'])) {
            $this->phone = (new ContactPhone())->fromArray($data['phone']);
        }
        if (isset($data['name'])) {
            $this->name = (new ContactName())->fromArray($data['name']);
        }
        $this->vat = $data['vat'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->isDeleted = $data['is_deleted'] ?? null;
        $this->deletedAt = $data['deleted_at'] ?? null;
        $this->resellerId = $data['reseller_id'] ?? null;
        if (isset($data['additional_data'])) {
            $this->additionalData = (new CustomerAdditionalData())->fromArray($data['additional_data']);
        }
        if (isset($data['tags'])) {
            $this->tags = array_map(function ($tagData) {
                return (new CustomerTag())->fromArray($tagData);
            }, $data['tags']);
        }
        $this->comments = $data['comments'] ?? null;
        $this->commentsLastChangedAt = $data['comments_last_changed_at'] ?? null;
        $this->emailVerificationStatus = $data['email_verification_status'] ?? null;
        if (isset($data['extension_additional_data'])) {
            $this->extensionAdditionalData = array_map(function ($extData) {
                return (new CustomerExtensionAdditionalData())->fromArray($extData);
            }, $data['extension_additional_data']);
        }
        if (isset($data['fax'])) {
            $this->fax = (new CustomerFax())->fromArray($data['fax']);
        }
        $this->inn = $data['inn'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'handle' => $this->handle,
            'company_name' => $this->companyName,
            'email' => $this->email,
            'vat' => $this->vat,
            'locale' => $this->locale,
            'is_deleted' => $this->isDeleted,
            'deleted_at' => $this->deletedAt,
            'reseller_id' => $this->resellerId,
            'comments' => $this->comments,
            'comments_last_changed_at' => $this->commentsLastChangedAt,
            'email_verification_status' => $this->emailVerificationStatus,
            'inn' => $this->inn,
        ];

        if ($this->address) {
            $data['address'] = $this->address->toArray();
        }

        if ($this->phone) {
            $data['phone'] = $this->phone->toArray();
        }

        if ($this->name) {
            $data['name'] = $this->name->toArray();
        }

        if ($this->additionalData) {
            $data['additional_data'] = $this->additionalData->toArray();
        }

        if ($this->tags) {
            $data['tags'] = array_map(function (CustomerTag $tag) {
                return $tag->toArray();
            }, $this->tags);
        }

        if ($this->extensionAdditionalData) {
            $data['extension_additional_data'] = array_map(function (CustomerExtensionAdditionalData $extData) {
                return $extData->toArray();
            }, $this->extensionAdditionalData);
        }

        if ($this->fax) {
            $data['fax'] = $this->fax->toArray();
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
