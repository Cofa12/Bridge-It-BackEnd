<?php

namespace App\Console\Commands;

use App\Mail\NotifySubscriptors;
use App\Models\Subscriptor;
use Illuminate\Console\Command;

class SendMailToSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-mail-to-subscribers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $subscribers = Subscriptor::all();
        foreach ($subscribers as $subscriber){
            $subscriber->notify(new NotifySubscriptors("1010","5555"));
        }
    }
}
