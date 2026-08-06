<?php
/**
 * 潇楠 Web3哨兵 PHP SDK - 多模态生成模块（文生图、文生视频）
 */

namespace XiaoNan\Web3;

class Multimodal
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========== 查询多模态模型列表 ==========
    public function listMultimodalModels()
    {
        $url = $this->client->getBaseUrl() . '/v1/multimodal/models';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if ($result === null) {
            throw new XiaoNanError('服务器返回了无法解析的响应');
        }
        return $result['data'];
    }

    // ========== 文生图 ==========
    public function image($prompt, $size = '1024x768', $imageUrl = '', $negativePrompt = '', $seed = null)
    {
        $data = [
            'model'  => 'agnes-image-2.1-flash',
            'prompt' => $prompt,
            'size'   => $size,
        ];
        if (!empty($imageUrl)) {
            $data['image_url'] = $imageUrl;
        }
        if (!empty($negativePrompt)) {
            $data['negative_prompt'] = $negativePrompt;
        }
        if ($seed !== null) {
            $data['seed'] = $seed;
        }

        $result = $this->client->_post('/v1/multimodal/completions', $data);

        if (isset($result['data'][0]['url'])) {
            return $result['data'][0]['url'];
        }
        if (isset($result['url'])) {
            return $result['url'];
        }

        throw new XiaoNanError('文生图失败，返回数据中未找到图片链接');
    }

    // ========== 文生视频 ==========
    public function video($prompt, $width = 1152, $height = 768, $numFrames = null, $frameRate = 24, $imageUrl = '', $negativePrompt = '', $seed = null)
    {
        $data = [
            'model'      => 'agnes-video-v2.0',
            'prompt'     => $prompt,
            'width'      => $width,
            'height'     => $height,
            'frame_rate' => $frameRate,
        ];
        if ($numFrames !== null) {
            $data['num_frames'] = $numFrames;
        }
        if (!empty($imageUrl)) {
            $data['image_url'] = $imageUrl;
        }
        if (!empty($negativePrompt)) {
            $data['negative_prompt'] = $negativePrompt;
        }
        if ($seed !== null) {
            $data['seed'] = $seed;
        }

        $result = $this->client->_post('/v1/multimodal/completions', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('文生视频任务提交失败', null, $result['error'] ?? '未知错误');
        }

        return new VideoTask($this->client, $result['video_id']);
    }
}

/**
 * 文生视频任务，封装自动轮询
 */
class VideoTask
{
    private $client;
    private $videoId;

    public function __construct(Client $client, $videoId)
    {
        $this->client = $client;
        $this->videoId = $videoId;
    }

    public function wait($interval = 10, $timeout = 360)
    {
        $start = time();
        $url = $this->client->getBaseUrl() . '/v1/multimodal/video_query';

        while (true) {
            if (time() - $start > $timeout) {
                throw new XiaoNanError("视频生成超时（{$timeout}秒），video_id={$this->videoId}");
            }

            $ch = curl_init($url . '?video_id=' . urlencode($this->videoId));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->client->getApiKey(),
                ],
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            if ($result === null) {
                throw new XiaoNanError('视频查询返回了无法解析的响应');
            }

            if (!empty($result['url'])) {
                return $result['url'];
            }
            if (in_array($result['status'] ?? '', ['failed', 'error'])) {
                throw new XiaoNanError('文生视频生成失败');
            }

            sleep($interval);
        }
    }
}