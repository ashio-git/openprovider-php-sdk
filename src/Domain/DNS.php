<?php

namespace Ashio\Domain;

use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;
use Ashio\Model\DnsCreateTemplateRequest;
use Ashio\Model\DnsCreateZoneRequest;
use Ashio\Model\DnsRecord as DnsRecordModel;
use Ashio\Model\DnsTemplate;
use Ashio\Model\DnsUpdateZoneRequest;
use Ashio\Model\DomainToken;
use Ashio\Model\DomainTokenRequest;
use Ashio\Model\Nameserver;
use Ashio\Model\NameserverGroup;
use Ashio\Model\PaginatedResponse;
use Ashio\Model\Zone as ZoneModel;
use Ashio\OpenProvider;
use GuzzleHttp\Exception\GuzzleException;

class DNS
{
    private OpenProvider $API;

    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * Create a new domain token.
     * @param DomainTokenRequest $data
     * @return DomainToken
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createDomainToken(DomainTokenRequest $data): DomainToken
    {
        $response = $this->API->post('dns/domain-token', $data->toArray());

        return (new DomainToken())->fromArray(is_array($response) ? ($response['data'] ?? []) : []);
    }

    /**
     * List all nameservers with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $name
     * @param string|null $ip
     * @param string|null $ip6
     * @param string|null $pattern
     * @param string $order
     * @param string $order_by
     * @return \Ashio\Model\PaginatedResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<Nameserver>
     * @psalm-return \Ashio\Model\PaginatedResponse<Nameserver>
     */
    public function listNameservers(int $limit = 100, int $offset = 0, ?string $name = null, ?string $ip = null, ?string $ip6 = null, ?string $pattern = null, string $order = 'asc', string $order_by = 'name'): \Ashio\Model\PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'name' => $name,
            'ip' => $ip,
            'ip6' => $ip6,
            'pattern' => $pattern,
            'order' => $order,
            'order_by' => $order_by,
        ]);

        $response = $this->API->get('dns/nameservers', $params);
        $nameservers = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $nameserverData) {
                $nameservers[] = (new Nameserver())->fromArray($nameserverData);
            }
        }

        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $nameservers);
    }

    /**
     * Create a new nameserver.
     * @param Nameserver $nameserver
     * @return Nameserver
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createNameserver(Nameserver $nameserver): Nameserver
    {
        $response = $this->API->post('dns/nameservers', $nameserver->toArray());

        return (new Nameserver())->fromArray(is_array($response) ? ($response['data'] ?? []) : []);
    }

    /**
     * Get a specific nameserver by its name.
     * @param string $name
     * @return Nameserver
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getNameserver(string $name): Nameserver
    {
        $response = $this->API->get("dns/nameservers/{$name}");

        return (new Nameserver())->fromArray(is_array($response) ? ($response['data'] ?? []) : []);
    }

    /**
     * Update an existing nameserver.
     * @param string $name
     * @param Nameserver $nameserver
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateNameserver(string $name, Nameserver $nameserver): bool
    {
        $response = $this->API->put("dns/nameservers/{$name}", $nameserver->toArray());

        return $response['data']['success'] ?? false;
    }

    /**
     * Delete a nameserver.
     * @param string $name
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteNameserver(string $name): bool
    {
        $response = $this->API->delete("dns/nameservers/{$name}");

        return $response['data']['success'] ?? false;
    }

    /**
     * List all nameserver groups with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $nsGroupPattern
     * @return PaginatedResponse
     * @phpstan-return PaginatedResponse<NameserverGroup>
     * @psalm-return PaginatedResponse<NameserverGroup>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listNameserverGroups(int $limit = 100, int $offset = 0, ?string $nsGroupPattern = null): PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'ns_group_pattern' => $nsGroupPattern,
        ]);

        $response = $this->API->get('dns/nameservers/groups', $params);
        $groups = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $groupData) {
                $groups[] = (new NameserverGroup())->fromArray($groupData);
            }
        }

        return new PaginatedResponse((int)($response['data']['total'] ?? 0), $groups);
    }

    /**
     * Create a new nameserver group.
     * @param NameserverGroup $nameserverGroup
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createNameserverGroup(NameserverGroup $nameserverGroup): bool
    {
        $response = $this->API->post('dns/nameservers/groups', $nameserverGroup->toArray());

        return $response['data']['success'] ?? false;
    }

    /**
     * Get a specific nameserver group by its name.
     * @param string $nsGroup
     * @return NameserverGroup
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getNameserverGroup(string $nsGroup): NameserverGroup
    {
        $response = $this->API->get("dns/nameservers/groups/{$nsGroup}");

        return (new NameserverGroup())->fromArray(is_array($response) ? ($response['data'] ?? []) : []);
    }

    /**
     * Update an existing nameserver group.
     * @param string $nsGroup
     * @param NameserverGroup $nameserverGroup
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateNameserverGroup(string $nsGroup, NameserverGroup $nameserverGroup): bool
    {
        $response = $this->API->put("dns/nameservers/groups/{$nsGroup}", $nameserverGroup->toArray());

        return $response['data']['success'] ?? false;
    }

    /**
     * Delete a nameserver group.
     * @param string $nsGroup
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteNameserverGroup(string $nsGroup): bool
    {
        $response = $this->API->delete("dns/nameservers/groups/{$nsGroup}");

        return $response['data']['success'] ?? false;
    }

    /**
     * List all DNS templates with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $namePattern
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<DnsTemplate>
     * @psalm-return \Ashio\Model\PaginatedResponse<DnsTemplate>
     */
    public function listTemplates(int $limit = 100, int $offset = 0, ?string $namePattern = null): \Ashio\Model\PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'name_pattern' => $namePattern,
        ]);

        $response = $this->API->get('dns/templates', $params);
        $templates = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $templateData) {
                $templates[] = (new DnsTemplate())->fromArray($templateData);
            }
        }

        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $templates);
    }

    /**
     * Create a new DNS template.
     * @param DnsCreateTemplateRequest $request
     * @return \Ashio\Model\DnsTemplate
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createTemplate(DnsCreateTemplateRequest $request): DnsTemplate
    {
        $response = $this->API->post('dns/templates', $request->toArray());

        return $this->getTemplate(is_array($response) ? ($response['data']['id'] ?? 0) : 0);
    }

    /**
     * Get a specific DNS template by its ID.
     * @param int $id
     * @return DnsTemplate
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getTemplate(int $id): DnsTemplate
    {
        $response = $this->API->get("dns/templates/{$id}");

        return (new DnsTemplate())->fromArray(is_array($response) ? ($response['data'] ?? []) : []);
    }

    /**
     * Delete a DNS template.
     * @param int $id
     * @return \Ashio\Model\ActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteTemplate(int $id): \Ashio\Model\ActionResponse
    {
        $response = $this->API->delete("dns/templates/{$id}");

        return (new \Ashio\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * List all DNS zones with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $namePattern
     * @return \Ashio\Model\PaginatedResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\PaginatedResponse
     * @phpstan-return \Ashio\Model\PaginatedResponse<ZoneModel>
     * @psalm-return \Ashio\Model\PaginatedResponse<ZoneModel>
     */
    public function listZones(int $limit = 100, int $offset = 0, ?string $namePattern = null): \Ashio\Model\PaginatedResponse
    {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'name_pattern' => $namePattern,
        ]);

        $response = $this->API->get('dns/zones', $params);
        $zones = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $zoneData) {
                $zones[] = new ZoneModel($zoneData);
            }
        }

        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $zones);
    }

    /**
     * Create a new DNS zone.
     * @param DnsCreateZoneRequest $request
     * @return \Ashio\Model\DnsActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createZone(DnsCreateZoneRequest $request): \Ashio\Model\DnsActionResponse
    {
        $response = $this->API->post('dns/zones', $request->toArray());

        return (new \Ashio\Model\DnsActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Get a specific DNS zone by its name.
     * @param string $name
     * @param array<string, mixed> $params
     * @return ZoneModel
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function getZone(string $name, array $params = []): ZoneModel
    {
        $response = $this->API->get("dns/zones/{$name}", $params);

        return new ZoneModel($response['data'] ?? []);
    }

    /**
     * Update an existing DNS zone.
     * @param string $name
     * @param DnsUpdateZoneRequest $request
     * @return \Ashio\Model\DnsActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Ashio\Model\DnsActionResponse
     */
    public function updateZone(string $name, DnsUpdateZoneRequest $request): \Ashio\Model\DnsActionResponse
    {
        $response = $this->API->put("dns/zones/{$name}", $request->toArray());

        return (new \Ashio\Model\DnsActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Delete a DNS zone.
     * @param string $name
     * @param array<string, mixed> $params
     * @return \Ashio\Model\DnsActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteZone(string $name, array $params = []): \Ashio\Model\DnsActionResponse
    {
        $response = $this->API->delete("dns/zones/{$name}", $params);

        return (new \Ashio\Model\DnsActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * List all DNS records for a zone.
     * @param string $name
     * @param array<string, mixed> $params
     * @return \Ashio\Model\PaginatedResponse<DnsRecordModel>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listZoneRecords(string $name, array $params = []): \Ashio\Model\PaginatedResponse
    {
        $response = $this->API->get("dns/zones/{$name}/records", $params);
        $records = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $recordData) {
                $records[] = new DnsRecordModel($recordData);
            }
        }

        return new \Ashio\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $records);
    }
}
