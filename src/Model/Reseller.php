<?php

declare(strict_types=1);

namespace Ashio\Model;

class Reseller
{
    public ?int $id = null;
    public ?string $companyName = null;
    public ?ContactAddress $address = null;
    public ?float $balance = null;
    public ?float $reservedBalance = null;
    public ?ResellerSettings $settings = null;
    public ?ResellerStatistics $statistics = null;
    public ?ResellerAdditionalData $additionalData = null;
    public ?string $comments = null;
    /** @var Contact[]|null */
    public ?array $contacts = null;
    public ?string $email = null;
    public ?CustomerFax $fax = null;
    public ?string $locale = null;
    public ?ContactName $name = null;
    public ?ContactPhone $phone = null;
    public ?string $status = null;
    public ?int $tier = null;
    public ?string $vat = null;
    public ?float $vatperc = null;

    /**

     * @param array<string, mixed> $data

     * @return self

     */
    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->companyName = $data['company_name'] ?? null;
        if (isset($data['address'])) {
            $this->address = (new ContactAddress())->fromArray($data['address']);
        }
        $this->balance = $data['balance'] ?? null;
        $this->reservedBalance = $data['reserved_balance'] ?? null;
        if (isset($data['settings'])) {
            $this->settings = (new ResellerSettings())->fromArray($data['settings']);
        }
        if (isset($data['statistics'])) {
            $this->statistics = (new ResellerStatistics())->fromArray($data['statistics']);
        }
        if (isset($data['additional_data'])) {
            $this->additionalData = (new ResellerAdditionalData())->fromArray($data['additional_data']);
        }
        $this->comments = $data['comments'] ?? null;
        if (isset($data['contacts'])) {
            $this->contacts = array_map(function ($contactData) {
                return (new Contact())->fromArray($contactData);
            }, $data['contacts']);
        }
        $this->email = $data['email'] ?? null;
        if (isset($data['fax'])) {
            $this->fax = (new CustomerFax())->fromArray($data['fax']);
        }
        $this->locale = $data['locale'] ?? null;
        if (isset($data['name'])) {
            $this->name = (new ContactName())->fromArray($data['name']);
        }
        if (isset($data['phone'])) {
            $this->phone = (new ContactPhone())->fromArray($data['phone']);
        }
        $this->status = $data['status'] ?? null;
        $this->tier = $data['tier'] ?? null;
        $this->vat = $data['vat'] ?? null;
        $this->vatperc = $data['vatperc'] ?? null;

        return $this;
    }

    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'company_name' => $this->companyName,
            'balance' => $this->balance,
            'reserved_balance' => $this->reservedBalance,
            'comments' => $this->comments,
            'email' => $this->email,
            'locale' => $this->locale,
            'status' => $this->status,
            'tier' => $this->tier,
            'vat' => $this->vat,
            'vatperc' => $this->vatperc,
        ];

        if ($this->address) {
            $data['address'] = $this->address->toArray();
        }

        if ($this->settings) {
            $data['settings'] = $this->settings->toArray();
        }

        if ($this->statistics) {
            $data['statistics'] = $this->statistics->toArray();
        }

        if ($this->additionalData) {
            $data['additional_data'] = $this->additionalData->toArray();
        }

        if ($this->contacts) {
            $data['contacts'] = array_map(function (Contact $contact) {
                return $contact->toArray();
            }, $this->contacts);
        }

        if ($this->fax) {
            $data['fax'] = $this->fax->toArray();
        }

        if ($this->name) {
            $data['name'] = $this->name->toArray();
        }

        if ($this->phone) {
            $data['phone'] = $this->phone->toArray();
        }

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }
}
