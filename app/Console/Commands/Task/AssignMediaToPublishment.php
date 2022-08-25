<?php

namespace App\Console\Commands\Task;

use App\Enum\PublishmentConstant;
use App\Helpers\TaskLogger;
use App\Models\Media;
use App\Models\Publishment;
use Illuminate\Console\Command;

class AssignMediaToPublishment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:assign-media-to-publishment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign downloaded video id to existing publishment';

    public TaskLogger $logger;

    public function handle()
    {
        $skippedCount = 0;
        $assignedCount = 0;

        $this->logger = new TaskLogger($this->signature);
        $this->logger->startTask();

        $publishments = Publishment::query()->whereNull('media_id')->whereNull('uploaded_time')->where('status', PublishmentConstant::STATUS_ACTIVE)->get();

        foreach ($publishments as $publishment) {

            $media = Media::query()->where('video_id', $publishment->video_id)->first();

            if (empty($media)) {
                $skippedCount++;
                continue;
            }

            $publishment->update([
                'media_id' => $media->id
            ]);
            $assignedCount++;
        }

        $this->logger->successAll("assigned {$assignedCount}, skipped {$skippedCount}");
        $this->logger->endTask();
    }
}
