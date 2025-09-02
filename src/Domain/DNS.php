<?php

namespace Vexura\Domain;

use GuzzleHttp\Exception\GuzzleException;
use Vexura\Exception\ApiException;
use Vexura\Exception\AuthenticationException;
use Vexura\Exception\NotFoundException;
use Vexura\Exception\ValidationException;
use Vexura\OpenProvider;
use Vexura\Model\DnsTemplate;
use Vexura\Model\Nameserver;
use Vexura\Model\NameserverGroup;
use Vexura\Model\Zone as ZoneModel;
use Vexura\Model\DnsRecord as DnsRecordModel;

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
        return (new DomainToken())->fromArray($response['data']);
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
     * @return \Vexura\Model\PaginatedResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<Nameserver>
     * @psalm-return \Vexura\Model\PaginatedResponse<Nameserver>
     */
    public function listNameservers(int $limit = 100, int $offset = 0, ?string $name = null, ?string $ip = null, ?string $ip6 = null, ?string $pattern = null, string $order = 'asc', string $order_by = 'name'): \Vexura\Model\PaginatedResponse
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
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $nameservers);
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
        return (new Nameserver())->fromArray($response['data']);
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
        return (new Nameserver())->fromArray($response['data']);
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
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function listNameserverGroups(int $limit = 100, int $offset = 0, ?string $nsGroupPattern = null): array
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
        return [
            'total' => $response['data']['total'] ?? 0,
            'results' => $groups,
        ];
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
        return (new NameserverGroup())->fromArray($response['data']);
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
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<DnsTemplate>
     * @psalm-return \Vexura\Model\PaginatedResponse<DnsTemplate>
     */
    public function listTemplates(int $limit = 100, int $offset = 0, ?string $namePattern = null): \Vexura\Model\PaginatedResponse
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
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $templates);
    }

    /**
     * Create a new DNS template.
     * @param array $data
     * @return \Vexura\Model\DnsTemplate
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createTemplate(array $data): DnsTemplate
    {
        $response = $this->API->post('dns/templates', $data);
        return $this->getTemplate($response['data']['id']);
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
        return (new DnsTemplate())->fromArray($response['data']);
    }

    /**
     * Delete a DNS template.
     * @param int $id
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function deleteTemplate(int $id): \Vexura\Model\ActionResponse
    {
        $response = $this->API->delete("dns/templates/{$id}");
        return (new \Vexura\Model\ActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * List all DNS zones with optional filters.
     * @param int $limit
     * @param int $offset
     * @param string|null $namePattern
     * @return \Vexura\Model\PaginatedResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<ZoneModel>
     * @psalm-return \Vexura\Model\PaginatedResponse<ZoneModel>
     */
    public function listZones(int $limit = 100, int $offset = 0, ?string $namePattern = null): \Vexura\Model\PaginatedResponse
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
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $zones);
    }

    /**
     * Create a new DNS zone.
     * @param array $data
     * @return \Vexura\Model\DnsActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\DnsActionResponse
     */
    public function createZone(array $data): \Vexura\Model\DnsActionResponse
    {
        $response = $this->API->post('dns/zones', $data);
        return (new \Vexura\Model\DnsActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Get a specific DNS zone by its name.
     * @param string $name
     * @param array $params
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
     * @param array $data
     * @return \Vexura\Model\DnsActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\DnsActionResponse
     */
    public function updateZone(string $name, array $data): \Vexura\Model\DnsActionResponse
    {
        $response = $this->API->put("dns/zones/{$name}", $data);
        return (new \Vexura\Model\DnsActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * Delete a DNS zone.
     * @param string $name
     * @param array $params
     * @return \Vexura\Model\DnsActionResponse
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\DnsActionResponse
     */
    public function deleteZone(string $name, array $params = []): \Vexura\Model\DnsActionResponse
    {
        $response = $this->API->delete("dns/zones/{$name}", $params);
        return (new \Vexura\Model\DnsActionResponse())->fromArray($response['data'] ?? []);
    }

    /**
     * List all DNS records for a zone.
     * @param string $name
     * @param array $params
     * @return array
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ValidationException
     */
    /**
     * @return \Vexura\Model\PaginatedResponse
     * @phpstan-return \Vexura\Model\PaginatedResponse<DnsRecordModel>
     * @psalm-return \Vexura\Model\PaginatedResponse<DnsRecordModel>
     */
    public function listZoneRecords(string $name, array $params = []): \Vexura\Model\PaginatedResponse
    {
        $response = $this->API->get("dns/zones/{$name}/records", $params);
        $records = [];
        if (isset($response['data']['results'])) {
            foreach ($response['data']['results'] as $recordData) {
                $records[] = new DnsRecordModel($recordData);
            }
        }
        return new \Vexura\Model\PaginatedResponse((int)($response['data']['total'] ?? 0), $records);
    }
}
