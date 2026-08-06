<?php
/**
 * 潇楠 Web3哨兵 PHP SDK - 数据获取工具（网页提取、快讯、特朗普动态）
 */

namespace XiaoNan\Web3\Tools;

use XiaoNan\Web3\Client;
use XiaoNan\Web3\XiaoNanError;

class Data
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========== 网页内容提取 ==========
    public function extract($url, $mode = 'basic')
    {
        $data = [
            'api_key' => $this->client->getApiKey(),
            'url'     => $url,
            'mode'    => $mode,
        ];

        $result = $this->client->_postForm('/key/v1/extract.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('网页提取失败', null, $result['error'] ?? '未知错误');
        }

        return $result;
    }

    // ========== 实时 Web3 快讯 ==========
    public function newsflash($category = 'all', $keyword = '', $page = 1, $size = 10, $lang = 'zh-cn')
    {
        $data = [
            'api_key'  => $this->client->getApiKey(),
            'category' => $category,
            'page'     => $page,
            'size'     => $size,
            'lang'     => $lang,
        ];
        if (!empty($keyword)) {
            $data['keyword'] = $keyword;
        }

        // 加入短暂延迟和重试机制
        usleep(600000); // 0.6 秒延迟
        $maxRetries = 3;
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $result = $this->client->_postForm('/key/v1/newsflash.php', $data);
                if (empty($result['success'])) {
                    throw new XiaoNanError('快讯获取失败', null, $result['error'] ?? '未知错误');
                }
                return $result;
            } catch (XiaoNanError $e) {
                if ($attempt < $maxRetries - 1 && $e->getStatusCode() == 530) {
                    sleep(2);
                    continue;
                }
                throw $e;
            }
        }
    }

    // ========== 特朗普动态追踪 ==========
    public function trumpFeed($action = 'get')
    {
        $data = [
            'api_key' => $this->client->getApiKey(),
            'action'  => $action,
        ];

        $result = $this->client->_postForm('/key/v1/trump_feed.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('特朗普动态获取失败', null, $result['error'] ?? '未知错误');
        }

        return $result;
    }
}