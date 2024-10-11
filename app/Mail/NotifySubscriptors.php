<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifySubscriptors extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $subjects;
    /**
     * Create a new message instance.
     */
    public function __construct($content,$subjects)
    {
        //
//        dd($subject);
        $this->content = $content;
        $this->subjects = $subjects;

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notify Subscriptors',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        return new Content(
            view: 'mails.notify_subscriptors',
            with: ['content' => $this->content, 'subjects' => $this->subjects]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
