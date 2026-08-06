<?php
/**
 * 潇楠 Web3哨兵 PHP SDK - 内容生成工具（TTS、播客、AI 创作）
 */

namespace XiaoNan\Web3\Tools;

use XiaoNan\Web3\Client;
use XiaoNan\Web3\XiaoNanError;

class Generation
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========== TTS 文本转语音 ==========
    public function tts($text, $voice = 'BV007_streaming')
    {
        $data = [
            'api_key' => $this->client->getApiKey(),
            'text'    => $text,
            'voice'   => $voice,
        ];

        $result = $this->client->_postForm('/key/v1/tts.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('TTS 失败', null, $result['error'] ?? '未知错误');
        }

        return $result['audio_url'];
    }

    // ========== 双人语音播客 ==========
    public function podcast($text = '', $action = 0, $inputUrl = '', $promptText = '',
                            $speaker1 = 'zh_male_dayixiansheng_v2_saturn_bigtts',
                            $speaker2 = 'zh_female_mizaitongxue_v2_saturn_bigtts',
                            $audioFormat = 'mp3', $useHeadMusic = false, $useTailMusic = false)
    {
        $data = [
            'api_key'        => $this->client->getApiKey(),
            'action'         => $action,
            'speaker1'       => $speaker1,
            'speaker2'       => $speaker2,
            'audio_format'   => $audioFormat,
            'use_head_music' => $useHeadMusic ? 1 : 0,
            'use_tail_music' => $useTailMusic ? 1 : 0,
        ];
        if (!empty($text)) {
            $data['text'] = $text;
        }
        if (!empty($inputUrl)) {
            $data['input_url'] = $inputUrl;
        }
        if (!empty($promptText)) {
            $data['prompt_text'] = $promptText;
        }

        $result = $this->client->_postForm('/key/v1/podcast.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('播客任务提交失败', null, $result['error'] ?? '未知错误');
        }

        return new PodcastTask($this->client, $result['task_id']);
    }

    // ========== AI 一键创作 ==========
    public function article($keywords, $modelIndex = 0, $style = 'professional',
                            $length = 'standard', $webSearchSerpApi = false, $webSearchSerper = false)
    {
        $data = [
            'api_key'           => $this->client->getApiKey(),
            'keywords'          => $keywords,
            'model_index'       => $modelIndex,
            'style'             => $style,
            'length'            => $length,
            'web_search_serpapi' => $webSearchSerpApi ? 1 : 0,
            'web_search_serper'  => $webSearchSerper ? 1 : 0,
        ];

        $result = $this->client->_postForm('/key/v1/article.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('AI 创作失败', null, $result['error'] ?? '未知错误');
        }

        return [
            'title'   => $result['title'] ?? '',
            'content' => $result['content'] ?? '',
            'excerpt' => $result['excerpt'] ?? '',
            'tags'    => $result['tags'] ?? '',
        ];
    }
}

/**
 * 播客任务，封装自动轮询
 */
class PodcastTask
{
    private $client;
    private $taskId;

    public function __construct(Client $client, $taskId)
    {
        $this->client = $client;
        $this->taskId = $taskId;
    }

    public function wait($interval = 10, $timeout = 120)
    {
        $start = time();
        $url = $this->client->getBaseUrl() . '/key/v1/podcast_check.php';

        while (true) {
            if (time() - $start > $timeout) {
                throw new XiaoNanError("播客生成超时（{$timeout}秒），task_id={$this->taskId}");
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
                throw new XiaoNanError('播客查询返回了无法解析的响应');
            }

            if (($result['status'] ?? '') === 'done') {
                return $result['audio_url'];
            }
            if (($result['status'] ?? '') === 'error') {
                throw new XiaoNanError('播客生成失败', null, $result['error_msg'] ?? '未知错误');
            }

            sleep($interval);
        }
    }
}