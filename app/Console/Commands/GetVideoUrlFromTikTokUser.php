<?php

namespace App\Console\Commands;

use App\Enum\ContentCreatorConstant;
use App\Enum\PlatformConstant;
use App\Enum\VideoConstant;
use App\Models\ContentCreator;
use App\Models\Platform;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;

class GetVideoUrlFromTikTokUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:load-tiktok {creatorID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all video url from a tiktok content creator';

    const API_BASE_URL = 'https://www.douyin.com/aweme/v1/web/aweme/post/';

    public function handle()
    {

        $savedCount = 0;
        $skippedCount = 0;
        $exceedLengthCount = 0;

        $progressBar = $this->output->createProgressBar(100);

        $contentCreator = ContentCreator::query()->where('id', $this->argument('creatorID'))->where('status', ContentCreatorConstant::STATUS_ACTIVE)->first();

        if (empty($contentCreator)) {
            $this->error("Content creator not found");
            exit();
        }

        $progressBar->advance(10);

        $platform = Platform::query()->where('id', $contentCreator->platform_id)->where('status', ContentCreatorConstant::STATUS_ACTIVE)->first();

        if (empty($platform)) {
            $this->error("Platform not found");
            exit();
        }

        if ($platform->name != PlatformConstant::NAME_TIKTOK) {
            $this->error("Platform is not TikTok");
            exit();
        }

        if (empty($contentCreator->platform_unique_uid)) {
            $this->error("Platform unique user id not found");
        }

        $progressBar->advance(10);

        $referer = 'https://www.douyin.com/user/' . $contentCreator->platform_unique_uid;

        $response = Http::timeout(60)->withHeaders([
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1',
            'referer' => $referer,
        ])->get(self::API_BASE_URL, [
            'aid' => 6383,
            'sec_user_id' => $contentCreator->platform_unique_uid,
            'count' => 50
        ]);

        $progressBar->advance(30);

        $isSuccess = $response->successful();

        if (!$isSuccess) {
            $this->error($response);
            exit();
        }
//        Storage::disk('local')->put('sample_tiktok_response.json', $response);
        $data = $response->json();

        $list = empty($data['aweme_list']) || !is_array($data['aweme_list']) ? [] : $data['aweme_list'];

        foreach ($list as $item) {

            $duration = (int)(intval($item['video']['duration']) / 1000);

            if ($duration >= 60) {
                $exceedLengthCount++;
                continue;
            }

            $videoUrl = "";
            $videoLinks = $item['video']['play_addr']['url_list'];
            foreach ($videoLinks as $link) {
                if (str_contains($link, "https://www.douyin.com")) {
                    $videoUrl = $link;
                }
            }


            $fields = [
                'key' => $item['video']['play_addr']['uri'],
                'platform_id' => $contentCreator->platform_id,
                'content_creator_id' => $contentCreator->id,
                'source_url' => $videoUrl,
                'status' => VideoConstant::STATUS_ACTIVE,
                'approval_status' => VideoConstant::APPROVAL_STATUS_PENDING,
                'cover_image_url' => $item['video']['cover']['url_list'][0],
                'title' => $item['desc'],
                'size' => intval($item['video']['play_addr']['data_size']),
                'height' => intval($item['video']['play_addr']['height']),
                'width' => intval($item['video']['play_addr']['width']),
                'duration' => $duration,
            ];

            try {
                $video = new Video($fields);
                $isSaved = $video->save();

                if ($isSaved) {
                    $savedCount++;
                } else {
                    $skippedCount++;
                }
            }catch (QueryException $e) {
                $this->warn($e->getMessage());
                $skippedCount++;
            }
        }

        $progressBar->advance(50);

        $this->info("Success, loaded: " . sizeof($list) . ", created: " . $savedCount . ", skipped: " . $skippedCount . ", exceed length: " . $exceedLengthCount);
    }
}
