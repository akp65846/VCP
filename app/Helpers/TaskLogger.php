<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

class TaskLogger
{
    public string $taskSignature;
    public const LOG_CHANNEL = 'tasklog';

    public function __construct($taskSignature) {
        $this->taskSignature = $taskSignature;
    }

    public function startTask(): void
    {
        Log::channel(self::LOG_CHANNEL)->info("[Start] " . $this->taskSignature);
    }

    public function endTask(): void
    {
        Log::channel(self::LOG_CHANNEL)->info("[End] " . $this->taskSignature);
    }

    public function critical($message): void
    {
        Log::channel(self::LOG_CHANNEL)->critical($message);
    }

    public function warning($message): void
    {
        Log::channel(self::LOG_CHANNEL)->warning($message);
    }

    public function successAll($message): void
    {
        Log::channel(self::LOG_CHANNEL)->info("[Success All] " . $message);
    }

    public function successOne($message): void
    {
        Log::channel(self::LOG_CHANNEL)->notice("[Success One] " . $message);
    }

    public function failOne($message): void
    {
        Log::channel(self::LOG_CHANNEL)->error("[Fail One] " . $message);
    }

}
