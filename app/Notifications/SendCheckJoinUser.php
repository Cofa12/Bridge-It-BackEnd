<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCheckJoinUser extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private string $userName,
        private string $userEmail,
        private string $groupName,
        private string $adminName,
        private int $groupId,
        private string $position="member"
    )
    { }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->view('mails.acceptJoinUser',[
                'userName'=>$this->userName,
                'userEmail'=>$this->userEmail,
                'groupName'=>$this->groupName,
                'adminName'=>$this->adminName,
                'groupId'=>$this->groupId,
                'position'=>$this->position
            ]);
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
        ];
    }
}
