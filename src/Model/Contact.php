<?php

declare(strict_types=1);

namespace Ashio\Model;

class Contact
{
    /**
     * @var int|null The unique identifier for the contact.
     */
    public ?int $id = null;

    /**
     * @var int|null The reseller's unique identifier.
     */
    public ?int $resellerId = null;

    /**
     * @var string|null The contact's username.
     */
    public ?string $username = null;

    /**
     * @var string|null The contact's password (only present in specific contexts).
     */
    public ?string $password = null;

    /**
     * @var string|null The contact's email address.
     */
    public ?string $email = null;

    /**
     * @var ContactName|null The name of the contact.
     */
    public ?ContactName $name = null;

    /**
     * @var string|null The name of the company associated with the contact.
     */
    public ?string $companyName = null;

    /**
     * @var ContactAddress|null The address of the contact.
     */
    public ?ContactAddress $address = null;

    /**
     * @var ContactPhone|null The phone number of the contact.
     */
    public ?ContactPhone $phone = null;

    /**
     * @var string|null The VAT number of the contact.
     */
    public ?string $vat = null;

    /**
     * @var string|null The role of the contact (e.g., 'admin', 'tech').
     */
    public ?string $role = null;

    /**
     * @var string|null The locale preference of the contact (e.g., 'en_US').
     */
    public ?string $locale = null;

    /**
     * @var bool|null Whether the contact is active.
     */
    public ?bool $isActive = null;

    /**
     * @var string|null Any comments or notes associated with the contact.
     */
    public ?string $comments = null;

    /**
     * @var bool|null Whether API access is enabled for the contact.
     */
    public ?bool $apiAccessEnabled = null;

    /**
     * @var ContactClientIpList|null A list of IP addresses for API access control.
     */
    public ?ContactClientIpList $apiClientIpList = null;

    /**
     * @var string|null The authentication type used by the contact.
     */
    public ?string $authType = null;

    /**
     * @var string|null The timestamp of the last login.
     */
    public ?string $lastLoginAt = null;

    /**
     * @var string|null The timestamp of the last API call.
     */
    public ?string $lastApiCallAt = null;

    /**
     * @var string|null The timestamp of the last password change.
     */
    public ?string $passwordChangedAt = null;

    /**
     * @var string|null The timestamp when a password change was declined.
     */
    public ?string $passwordChangeDeclinedAt = null;

    /**
     * @var string|null The timestamp of the last hash change.
     */
    public ?string $hashChangedAt = null;

    /**
     * @var string|null The secret key for the contact.
     */
    public ?string $secretKey = null;

    /**
     * @var ContactClientIpList|null A list of IP addresses for RCP access control.
     */
    public ?ContactClientIpList $rcpClientIpList = null;

    /**
     * @var ContactAdditionalData|null Additional data associated with the contact.
     */
    public ?ContactAdditionalData $additionalData = null;

    /**
     * Populates the Contact object from an array.
     *
     * @param array<string, mixed> $data The data to populate from.
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->resellerId = $data['reseller_id'] ?? null;
        $this->username = $data['username'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->email = $data['email'] ?? null;
        if (isset($data['name'])) {
            $this->name = (new ContactName())->fromArray($data['name']);
        }
        $this->companyName = $data['company_name'] ?? null;
        if (isset($data['address'])) {
            $this->address = (new ContactAddress())->fromArray($data['address']);
        }
        if (isset($data['phone'])) {
            $this->phone = (new ContactPhone())->fromArray($data['phone']);
        }
        $this->vat = $data['vat'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->isActive = $data['is_active'] ?? null;
        $this->comments = $data['comments'] ?? null;
        $this->apiAccessEnabled = $data['api_access_enabled'] ?? null;
        if (isset($data['api_client_ip_list'])) {
            $this->apiClientIpList = (new ContactClientIpList())->fromArray($data['api_client_ip_list']);
        }
        $this->authType = $data['auth_type'] ?? null;
        $this->lastLoginAt = $data['last_login_at'] ?? null;
        $this->lastApiCallAt = $data['last_api_call_at'] ?? null;
        $this->passwordChangedAt = $data['password_changed_at'] ?? null;
        $this->passwordChangeDeclinedAt = $data['password_change_declined_at'] ?? null;
        $this->hashChangedAt = $data['hash_changed_at'] ?? null;
        $this->secretKey = $data['secret_key'] ?? null;
        if (isset($data['rcp_client_ip_list'])) {
            $this->rcpClientIpList = (new ContactClientIpList())->fromArray($data['rcp_client_ip_list']);
        }
        if (isset($data['additional_data'])) {
            $this->additionalData = (new ContactAdditionalData())->fromArray($data['additional_data']);
        }
        return $this;
    }

    /**
     * Converts the Contact object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'reseller_id' => $this->resellerId,
            'username' => $this->username,
            'password' => $this->password,
            'email' => $this->email,
            'company_name' => $this->companyName,
            'vat' => $this->vat,
            'role' => $this->role,
            'locale' => $this->locale,
            'is_active' => $this->isActive,
            'comments' => $this->comments,
            'api_access_enabled' => $this->apiAccessEnabled,
            'auth_type' => $this->authType,
            'last_login_at' => $this->lastLoginAt,
            'last_api_call_at' => $this->lastApiCallAt,
            'password_changed_at' => $this->passwordChangedAt,
            'password_change_declined_at' => $this->passwordChangeDeclinedAt,
            'hash_changed_at' => $this->hashChangedAt,
            'secret_key' => $this->secretKey,
        ];

        if ($this->name) {
            $data['name'] = $this->name->toArray();
        }

        if ($this->address) {
            $data['address'] = $this->address->toArray();
        }

        if ($this->phone) {
            $data['phone'] = $this->phone->toArray();
        }

        if ($this->apiClientIpList) {
            $data['api_client_ip_list'] = $this->apiClientIpList->toArray();
        }

        if ($this->rcpClientIpList) {
            $data['rcp_client_ip_list'] = $this->rcpClientIpList->toArray();
        }

        if ($this->additionalData) {
            $data['additional_data'] = $this->additionalData->toArray();
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
