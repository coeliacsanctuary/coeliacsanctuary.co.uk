<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsletter;

use App\Actions\SignUpToNewsletterAction;
use App\Http\Requests\Newsletter\StoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class StoreController
{
    public function __invoke(StoreRequest $request, SignUpToNewsletterAction $signUpToNewsletterAction): RedirectResponse
    {
        $signUpToNewsletterAction->handle($request->string('email')->toString());

        return new RedirectResponse(URL::previous());
    }
}
