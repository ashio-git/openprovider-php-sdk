<?php

namespace Vexura\Domain;

use GuzzleHttp\Exception\GuzzleException;
use Vexura\Model\SpamExpertDomain;
use Vexura\Model\SpamExpertCreateDomainRequest;
use Vexura\OpenProvider;

class SpamExpert
{
    private OpenProvider $API;
    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * Create SpamExpert domain
     * @param SpamExpertCreateDomainRequest $request
     * @return bool
     * @throws GuzzleException
     */
    public function createDomain(SpamExpertCreateDomainRequest $request): bool
    {
        $response = $this->API->post('spam-expert/domains', $request->toArray());
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Get SpamExpert domain details
     * @param string $domainName
     * @param bool $withRecords
     * @param bool|null $bundle
     * @return SpamExpertDomain
     * @throws GuzzleException
     */
    public function getDomain(string $domainName, bool $withRecords = false, ?bool $bundle = null): SpamExpertDomain
    {
        $params = ['with_records' => $withRecords];
        if ($bundle !== null) {
            $params['bundle'] = $bundle;
        }
        $response = $this->API->get("spam-expert/domains/{$domainName}", $params);
        return (new SpamExpertDomain())->fromArray($response['data'] ?? []);
    }

    /**
     * Update SpamExpert domain
     * @param string $domainName
     * @param array $data
     * @return bool
     * @throws GuzzleException
     */
    public function updateDomain(string $domainName, array $data): bool
    {
        $response = $this->API->put("spam-expert/domains/{$domainName}", $data);
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Delete SpamExpert domain
     * @param string $domainName
     * @param bool|null $bundle
     * @return bool
     * @throws GuzzleException
     */
    public function deleteDomain(string $domainName, ?bool $bundle = null): bool
    {
        $params = [];
        if ($bundle !== null) {
            $params['bundle'] = $bundle;
        }
        $response = $this->API->delete("spam-expert/domains/{$domainName}", $params);
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Generate SpamExpert login URL
     * @param array $data
     * @return string
     * @throws GuzzleException
     */
    public function generateLoginUrl(array $data): string
    {
        $response = $this->API->post("spam-expert/generate-login-url", $data);
        return (string)($response['data']['url'] ?? '');
    }
}
