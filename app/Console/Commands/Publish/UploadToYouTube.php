<?php

namespace App\Console\Commands\Publish;

use App\Enum\PlatformAccountConstant;
use App\Models\Media;
use App\Models\PlatformAccount;
use App\Models\Publishment;
use Exception;
use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UploadToYouTube extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish:upload-youtube {publishmentID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload a video to youtube';

    private PlatformAccount $accessTokenAccount;
    private Publishment $publishment;
    private string $accessToken;
    private Media $media;

    private array $oauthClientConfigs = [];

    /**
     * @throws Exception
     */
    public function handle()
    {
        if (empty($this->argument('publishmentID'))) {
            throw new Exception("Missing publishment id");
        }

        $publishment = Publishment::query()->find($this->argument('publishmentID'));

        if(!$publishment instanceof Publishment) {
            throw new Exception("Publishment not found");
        }

        $this->publishment = $publishment;

        $accessTokenAccount = PlatformAccount::query()
            ->where('platform_id', $publishment['target_platform_id'])
            ->where('status', PlatformAccountConstant::STATUS_ACTIVE)
            ->where('name', PlatformAccountConstant::NAME_API_ACCESS_TOKEN)
            ->first();

        if (!$accessTokenAccount instanceof PlatformAccount) {
            throw new Exception("access token account not found");
        }
        $this->accessTokenAccount = $accessTokenAccount;

        $oauthClientConfigAccount = PlatformAccount::query()
            ->where('platform_id',  $publishment['target_platform_id'])
            ->where('status', PlatformAccountConstant::STATUS_ACTIVE)
            ->where('name', PlatformAccountConstant::NAME_OAUTH_CLIENT_JSON)
            ->first();

        if (empty($oauthClientConfigAccount)) {
            throw new Exception("oauth client config not found");
        }

        $this->oauthClientConfigs = json_decode($oauthClientConfigAccount['value'], TRUE);

        $this->accessToken = $accessTokenAccount['value'];

        if (!empty($accessTokenAccount['expire_time'])) {

            if (strtotime($accessTokenAccount['expire_time']) < time()) {
                $this->accessToken = $this->refreshAccessToken();
            }
        }

        if (!$this->accessToken) {
            throw new Exception("cannot refresh access token");
        }

        $media = Media::query()->find($this->publishment['media_id']);
        if (!$media instanceof Media) {
            throw new Exception("media not found");
        }
        $this->media = $media;

        $this->upload();

        return TRUE;

    }

    /**
     * @throws Exception
     */
    private function refreshAccessToken() {

        $refreshTokenAccount = PlatformAccount::query()
            ->where('platform_id', $this->publishment['target_platform_id'])
            ->where('status', PlatformAccountConstant::STATUS_ACTIVE)
            ->where('name', PlatformAccountConstant::NAME_API_REFRESH_TOKEN)
            ->first();

        if (empty($refreshTokenAccount)) {
            throw new Exception("refresh token account not found");
        }

        $refreshToken = $refreshTokenAccount['value'];

        $oauthUrl = 'https://oauth2.googleapis.com/token';

        $response = Http::asForm()->post($oauthUrl, [
            'client_id' => $this->oauthClientConfigs['web']['client_id'],
            'client_secret' => $this->oauthClientConfigs['web']['client_secret'],
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $accessToken = $data['access_token'];
            $expireTime = time() + intval($data['expires_in']);

            $this->accessTokenAccount->update([
                'value' => $accessToken,
                'expire_time' => date('Y-m-d H:i:s', $expireTime)
            ]);

            return $accessToken;

        } else {
            throw new Exception("cannot refresh token");
        }
    }

    /**
     * @throws Exception
     */
    private function upload() {

        $apiKeyAccount = PlatformAccount::query()
            ->where('platform_id', $this->publishment['target_platform_id'])
            ->where('status', PlatformAccountConstant::STATUS_ACTIVE)
            ->where('name', PlatformAccountConstant::NAME_API_KEY)
            ->first();

        if (empty($apiKeyAccount)) {
            throw new Exception("api key account not found");
        }

        $client = new Google_Client();
        try {
            $client->setAuthConfig($this->oauthClientConfigs);
        } catch (\Google\Exception $e) {
            throw new Exception($e->getMessage());
        }
        $client->setDeveloperKey($apiKeyAccount['value']);
        $client->setAccessToken($this->accessToken);
        $service = new Google_Service_YouTube($client);
        $video = new Google_Service_YouTube_Video();

        $videoSnippet = new Google_Service_YouTube_VideoSnippet();
//        $videoSnippet->setCategoryId('22');

        if (!empty($this->publishment->description)) {
            $videoSnippet->setDescription($this->publishment->description);
        }

        if (!empty($this->publishment->title)) {
            $videoSnippet->setTitle($this->publishment->title);
        }

        if (!empty($this->publishment->scheduled_time)) {
            $videoSnippet->setPublishedAt($this->publishment->scheduled_time);
        }

        $video->setSnippet($videoSnippet);

        $videoStatus = new Google_Service_YouTube_VideoStatus();
        $videoStatus->setPrivacyStatus('private');
        $video->setStatus($videoStatus);

        try{
            $response = $service->videos->insert(
                'snippet,status',
                $video,
                array(
                    'data' => Storage::disk('local')->get($this->media['path']),
                    'mimeType' => 'application/octet-stream',
                    'uploadType' => 'multipart',
                    'notifySubscribers' => !empty($this->publishment->is_notify_subscribers) && $this->publishment->is_notify_subscribers == 1
                )
            );

        }catch(Exception $e) {
            $errorMsg = $e->getMessage();
            if ($errorMsg[0] = '{') {
                $errorDetail = json_decode($errorMsg, TRUE);

                throw new Exception("code: {$errorDetail['error']['code']}, message: {$errorDetail['error']['message']}, status: {$errorDetail['error']['status']}");
            }
            throw new Exception($e->getMessage());
        }

        $this->publishment->update([
            'uploaded_time' => now()
        ]);

        Storage::put('testres.txt', json_encode($response));

    }
}
