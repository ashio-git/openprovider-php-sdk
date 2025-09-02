<?php

namespace Ashio\Accounting;

use GuzzleHttp\Exception\GuzzleException;
use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\OpenProvider;
use Ashio\Model\Contact;
use Ashio\Model\Customer as CustomerModel;
use Ashio\Model\Reseller as ResellerModel;
use Ashio\Model\Tag;
use Ashio\Model\DomainEmailVerification;
use Ashio\Model\PaginatedResponse;

class ResellerCustomer
{
    private OpenProvider $API;

    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * List all contacts with optional filters.
     * @param int $limit The number of contacts to return.
     * @param int $offset The offset to start from.
     * @param string|null $emailPattern A pattern to filter by email address.
     * @param string|null $lastNamePattern A pattern to filter by last name.
     * @param string|null $companyNamePattern A pattern to filter by company name.
     * @return array{total: int, results: array<int, Contact>} An array containing the total number of contacts and a list of Contact objects.
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<Contact>
     * @psalm-return \Ashio\Model\PaginatedResponse<Contact>
     */
    public function listContacts(int $limit = 100, int $offset = 0, ?string $emailPattern = null, ?string $lastNamePattern = null, ?string $companyNamePattern = null): \Ashio\Model\PaginatedResponse
    {
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        if ($emailPattern) {
            $params['email_pattern'] = $emailPattern;
        }

        if ($lastNamePattern) {
            $params['last_name_pattern'] = $lastNamePattern;
        }

        if ($companyNamePattern) {
            $params['company_name_pattern'] = $companyNamePattern;
        }

        $response = $this->API->get('contacts', $params);
        $contacts = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $contactData) {
                $contacts[] = (new Contact())->fromArray($contactData);
            }
        }
        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $contacts);
    }

    /**
     * Create a new contact.
     * @param Contact $contact The contact object to create.
     * @return Contact
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createContact(Contact $contact): Contact
    {
        $response = $this->API->post('contacts', $contact->toArray());
        return $this->getContact($response['data']['id']);
    }

    /**
     * Get a specific contact by its ID.
     * @param int $id The ID of the contact.
     * @return Contact
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getContact(int $id): Contact
    {
        $response = $this->API->get("contacts/{$id}");
        return (new Contact())->fromArray($response['data']);
    }

    /**
     * Update an existing contact.
     * @param int $id The ID of the contact to update.
     * @param Contact $contact The contact object with updated data.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateContact(int $id, Contact $contact): bool
    {
        $response = $this->API->put("contacts/{$id}", $contact->toArray());
        return $response['data']['success'] ?? false;
    }

    /**
     * Delete a contact.
     * @param int $id The ID of the contact to delete.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteContact(int $id): bool
    {
        $response = $this->API->delete("contacts/{$id}");
        return $response['data']['success'] ?? false;
    }

    /**
     * List all customers with optional filters.
     *
     * @param int|null $resellerId
     * @param int $limit
     * @param int $offset
     * @param string|null $order
     * @param string|null $orderBy
     * @param string|null $emailPattern
     * @param string|null $companyNamePattern
     * @param string|null $lastNamePattern
     * @param string|null $firstNamePattern
     * @param string|null $commentPattern
     * @param string|null $handlePattern
     * @param string|null $pattern
     * @param bool|null $withAdditionalData
     * @param string|null $type
     * @param array|null $columns
     * @return array{total: int, results: array<int, CustomerModel>}
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listCustomers(
        ?int $resellerId = null,
        int $limit = 100,
        int $offset = 0,
        ?string $order = null,
        ?string $orderBy = null,
        ?string $emailPattern = null,
        ?string $companyNamePattern = null,
        ?string $lastNamePattern = null,
        ?string $firstNamePattern = null,
        ?string $commentPattern = null,
        ?string $handlePattern = null,
        ?string $pattern = null,
        ?bool $withAdditionalData = null,
        ?string $type = null,
        ?array $columns = null
    ): \Ashio\Model\PaginatedResponse {
        $params = array_filter(compact(
            'resellerId', 'limit', 'offset', 'order', 'orderBy', 'emailPattern',
            'companyNamePattern', 'lastNamePattern', 'firstNamePattern', 'commentPattern',
            'handlePattern', 'pattern', 'withAdditionalData', 'type', 'columns'
        ));

        $response = $this->API->get('customers', $params);
        $customers = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $customerData) {
                $customers[] = (new CustomerModel())->fromArray($customerData);
            }
        }
        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $customers);
    }

    /**
     * Create a new customer.
     * @param CustomerModel $customer The customer object to create.
     * @return CustomerModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createCustomer(CustomerModel $customer): CustomerModel
    {
        $response = $this->API->post('customers', $customer->toArray());
        return $this->getCustomer($response['data']['handle']);
    }

    /**
     * Get a specific customer by its handle.
     * @param string $handle The handle of the customer.
     * @param array $params Optional parameters.
     * @return CustomerModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getCustomer(string $handle, array $params = []): CustomerModel
    {
        $response = $this->API->get("customers/{$handle}", $params);
        return (new CustomerModel())->fromArray($response['data'] ?? []);
    }

    /**
     * Update an existing customer.
     * @param string $handle The handle of the customer to update.
     * @param CustomerModel $customer The customer object with updated data.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateCustomer(string $handle, CustomerModel $customer): bool
    {
        $response = $this->API->put("customers/{$handle}", $customer->toArray());
        return $response['data']['success'] ?? false;
    }

    /**
     * Delete a customer.
     * @param string $handle The handle of the customer to delete.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteCustomer(string $handle): bool
    {
        $response = $this->API->delete("customers/{$handle}");
        return $response['data']['success'] ?? false;
    }

    /**
     * List all domain email verifications with optional filters.
     *
     * @param int|null $domain_id
     * @param string|null $domain
     * @param string|null $email
     * @param string|null $handle
     * @param string|null $status
     * @param string|null $description
     * @param bool|null $is_suspended
     * @param string|null $response_type
     * @param string|null $response_to
     * @param int $limit
     * @param int $offset
     * @return PaginatedResponse
     * @phpstan-return PaginatedResponse<DomainEmailVerification>
     * @psalm-return PaginatedResponse<DomainEmailVerification>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listDomainEmailVerifications(
        ?int $domain_id = null,
        ?string $domain = null,
        ?string $email = null,
        ?string $handle = null,
        ?string $status = null,
        ?string $description = null,
        ?bool $is_suspended = null,
        ?string $response_type = null,
        ?string $response_to = null,
        int $limit = 100,
        int $offset = 0
    ): PaginatedResponse {
        $params = array_filter(compact(
            'domain_id', 'domain', 'email', 'handle', 'status', 'description',
            'is_suspended', 'response_type', 'response_to', 'limit', 'offset'
        ));

        $response = $this->API->get('customers/verifications/emails/domains', $params);
        $verifications = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $verificationData) {
                $verifications[] = (new DomainEmailVerification())->fromArray($verificationData);
            }
        }
        return new PaginatedResponse((int)($response['data']['total'] ?? 0), $verifications);
    }

    /**
     * Restart the email verification process.
     * @param string $email The email address to restart verification for.
     * @param string|null $language The language for the verification email.
     * @param string|null $tag A tag to associate with the verification.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function restartEmailVerification(string $email, ?string $language = null, ?string $tag = null): bool
    {
        $params = array_filter(compact('email', 'language', 'tag'));
        $response = $this->API->post('customers/verifications/emails/restart', $params);
        return $response['data']['success'] ?? false;
    }

    /**
     * Start the email verification process.
     * @param string $email The email address to start verification for.
     * @param string|null $handle The customer handle to associate with the verification.
     * @param string|null $language The language for the verification email.
     * @param string|null $tag A tag to associate with the verification.
     * @return int
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function startEmailVerification(string $email, ?string $handle = null, ?string $language = null, ?string $tag = null): int
    {
        $params = array_filter(compact('email', 'handle', 'language', 'tag'));
        $response = $this->API->post('customers/verifications/emails/start', $params);
        return $response['data']['id'];
    }

    /**
     * Get the reseller information.
     *
     * @param bool|null $withAdditionalData
     * @param bool|null $withStatistics
     * @param bool|null $withSettings
     * @param bool|null $withContacts
     * @return ResellerModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getReseller(
        ?bool $withAdditionalData = null,
        ?bool $withStatistics = null,
        ?bool $withSettings = null,
        ?bool $withContacts = null
    ): ResellerModel {
        $params = array_filter(compact(
            'withAdditionalData', 'withStatistics', 'withSettings', 'withContacts'
        ));

        $response = $this->API->get('resellers', $params);
        return (new ResellerModel())->fromArray($response['data'] ?? []);
    }

    /**
     * Update the reseller information.
     * @param int $id The ID of the reseller.
     * @param ResellerModel $reseller The reseller object with updated data.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateReseller(int $id, ResellerModel $reseller): bool
    {
        $response = $this->API->put("resellers/{$id}", $reseller->toArray());
        return $response['data']['success'] ?? false;
    }

    /**
     * Get the reseller settings.
     * @return ResellerSettings
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getSettings(): ResellerSettings
    {
        $response = $this->API->get('resellers/settings');
        return (new ResellerSettings())->fromArray($response['data'] ?? []);
    }

    /**
     * Get the reseller statistics.
     * @return ResellerStatistics
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getStatistics(): ResellerStatistics
    {
        $response = $this->API->get('resellers/statistics');
        return (new ResellerStatistics())->fromArray($response['data'] ?? []);
    }

    /**
     * List all tags with optional filters.
     *
     * @param string|null $key
     * @param string|null $value
     * @return PaginatedResponse
     * @phpstan-return PaginatedResponse<Tag>
     * @psalm-return PaginatedResponse<Tag>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listTags(?string $key = null, ?string $value = null): PaginatedResponse
    {
        $params = array_filter(compact('key', 'value'));
        $response = $this->API->get('tags', $params);
        $tags = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $tagData) {
                $tags[] = (new Tag())->fromArray($tagData);
            }
        }
        return new PaginatedResponse((int)($response['data']['total'] ?? 0), $tags);
    }

    /**
     * Create a new tag.
     * @param Tag $tag The tag to create.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createTag(Tag $tag): bool
    {
        $response = $this->API->post('tags', $tag->toArray());
        return $response['data']['success'] ?? false;
    }

    /**
     * Delete a tag.
     * @param string $key The key of the tag to delete.
     * @param string|null $value The value of the tag to delete.
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteTag(string $key, ?string $value = null): bool
    {
        $params = array_filter(compact('key', 'value'));
        $response = $this->API->delete('tags', $params);
        return $response['data']['success'] ?? false;
    }
}
