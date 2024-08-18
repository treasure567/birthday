<?php

namespace App\Console\Commands;

use App\Http\Controllers\BirthdayController;
use Illuminate\Console\Command;

class SendBirthday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        BirthdayController::sendBirthday();
    }
}
