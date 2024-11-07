<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendJoinGroupInvitationToDoctor extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private string $senderName, private string $groupName, private string $receiverEmail,private int $groupId)
    {
        //
    }

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
            ->view('mails.joinGroupInvitationToDoctor',['senderName'=>$this->senderName,
                'groupName'=>$this->groupName,
                'receiverEmail'=>$this->receiverEmail,
                'groupId'=>$this->groupId]);
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
