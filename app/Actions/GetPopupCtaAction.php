<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Popup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class GetPopupCtaAction
{
    public function handle(): ?Popup
    {
        return Popup::query()
            ->get()
            ->reject(function (Popup $popup): bool {
                if (Request::hasCookie("CS_SEEN_POPUP_{$popup->id}")) {
                    $lastSeenModal = Carbon::createFromTimestamp((int) Request::cookie("CS_SEEN_POPUP_{$popup->id}"));
                    $modalDisplayLimit = Carbon::now()->subDays($popup->display_every);

                    if ($lastSeenModal->greaterThan($modalDisplayLimit)) {
                        return true;
                    }
                }

                return false;
            })
            ->first();
    }
}
