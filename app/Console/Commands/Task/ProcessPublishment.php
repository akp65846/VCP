<?php

namespace App\Console\Commands\Task;

use App\Enum\PlatformConstant;
use App\Enum\PublishmentConstant;
use App\Helpers\TaskLogger;
use App\Models\Platform;
use App\Models\Publishment;
use Exception;
use Illuminate\Console\Command;

class ProcessPublishment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:process-publishment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload publishment to platforms';

    public TaskLogger $logger;

    public function handle() {

        $uploadedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        $this->logger = new TaskLogger($this->signature);
        $this->logger->startTask();

        $publishments = Publishment::query()
            ->whereNotNull('media_id')
            ->where('status', PublishmentConstant::STATUS_ACTIVE)
            ->whereNull('uploaded_time')
            ->whereDate('scheduled_time', '<=', now())
            ->get();

        foreach ($publishments as $publishment) {
            $targetPlatform = Platform::query()->find($publishment->target_platform_id);

            if (empty($targetPlatform)) {
                $skippedCount++;
                continue;
            }

            $platformGroup = $targetPlatform['group'];

            $publishment->update([
                'upload_trial_times' => $publishment->upload_trial_times + 1
            ]);

            switch ($platformGroup) {
                case PlatformConstant::GROUP_YOUTUBE : {
                    try {
                        $this->call("publish:upload-youtube", [
                            'publishmentID' => $publishment->id
                        ]);
                        $this->logger->successOne("published id {$publishment->id}");
                    } catch (Exception $e) {
                        $failedCount++;
                        $this->logger->failOne("failed to publish id {$publishment->id}:" . $e);
                    }
                    break;
                }
                default : {
                    $skippedCount++;
                    break;
                }
            }

        }

        $this->logger->successAll("uploaded {$uploadedCount}, failed {$failedCount}, skipped {$skippedCount}");
        $this->logger->endTask();
    }
}
