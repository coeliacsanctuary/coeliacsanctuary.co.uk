<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Casts\EmailDataCast;
use Illuminate\Notifications\Messages\MailMessage;

class MjmlMessage extends MailMessage
{
    public string $mjml;

    public static function make(?string $view = null, array|EmailDataCast $data = []): self
    {
        $instance = new self();

        if ($view) {
            $instance->mjml($view, $data);
        }

        return $instance;
    }

    public function mjml(string $view, array|EmailDataCast $data = []): static
    {
        $this->mjml = $this->view = $view;
        $this->markdown = null;
        $this->viewData = (array) $data;

        return $this;
    }

    public function via(): array
    {
        return ['mail'];
    }
}
