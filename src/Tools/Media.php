<?php
/**
 * 潇楠 Web3哨兵 PHP SDK - 媒体处理工具（视频解析、AI数字人）
 */

namespace XiaoNan\Web3\Tools;

use XiaoNan\Web3\Client;
use XiaoNan\Web3\XiaoNanError;

class Media
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========== 视频解析 ==========
    public function videoParse($url)
    {
        $data = [
            'api_key' => $this->client->getApiKey(),
            'url'     => $url,
        ];

        $result = $this->client->_postForm('/key/v1/video_parse.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('视频解析失败', null, $result['error'] ?? '未知错误');
        }

        return $result;
    }

    // ========== AI数字人 V1（OmniHuman1.5）==========
    public function digitalHuman($text, $imageUrl, $voice = 'BV007_streaming')
    {
        $data = [
            'api_key'   => $this->client->getApiKey(),
            'text'      => $text,
            'image_url' => $imageUrl,
            'voice'     => $voice,
        ];

        $result = $this->client->_postForm('/key/v1/digital_human.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('数字人任务提交失败', null, $result['error'] ?? '未知错误');
        }

        return new DigitalHumanTask($this->client, $result['task_id'], 'v1');
    }

    // ========== AI数字人 V2（单图音频驱动）==========
    public function digitalHumanV2($text, $imageUrl, $voice = 'BV007_streaming')
    {
        $data = [
            'api_key'   => $this->client->getApiKey(),
            'text'      => $text,
            'image_url' => $imageUrl,
            'voice'     => $voice,
        ];

        $result = $this->client->_postForm('/key/v1/digital_human_volcv2.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('数字人V2任务提交失败', null, $result['error'] ?? '未知错误');
        }

        return new DigitalHumanTask($this->client, $result['task_id'], 'v2');
    }
}

/**
 * AI数字人视频生成任务，封装自动轮询逻辑
 */
class DigitalHumanTask
{
    private $client;
    private $taskId;
    private $apiType;

    public function __construct(Client $client, $taskId, $apiType = 'v1')
    {
        $this->client = $client;
        $this->taskId = $taskId;
        $this->apiType = $apiType;
    }

    public function wait($interval = 10, $timeout = 300)
    {
        $start = time();
        $path = $this->apiType === 'v1' ? '/key/v1/digital_human_check.php' : '/key/v1/digital_human_volcv2_check.php';
        $url = $this->client->getBaseUrl() . $path;

        while (true) {
            if (time() - $start > $timeout) {
                throw new XiaoNanError("数字人视频生成超时（{$timeout}秒），task_id={$this->taskId}");
            }

            $params = [
                'api_key' => $this->client->getApiKey(),
                'task_id' => $this->taskId,
            ];

            $ch = curl_init($url . '?' . http_build_query($params));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            if ($result === null) {
                throw new XiaoNanError('数字人查询返回了无法解析的响应');
            }

            if (($result['status'] ?? '') === 'done' && !empty($result['video_url'])) {
                return $result['video_url'];
            }
            if (($result['status'] ?? '') === 'failed') {
                throw new XiaoNanError('数字人视频生成失败');
            }

            sleep($interval);
        }
    }
}