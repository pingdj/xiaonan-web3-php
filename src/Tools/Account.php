<?php
/**
 * 潇楠 Web3哨兵 PHP SDK - 账户工具（配额查询）
 */

namespace XiaoNan\Web3\Tools;

use XiaoNan\Web3\Client;
use XiaoNan\Web3\XiaoNanError;

class Account
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function quota()
    {
        $data = [
            'api_key' => $this->client->getApiKey(),
        ];

        $result = $this->client->_postForm('/key/v1/quota.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('配额查询失败', null, $result['error'] ?? '未知错误');
        }

        return [
            'total_quota' => $result['total_quota'],
            'used_quota'  => $result['used_quota'],
            'remaining'   => $result['remaining'],
        ];
    }
}