<?php
/**
 * 潇楠 Web3哨兵 PHP SDK - 统一客户端入口
 */

namespace XiaoNan\Web3;

class Client
{
    private $apiKey;
    private $baseUrl;

    /**
     * @param string $apiKey  API Key，格式 sk-xxxxxxxxxxxxxxxx
     * @param string $baseUrl API 基础地址，默认 https://www.ming.store
     */
    public function __construct($apiKey, $baseUrl = 'https://www.ming.store')
    {
        if (empty($apiKey)) {
            throw new XiaoNanError('API Key 不能为空，请前往 https://www.ming.store/key/buy_api_key.php 购买');
        }
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * 内部方法：发送 POST 请求
     */
    public function _post($path, $data = [])
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new XiaoNanError('请求失败', null, $curlError);
        }

        $result = json_decode($response, true);
        if ($result === null) {
            throw new XiaoNanError('服务器返回了无法解析的响应');
        }

        if ($httpCode >= 400) {
            $errorMsg = isset($result['error']['message']) ? $result['error']['message'] : ($result['error'] ?? '未知错误');
            throw new XiaoNanError('请求失败', $httpCode, $errorMsg);
        }

        return $result;
    }

    /**
     * 内部方法：发送 GET 请求
     */
    public function _get($path, $params = [])
    {
        $url = $this->baseUrl . $path;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new XiaoNanError('请求失败', null, $curlError);
        }

        $result = json_decode($response, true);
        if ($result === null) {
            throw new XiaoNanError('服务器返回了无法解析的响应');
        }

        if ($httpCode >= 400) {
            $errorMsg = isset($result['error']['message']) ? $result['error']['message'] : ($result['error'] ?? '未知错误');
            throw new XiaoNanError('请求失败', $httpCode, $errorMsg);
        }

        return $result;
    }

    /**
     * 发送表单格式 POST 请求（用于封装 API 接口）
     */
    public function _postForm($path, $data = [])
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new XiaoNanError('请求失败', null, $curlError);
        }

        $result = json_decode($response, true);
        if ($result === null) {
            throw new XiaoNanError('服务器返回了无法解析的响应');
        }

        if ($httpCode >= 400) {
            $errorMsg = $result['error'] ?? '未知错误';
            throw new XiaoNanError('请求失败', $httpCode, $errorMsg);
        }

        return $result;
    }

    // ========== 聚合API：文本对话 ==========
    public function chat($message, $model = 'deepseek-v4-flash', $system = '', $maxTokens = 4096, $temperature = 0.7)
    {
        $messages = [];
        if (!empty($system)) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $data = [
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => $maxTokens,
            'temperature' => $temperature,
        ];

        $result = $this->_post('/v1/chat/completions', $data);
        return $result['choices'][0]['message']['content'];
    }

    // ========== 聚合API：查询可用模型列表 ==========
    public function listModels()
    {
        $url = $this->baseUrl . '/v1/models';
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
}