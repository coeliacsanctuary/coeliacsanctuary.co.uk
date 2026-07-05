<?php

declare(strict_types=1);

namespace App\Actions\Redirects;

use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;

class HandleRedirectResponseAction
{
    public function handle(Redirect $redirect): RedirectResponse
    {
        $redirect->increment('hits');

        return redirect($redirect->to, $redirect->status);
    }
}
