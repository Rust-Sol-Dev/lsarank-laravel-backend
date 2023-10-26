<?php

namespace App\Services;

use App\Exceptions\IntercomFailedResponse;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use function Symfony\Component\ErrorHandler\traceAt;

class IntercomConnector
{
    /**
     * @var mixed
     */
    public $token;

    /**
     * IntercomConnector constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->token = env('INTERCOM_API_KEY', null);

        if (!$this->token) {
            throw new \Exception("Intercom API key not set");
        }
    }

    /**
     * Switch tags for user (Premium - Fremium)
     *
     * @param $oldTag
     * @param $newTag
     * @param User $user
     * @return string
     * @throws IntercomFailedResponse
     */
    public function switchTagsForUser($oldTag, $newTag, User $user)
    {
        $oldTagData = $this->createTag($oldTag);
        $newTagData = $this->createTag($newTag);

        $oldTagId = $oldTagData['id'];
        //$newTagId = $newTagData['id'];
        $newTagName = $newTagData['name'];

        $tagPayload = [
            'name' => $newTagName,
            'users' => [
                [
                    'id' => $user->intercom_id
                ]
            ],
        ];

        $this->untagUser($user->intercom_id, $oldTagId);
        $response = $this->tagUsers($tagPayload);

        return $response;
    }

    /**
     * Untag user
     *
     * @param User $user
     * @param $tagId
     * @return mixed|string
     * @throws IntercomFailedResponse
     */
    public function untagUser($intercom_id, $tagId)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->token",
            'Intercom-Version' => '2.8',
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->delete("https://api.intercom.io/contacts/$intercom_id/tags/$tagId");

        if (!$response->successful()) {
            $body = $response->body();
            $status = $response->status();
            throw new IntercomFailedResponse("Error while creating tag." . $body . "status " . $status);
        }

        $body = $response->body();

        $body = json_decode($body, true);

        return $body;

    }

    /**
     * Create a tag
     *
     * @param string $tag
     * @return bool
     * @throws IntercomFailedResponse
     */
    public function createTag(string $tag)
    {
        $tagPayload = ['name' => $tag];

        $response = $this->tagEndpointRequest($tagPayload);

        return $response;
    }

    /**
     * Tag a multiple users
     *
     * @param array $payload
     * @return string
     * @throws IntercomFailedResponse
     */
    public function tagUsers(array $payload)
    {
        $response = $this->tagEndpointRequest($payload);

        return $response;
    }

    /**
     * Tag endpoint request
     *
     * @param array $payload
     * @return string
     * @throws IntercomFailedResponse
     */
    private function tagEndpointRequest(array $payload)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->token",
            'Intercom-Version' => '2.8',
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.intercom.io/tags', $payload);

        if (!$response->successful()) {
            $body = $response->body();
            $status = $response->status();

            throw new IntercomFailedResponse("Error while creating tag." . $body . "status " . $status);
        }

        $body = $response->body();

        $body = json_decode($body, true);

        return $body;
    }

    /**
     * Create user in intecom
     *
     * @param User $user
     * @return bool
     * @throws IntercomFailedResponse
     */
    public function createUser(User $user)
    {
        $userPayload = $this->buildUserPayload($user);

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->token",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.intercom.io/contacts', $userPayload);

        if (!$response->successful()) {
            $body = $response->body();
            $status = $response->status();
            throw new IntercomFailedResponse("Error while creating user." . $body . "status " . $status);
        }

        $body = $response->body();

        $body = json_decode($body, true);

        return $body;
    }

    /**
     * Create user in intecom
     *
     * @param User $user
     * @return bool
     * @throws IntercomFailedResponse
     */
    public function getAllUsers()
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->token",
            'Intercom-Version' => '2.8',
            'accept' => 'application/json',
        ])->get('https://api.intercom.io/contacts?per_page=100');

        if (!$response->successful()) {
            $body = $response->body();
            $status = $response->status();
            throw new IntercomFailedResponse("Error while getting user list." . $body . "status " . $status);
        }

        $body = $response->body();

        $body = json_decode($body, true);

        return $body;
    }

    /**
     * Build user payload
     *
     * @param User $user
     * @return array
     */
    private function buildUserPayload(User $user)
    {
        return [
            "external_id" => $user->id,
            "email" => $user->email,
            "name" => $user->name,
            "signed_up_at" => $user->created_at->timestamp
        ];
    }
}
