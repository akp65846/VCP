<?php

namespace App\Console\Commands\Publish;

use App\Enum\PlatformAccountConstant;
use App\Models\Media;
use App\Models\PlatformAccount;
use App\Models\Publishment;
use Google\Exception;
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

    public function handle()
    {
//        $progressBar = $this->output->createProgressBar(100);

        if (empty($this->argument('publishmentID'))) {
            $this->error("Missing publishment ID");
            return FALSE;
        }

        $publishment = Publishment::query()->find($this->argument('publishmentID'));

        if(!$publishment instanceof Publishment) {
            $this->error("Publishment not found");
            return FALSE;
        }

        $this->publishment = $publishment;
//        $progressBar->advance(20);

        $accessTokenAccount = PlatformAccount::query()
            ->where('platform_id', $publishment['target_platform_id'])
            ->where('status', PlatformAccountConstant::STATUS_ACTIVE)
            ->where('name', PlatformAccountConstant::NAME_API_ACCESS_TOKEN)
            ->first();

        if (!$accessTokenAccount instanceof PlatformAccount) {
            $this->error("access token account not found");
            return FALSE;
        }
        $this->accessTokenAccount = $accessTokenAccount;

        $oauthClientConfigAccount = PlatformAccount::query()
            ->where('platform_id',  $publishment['target_platform_id'])
            ->where('status', PlatformAccountConstant::STATUS_ACTIVE)
            ->where('name', PlatformAccountConstant::NAME_OAUTH_CLIENT_JSON)
            ->first();

        if (empty($oauthClientConfigAccount)) {
            $this->error("oauth client config not found");
            return FALSE;
        }

        $this->oauthClientConfigs = json_decode($oauthClientConfigAccount['value'], TRUE);

//        $progressBar->advance(10);

        $this->accessToken = $accessTokenAccount['value'];

        if (!empty($accessTokenAccount['expire_time'])) {

            if (strtotime($accessTokenAccount['expire_time']) < time()) {
                $this->accessToken = $this->refreshAccessToken();
            }
        }

        if (!$this->accessToken) {
            $this->error("cannot refresh access token");
            return FALSE;
        }

//        $progressBar->advance(10);

        $media = Media::query()->find($this->publishment['media_id']);
        if (!$media instanceof Media) {
            $this->error("media not found");
            return FALSE;
        }
        $this->media = $media;

        $this->upload();


        $this->info("success");
        return TRUE;

    }

    private function refreshAccessToken() {

        $refreshTokenAccount = PlatformAccount::query()
            ->where('platform_id', $this->publishment['target_platform_id'])
            ->where('status', PlatformAccountConstant::STATUS_ACTIVE)
            ->where('name', PlatformAccountConstant::NAME_API_REFRESH_TOKEN)
            ->first();

        if (empty($refreshTokenAccount)) {
            $this->error("Refresh token account not found");
            return FALSE;
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
            $this->error('cannot refresh token');
            return FALSE;
        }
    }

    private function upload() {

        $apiKeyAccount = PlatformAccount::query()
            ->where('platform_id', $this->publishment['target_platform_id'])
            ->where('status', PlatformAccountConstant::STATUS_ACTIVE)
            ->where('name', PlatformAccountConstant::NAME_API_KEY)
            ->first();

        if (empty($apiKeyAccount)) {
            $this->error('api key account not found');
            return FALSE;
        }

        $client = new Google_Client();
        try {
            $client->setAuthConfig($this->oauthClientConfigs);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return FALSE;
        }
        $client->setDeveloperKey($apiKeyAccount['value']);
        $client->setAccessToken($this->accessToken);
        $service = new Google_Service_YouTube($client);
        $video = new Google_Service_YouTube_Video();

        $videoSnippet = new Google_Service_YouTube_VideoSnippet();
        $videoSnippet->setCategoryId('22');
        $videoSnippet->setDescription('Description of uploaded video.');
        $videoSnippet->setTitle('Test video upload.');
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
                    'uploadType' => 'multipart'
                )
            );

        }catch(Exception $e) {
            $errorMsg = $e->getMessage();
            if ($errorMsg[0] = '{') {
                $errorDetail = json_decode($errorMsg, TRUE);

                var_dump($errorDetail['error']['code']); //400
                var_dump($errorDetail['error']['message']); // Request contains an invalid argument.
                var_dump($errorDetail['error']['status']); // INVALID_ARGUMENT
            }
            $this->error($e->getMessage());
            return FALSE;
        }

        Storage::put('testres.txt', json_encode($response));

    }
}
