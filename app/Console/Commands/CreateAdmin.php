<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('zenray:create-admin {email? : Email address} {--name= : Full name} {--password= : Password (prompted if omitted)}')]
#[Description('Create an admin account, or promote an existing account to admin')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Email address');

        if ($user = User::query()->where('email', $email)->first()) {
            if (! $this->confirm("{$email} already exists. Make this account an admin?", true)) {
                return self::FAILURE;
            }

            $user->forceFill(['role' => User::ROLE_ADMIN])->save();
            $this->info("{$email} is now an admin.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?? $this->ask('Full name');
        $password = $this->option('password') ?? $this->secret('Password (min. 8 characters)');

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'password' => ['required', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create(compact('name', 'email', 'password'));
        $user->forceFill(['role' => User::ROLE_ADMIN, 'email_verified_at' => now()])->save();

        $this->info("Admin account created for {$email}. Sign in at ".route('login'));

        return self::SUCCESS;
    }
}
