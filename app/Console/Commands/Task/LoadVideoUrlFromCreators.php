<?php

namespace App\Console\Commands\Task;

use App\Enum\ContentCreatorConstant;
use App\Enum\PlatformConstant;
use App\Helpers\TaskLogger;
use App\Models\ContentCreator;
use App\Models\Platform;
use Exception;
use Illuminate\Console\Command;

class LoadVideoUrlFromCreators extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:load-video-url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve a video url form creators';

    /**
     * @var ContentCreator []
     */
    private $creators;

    public TaskLogger $logger;

    public function handle() {

        $this->logger = new TaskLogger($this->signature);
        $this->logger->startTask();
        $this->creators = ContentCreator::query()->where('status', ContentCreatorConstant::STATUS_ACTIVE)->get();

        if (is_null($this->creators)) {
            $this->logger->critical("cannot fetch content creator");
            $this->logger->endTask();
            exit();
        }

        foreach ($this->creators as $creator) {

            if (empty($creator->platform_id)) {
                $this->logger->failOne("platform id not found for creator id {$creator->id}");
                continue;
            }

            $platform = Platform::query()->find($creator->platform_id);

            if (empty($platform) || empty($platform->group)) {
                $this->logger->failOne("platform object not found for platform id {$creator->platform_id}");
                continue;
            }

            switch($platform->group) {
                case PlatformConstant::GROUP_TIKTOK: {

                    try {
                        $savedCount = $this->call("retrieve:load-tiktok", [
                            'creatorID' => $creator->id
                        ]);
                        $this->logger->successOne("loaded {$savedCount} video url for $creator->id");
                    } catch (Exception $e) {
                        $this->logger->failOne($e);
                    }

                    break;
                }
                default: {
                   $this->logger->failOne("no handler for platform group {$platform->group}");
                   break;
                }
            }
        }

        $this->logger->endTask();
    }
}
