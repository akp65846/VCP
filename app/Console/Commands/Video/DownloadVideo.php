<?php

namespace App\Console\Commands\Video;

use App\Enum\MediaConstant;
use App\Enum\StatusCode;
use App\Enum\VideoConstant;
use App\Models\Media;
use App\Models\Video;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DownloadVideo extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:download-video {videoID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download a approved video to storage';

    private Video $video;

    public function handle()
    {
        $video = Video::query()->find($this->argument('videoID'));
        if (!$video instanceof Video) {
            $this->error("Video not found");
            return FALSE;
        }

        $this->video = $video;

        if (empty($this->video['source_url'])) {
            $this->markAsInvalid();
            $this->error("Video url is empty");
            return FALSE;
        }

        $client = new Client();
        try {
            $response = $client->request('GET', $this->video['source_url']);
            $code = $response->getStatusCode();
            $headers = $response->getHeaders();

            $contentType = NULL;
            if (!empty($headers['Content-Type'])) {
                if (is_array($headers['Content-Type'])) {
                    $contentType = current($headers['Content-Type']);
                } else {
                    $contentType = $headers['Content-Type'];
                }
            }

            $extension = is_null($contentType) ? 'mp4' : explode('/', $contentType)['1'];

            if ($code != StatusCode::HTTP_OK) {
                $this->markAsInvalid();
                $this->error("Video url response code is {$code}");
                return FALSE;
            }
        } catch (GuzzleException $e) {
            $this->markAsInvalid();
            $this->error($e->getMessage());
            return FALSE;
        }

        $mediaPath = 'video' . '/' . date('Y') . '/' . date('m') . '/' .  date('d');
        $mediaFileName = $this->video['id'] . '_' . $this->video['content_creator_id'];
        $publicPath = 'public' . '/' . $mediaPath;
        $publicPathWithFile = $publicPath . '/' . $mediaFileName . '.' . $extension;

        if (!Storage::path('public' . '/' . $mediaPath)) {
            Storage::makeDirectory('public' . '/' . $mediaPath);
        }
        Storage::makeDirectory($mediaPath);

        //TODO:: install ssl to server and set php.ini
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];

        $videoContent = file_get_contents($this->video['source_url'], false, stream_context_create($stream_opts));

        if (empty($videoContent)) {
            $this->markAsInvalid();
            $this->error("Empty video content");
            return FALSE;
        }

        Storage::put($publicPathWithFile, $videoContent, 'public');

        $media = new Media();
        $media['status'] = MediaConstant::STATUS_ACTIVE;
        $media['path'] = $publicPathWithFile;
        $media['file_name'] = $mediaFileName . '.' . $extension;
        $media['file_size'] = Storage::size($publicPathWithFile);
        $media['file_format'] = $extension;
        $media->save();

        $this->video->update([
            'media_id' => $media['id']
        ]);

        $this->info("Upload Success");
        return TRUE;
    }

    protected function markAsInvalid() {
        $this->video->update([
            'status' => VideoConstant::STATUS_INVALID
        ]);
    }

}
