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

class RegisterNewUserAtIntercomAndTagThem implements ShouldQueue
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
     * RegisterNewUserAtIntercomAndTagThem constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     *
     *
     * @param IntercomConnector $intercomConnector
     * @throws \App\Exceptions\IntercomFailedResponse
     */
    public function handle(IntercomConnector $intercomConnector)
    {
        try {
            $userData = $intercomConnector->createUser($this->user);
            $interComId = $userData['id'];
            $this->user->intercom_id = $interComId;
            $this->user->save();
        } catch (\Exception $exception) {
            $interComId = $this->user->intercom_id;
        }

        $fremiumPayload = [
            'name' => 'Freemium',
            'users' => [
                [
                    'id' => $interComId
                ]
            ],
        ];

        $userData = $intercomConnector->tagUsers($fremiumPayload);
    }

    /**
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception)
    {
        activity()->event('INTERCOM_REGISTER_JOB_FAILED')->log($exception->getMessage() . $this->user->id);
    }
}
