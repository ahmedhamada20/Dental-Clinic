<?php

namespace App\Modules\Notifications\DTOs;

class NotificationDTO
{
    public function __construct(
        public int $user_id,
        public string $type,
        public string $title,
        public string $message,
        public ?array $data = null,
    ) {}
}

