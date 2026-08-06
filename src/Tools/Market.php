<?php
/**
 * 潇楠 Web3哨兵 PHP SDK - 市场与安全分析工具
 */

namespace XiaoNan\Web3\Tools;

use XiaoNan\Web3\Client;
use XiaoNan\Web3\XiaoNanError;

class Market
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // ========== 市场数据 + AI 分析 ==========
    public function market($pair)
    {
        $data = [
            'api_key' => $this->client->getApiKey(),
            'pair'    => strtoupper($pair),
        ];

        $result = $this->client->_postForm('/key/v1/market_analyze.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('市场数据获取失败', null, $result['error'] ?? '未知错误');
        }

        return $result;
    }

    // ========== 风控检查 + AI 解读 ==========
    public function risk($pair, $side, $leverage = 10, $positionSize = '', $stopLoss = '', $holdingTime = '', $reason = '')
    {
        $data = [
            'api_key'  => $this->client->getApiKey(),
            'pair'     => strtoupper($pair),
            'side'     => $side,
            'leverage' => $leverage,
        ];
        if (!empty($positionSize)) {
            $data['position_size'] = $positionSize;
        }
        if (!empty($stopLoss)) {
            $data['stop_loss'] = $stopLoss;
        }
        if (!empty($holdingTime)) {
            $data['holding_time'] = $holdingTime;
        }
        if (!empty($reason)) {
            $data['reason'] = $reason;
        }

        $result = $this->client->_postForm('/key/v1/risk_analyze.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('风控检查失败', null, $result['error'] ?? '未知错误');
        }

        return $result;
    }

    // ========== 代币安全检测 + AI 解读 ==========
    public function tokenCheck($address, $chainId = '56')
    {
        $data = [
            'api_key'  => $this->client->getApiKey(),
            'address'  => $address,
            'chain_id' => $chainId,
        ];

        $result = $this->client->_postForm('/key/v1/token_analyze.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('代币安全检测失败', null, $result['error'] ?? '未知错误');
        }

        return $result;
    }

    // ========== 代币合约审计 + AI 解读 ==========
    public function tokenAudit($contractAddress, $chainId = '56')
    {
        $data = [
            'api_key'          => $this->client->getApiKey(),
            'contract_address' => $contractAddress,
            'chain_id'         => $chainId,
        ];

        $result = $this->client->_postForm('/key/v1/token_audit_analyze.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('代币审计失败', null, $result['error'] ?? '未知错误');
        }

        return $result;
    }

    // ========== DEX 交易对分析 + AI 解读 ==========
    public function dex($tokenAddress, $chain = 'bsc')
    {
        $data = [
            'api_key'       => $this->client->getApiKey(),
            'token_address' => $tokenAddress,
            'chain'         => $chain,
        ];

        $result = $this->client->_postForm('/key/v1/dexscreener_analyze.php', $data);

        if (empty($result['success'])) {
            throw new XiaoNanError('DEX分析失败', null, $result['error'] ?? '未知错误');
        }

        return $result;
    }
}