<?php

namespace Vexura\Accounting;

use GuzzleHttp\Exception\GuzzleException;
use Vexura\Model\EmailTemplate as EmailTemplateModel;
use Vexura\Model\PaginatedResponse;
use Vexura\OpenProvider;

class EmailTemplate
{
    private OpenProvider $API;

    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * List email templates.
     *
     * @param int $limit
     * @param int $offset
     * @param int|null $id
     * @param string|null $name
     * @param string|null $group
     * @param bool|null $isSystem
     * @return PaginatedResponse
     * @phpstan-return PaginatedResponse<EmailTemplateModel>
     * @psalm-return PaginatedResponse<EmailTemplateModel>
     * @throws GuzzleException
     */
    public function listEmails(
        int $limit = 100,
        int $offset = 0,
        ?int $id = null,
        ?string $name = null,
        ?string $group = null,
        ?bool $isSystem = null
    ): PaginatedResponse {
        $params = array_filter([
            'limit' => $limit,
            'offset' => $offset,
            'id' => $id,
            'name' => $name,
            'group' => $group,
            'is_system' => $isSystem,
        ], static fn($v) => $v !== null);

        $response = $this->API->get('emails', $params);
        $items = [];
        foreach ($response['data']['results'] ?? [] as $row) {
            $items[] = (new EmailTemplateModel())->fromArray($row);
        }
        return new PaginatedResponse((int)($response['data']['total'] ?? 0), $items);
    }

    /**
     * Create email template.
     * @param array $data
     * @return int The created email template id.
     * @throws GuzzleException
     */
    public function createEmail(array $data): int
    {
        $response = $this->API->post('emails', $data);
        return (int)($response['data']['id'] ?? 0);
    }

    /**
     * Update email template.
     * @param int $id
     * @param array $data
     * @return bool
     * @throws GuzzleException
     */
    public function updateEmail(int $id, array $data): bool
    {
        $response = $this->API->put("emails/{$id}", $data);
        return (bool)($response['data']['success'] ?? false);
    }

    /**
     * Delete email template.
     * @param int $id
     * @return bool
     * @throws GuzzleException
     */
    public function deleteEmail(int $id): bool
    {
        $response = $this->API->delete("emails/{$id}");
        return (bool)($response['data']['success'] ?? false);
    }
}
