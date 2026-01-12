<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class GenerateExternalToken extends Command
{
    protected $signature = 'external:token';
    protected $description = 'Generate permanent token for external system';

    public function handle()
    {
        $user = User::where('email', 'external@system.sk')->first();

        if (!$user) {
            $this->error('External user not found');
            return;
        }


        $user->tokens()->delete();

        $token = $user->createToken('external-system')->accessToken;

        $this->info('EXTERNAL SYSTEM TOKEN:');
        $this->line($token);
    }
}
