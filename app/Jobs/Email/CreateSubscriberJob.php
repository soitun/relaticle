<?php

declare(strict_types=1);

namespace App\Jobs\Email;

use App\Data\SubscriberData;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\ThrottlesExceptionsWithRedis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\MailcoachSdk\Facades\Mailcoach;

final class CreateSubscriberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 0;

    public function __construct(private readonly SubscriberData $data) {}

    public function handle(): void
    {
        $subscriber = Mailcoach::createSubscriber(
            config('mailcoach-sdk.subscribers_list_id'),
            $this->data->except('user_id')->toArray(),
        );

        if ($this->data->user_id) {
            User::query()
                ->where('id', $this->data->user_id)
                ->whereNull('mailcoach_subscriber_uuid')
                ->update(['mailcoach_subscriber_uuid' => $subscriber->uuid]);
        }
    }

    /** @return array<ThrottlesExceptionsWithRedis> */
    public function middleware(): array
    {
        return [new ThrottlesExceptionsWithRedis(1, 1)->backoff(1)->report()];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHour();
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('email_subscriptions_channel')->error("Failed to create subscriber '{$this->data->email}'", [
            'error' => $exception->getMessage(),
        ]);
    }
}
