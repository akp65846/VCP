<?php

namespace App\Console\Commands\Task;

use App\Enum\VideoConstant;
use App\Helpers\TaskLogger;
use App\Models\Video;
use Exception;
use Illuminate\Console\Command;

class ProcessApprovedVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:process-approved-video';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download videos from video url pool';

    public TaskLogger $logger;

    public function handle() {

        set_time_limit(90);

        $this->logger = new TaskLogger($this->signature);
        $this->logger->startTask();

        $loopExpiryTime = time() + 60;

        while(time() < $loopExpiryTime) {
            $approvedVideo = Video::query()
                ->where('status', VideoConstant::STATUS_ACTIVE)
                ->where('approval_status', VideoConstant::APPROVAL_STATUS_APPROVED)
                ->whereNull('media_id')
                ->first();

            if ($approvedVideo instanceof Video) {
                $videoID = $approvedVideo['id'];

                try {
                    $this->call('video:process-approved-video', [
                        'videoID' => $videoID
                    ]);

                    $this->logger->successOne("downloaded video id {$videoID}");
                } catch (Exception $e) {
                    $this->logger->failOne("failed to download video id {$videoID}: " . $e->getMessage());
                }
            }

            sleep(10);
        }

        $this->logger->endTask();
    }
}
