<?php

declare(strict_types=1);

namespace App\Http\Controllers\Email;

use App\Models\NotificationEmail;
use Spatie\Mjml\Mjml;

class ShowController
{
    public function __invoke(NotificationEmail $email): string
    {
        /** @phpstan-ignore-next-line  */
        $mjml = view($email->template, $email->data)->render();

        return app(Mjml::class)
            ->sidecar()
            ->minify()
            ->toHtml($mjml);
    }
}
