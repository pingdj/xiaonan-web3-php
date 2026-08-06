<p align="center">
  <h1 align="center">🛡️ 潇楠 Web3哨兵 · PHP SDK</h1>
  <p align="center">
    <img src="https://img.shields.io/packagist/v/xiaonan/web3-sdk?color=blue" alt="Packagist version">
    <img src="https://img.shields.io/packagist/php-v/xiaonan/web3-sdk?color=green" alt="PHP versions">
    <img src="https://img.shields.io/packagist/dt/xiaonan/web3-sdk?color=orange" alt="Downloads">
    <img src="https://img.shields.io/github/license/pingdj/xiaonan-web3-php?color=lightgrey" alt="License">
  </p>
  <p align="center">
    一个 API Key，调用 20+ AI 功能。覆盖文本对话、文生图、文生视频、TTS、播客、市场分析、安全检测等全部能力。
  </p>
</p>

---

## 📦 安装

    composer require xiaonan/web3-sdk

**要求**：PHP 7.4 及以上版本，需要开启 curl 和 json 扩展。

---

## 🚀 快速开始

1. [购买 API Key](https://www.ming.store/key/buy_api_key.php)
2. 在代码中初始化客户端

    require_once 'vendor/autoload.php';

    use XiaoNan\Web3\Client;

    $client = new Client('sk-你的Key');

---

## 📋 功能列表

| 类别 | 方法 | 说明 |
|------|------|------|
| 💬 文本对话 | `$client->chat()` | 调用 DeepSeek、GPT、Qwen、Kimi 等大模型 |
| 📋 模型列表 | `$client->listModels()` | 查询可用文本模型列表 |
| 🎨 多模态模型 | `$multimodal->listMultimodalModels()` | 查询可用图像/视频模型列表 |
| 🖼️ 文生图 | `$multimodal->image()` | 根据提示词生成图片 |
| 🎬 文生视频 | `$multimodal->video()->wait()` | 提交视频任务，自动轮询 |
| 🔊 TTS | `$generation->tts()` | 文本转语音，返回音频链接 |
| 🎙️ 播客 | `$generation->podcast()->wait()` | 双人播客，自动轮询 |
| ✍️ AI 创作 | `$generation->article()` | 根据关键词生成文章 |
| 📈 市场数据 | `$market->market()` | 市场数据 + AI 分析报告 |
| 🛡️ 风控检查 | `$market->risk()` | 开仓风控 + AI 建议 |
| 🔍 代币安全 | `$market->tokenCheck()` | GoPlus 安全检测 |
| 🔐 代币审计 | `$market->tokenAudit()` | 币安 Web3 合约审计 |
| 📊 DEX 分析 | `$market->dex()` | DEX 交易对分析 |
| 📄 网页提取 | `$data->extract()` | 网页内容提取 + AI 分析 |
| 📰 实时快讯 | `$data->newsflash()` | Web3 实时快讯 |
| 🦅 特朗普动态 | `$data->trumpFeed()` | Truth Social + X 双平台 |
| 📹 视频解析 | `$media->videoParse()` | 抖音/TikTok 去水印 |
| 🤖 数字人 V2 | `$media->digitalHumanV2()->wait()` | 单图音频驱动，自动轮询 |
| 📊 配额查询 | `$account->quota()` | 查询 Key 剩余配额 |

---

## 💻 代码示例

### 文本对话

    $client = new Client('sk-你的Key');

    $reply = $client->chat('你好，请介绍一下你自己');
    echo $reply;

    // 指定模型
    $reply = $client->chat('分析一下今天的行情', 'deepseek-v4-flash');

### 文生图

    $multimodal = new Multimodal($client);

    $imageUrl = $multimodal->image('一只在月亮下奔跑的狼');
    echo $imageUrl;

### 文生视频

    $multimodal = new Multimodal($client);

    $task = $multimodal->video('海浪拍打礁石的慢动作');
    $videoUrl = $task->wait();
    echo $videoUrl;

### TTS 文本转语音

    $generation = new Generation($client);

    $audioUrl = $generation->tts('你好世界');
    echo $audioUrl;

### AI 一键创作

    $generation = new Generation($client);

    $article = $generation->article('Web3发展趋势');
    echo $article['title'];
    echo $article['content'];

### 市场数据 + AI 分析

    $market = new Market($client);

    $data = $market->market('BTCUSDT');
    echo $data['ai_analysis'];

### 风控检查

    $market = new Market($client);

    $report = $market->risk('BTCUSDT', 'long', 10);
    echo '风险等级: ' . $report['risk_report']['level'];

### 代币安全检测

    $market = new Market($client);

    $result = $market->tokenCheck('0x55d398326f99059fF775485246999027B3197955');
    echo $result['ai_analysis'];

---

## ❗ 错误处理

SDK 在所有 API 调用失败时抛出 `XiaoNanError` 异常，包含 HTTP 状态码和中文错误提示：

    try {
        $reply = $client->chat('你好');
    } catch (XiaoNanError $e) {
        echo '调用失败: ' . $e->getMessage();
    }

---

## 🔗 相关资源

| 资源 | 链接 |
|------|------|
| Packagist 包首页 | [packagist.org/packages/xiaonan/web3-sdk](https://packagist.org/packages/xiaonan/web3-sdk) |
| GitHub 仓库 | [github.com/pingdj/xiaonan-web3-php](https://github.com/pingdj/xiaonan-web3-php) |
| API 接口文档 | [ming.store/key/docs.php](https://www.ming.store/key/docs.php) |
| 聚合多模型 API | [ming.store/key/llm_api_docs.php](https://www.ming.store/key/llm_api_docs.php) |
| MCP 接入指南 | [ming.store/key/mcp_docs.php](https://www.ming.store/key/mcp_docs.php) |
| 购买 API Key | [ming.store/key/buy_api_key.php](https://www.ming.store/key/buy_api_key.php) |

---

## 📄 License

MIT © 2026 潇楠 Web3哨兵 实验室