<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;
    protected $videoFile;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video, $videoFile)
    {
        $this->video = $video;
        $this->videoFile = $videoFile;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Define the permanent path
        $permanentPath = 'videos/' . time() . '.' . $this->videoFile->getClientOriginalExtension();

        // Store the video directly in the permanent location
        Storage::disk('public')->put($permanentPath, file_get_contents($this->videoFile));

        // Update the video record with the new path
        $this->video->update([
            'video_path' => Storage::url($permanentPath),
        ]);

        // Additional video processing can go here (e.g., transcoding, thumbnails, etc.)
    }
}
