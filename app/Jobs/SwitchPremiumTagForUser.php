<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\IntercomConnector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SwitchPremiumTagForUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 999999;

    /**
     * @var int
     */
    public $tries = 10;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 600;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $newTag;

    /**
     * RegisterNewUserAtIntercomAndTagThem constructor.
     * @param User $user
     */
    public function __construct(User $user, string $newTag)
    {
        $this->user = $user;
        $this->newTag = $newTag;
    }

    /**
     * @param IntercomConnector $intercomConnector
     * @throws \Exception
     */
    public function handle(IntercomConnector $intercomConnector)
    {
        if ($this->newTag === 'Premium') {
            $oldTag = 'Freemium';
        } else {
            $oldTag = 'Premium';
        }

        try {
            $response = $intercomConnector->switchTagsForUser($oldTag, $this->newTag, $this->user);
        } catch (\Exception $exception) {
            throw $exception;
        }

    }

    /**
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception)
    {
        activity()->event('INTERCOM_SWITCH_TAG_JOB_FAILED')->log($exception->getMessage() . $this->user->id);
    }
}
