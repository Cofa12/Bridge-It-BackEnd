<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendJoinGroupInvitation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private string $senderName, private string $groupName, private string $receiverEmail,private int $groupId,private string $position="member")
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
        if($this->position=='member'){
            return (new MailMessage)
                ->view('mails.joinGroupInvitation',['senderName'=>$this->senderName,
                    'groupName'=>$this->groupName,
                    'receiverEmail'=>$this->receiverEmail,
                    'groupId'=>$this->groupId,'position'=>$this->position]);
        }

        return (new MailMessage)
            ->view('mails.joinGroupInvitationToDoctor',['senderName'=>$this->senderName,
                'groupName'=>$this->groupName,
                'receiverEmail'=>$this->receiverEmail,
                'groupId'=>$this->groupId,'position'=>$this->position]);

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
