<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Models\NotificationEmail;
use App\Models\Shop\ShopCustomer;
use Illuminate\Mail\SentMessage;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Spatie\Mjml\Mjml;

class MailChannel extends BaseMailChannel
{
    /** @param ShopCustomer | AnonymousNotifiable $notifiable */
    public function send($notifiable, Notification $notification): ?SentMessage
    {
        if ( ! method_exists($notification, 'toMail')) {
            return parent::send($notifiable, $notification);
        }

        /** @var MjmlMessage $message */
        $message = $notification->toMail($notifiable);

        if ( ! isset($message->mjml)) {
            return parent::send($notifiable, $notification);
        }

        /** @var \App\Infrastructure\Notification $notification */
        $email = '';

        if ($notifiable instanceof ShopCustomer) {
            $email = $notifiable->email;
        }

        if ($notifiable instanceof AnonymousNotifiable) {
            $email = $notifiable->routes['mail'] ?? '';
        }

        $model = NotificationEmail::query()->create([
            'user_id' => $notifiable->id ?? null,
            'email_address' => $email,
            'template' => $message->mjml,
            'data' => $message->viewData,
        ]);

        $notification->setKey($model->key);

        return parent::send($notifiable, $notification);
    }

    protected function buildMjml(MjmlMessage $message): string
    {
        /** @phpstan-ignore-next-line  */
        $rawMessage = view($message->mjml, $message->data())->render();

        return $rawMessage;

//        return app(Mjml::class)
//            ->sidecar()
//            ->minify()
//            ->toHtml($rawMessage);
    }

    /**
     * @param  MjmlMessage  $message
     * @return array
     */
    protected function buildView($message)
    {
        return ['html' => new HtmlString($this->buildMjml($message))];
    }
}
