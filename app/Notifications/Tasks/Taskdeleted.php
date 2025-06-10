<?php

namespace App\Notifications\Tasks;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Taskdeleted extends Notification
{
    use Queueable;
    protected $task;

    /**
     * Create a new notification instance.
     */
    public function __construct($task)
    {
        //
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
            'task_id' => $this->task->id,
            'task_description' => $this->task->description,
            'deleted_by' => auth()->user()->name,
            'message' => 'The task has been deleted: ' . $this->task->title,
        ];
    }
}
