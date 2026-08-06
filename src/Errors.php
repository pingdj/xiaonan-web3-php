<?php
/**
 * 潇楠 Web3哨兵 PHP SDK - 统一异常类
 */

namespace XiaoNan\Web3;

use Exception;

class XiaoNanError extends Exception
{
    private static $messages = [
        400 => '请求参数有误，请检查您的输入是否正确。',
        401 => '认证失败，请检查您的 API Key 是否正确，或前往 https://www.ming.store/key/buy_api_key.php 购买。',
        403 => '访问被拒绝，请确认您的 API Key 是否有权限调用此接口。',
        404 => '请求的接口不存在，请检查 URL 地址是否正确。',
        429 => '请求频率过高，请稍后再试（每次调用间隔至少 500 毫秒）。',
        500 => '服务器内部错误，请稍后重试。如果多次出现，请联系管理员。',
        502 => '上游服务暂时不可用，请稍后重试。',
        503 => '服务暂时繁忙，请稍后重试。',
        530 => '上游服务暂时不可用，请稍后重试。',
    ];

    protected $statusCode;
    protected $originalError;

    public function __construct($message = '', $statusCode = null, $originalError = null)
    {
        $this->statusCode = $statusCode;
        $this->originalError = $originalError;

        $parts = [$message];
        if ($statusCode && isset(self::$messages[$statusCode])) {
            $parts[] = '[' . $statusCode . '] ' . self::$messages[$statusCode];
        } elseif ($statusCode) {
            $parts[] = '[' . $statusCode . ']';
        }
        if ($originalError) {
            $parts[] = '（原始错误: ' . $originalError . '）';
        }

        $fullMessage = implode(' ', $parts);
        parent::__construct($fullMessage);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getOriginalError()
    {
        return $this->originalError;
    }
}