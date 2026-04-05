<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mail {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test mail configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'test@example.com';
        
        try {
            Mail::raw('Test email dari Assessment System', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email');
            });
            
            $this->info("Email berhasil dikirim ke {$email}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Gagal mengirim email: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
