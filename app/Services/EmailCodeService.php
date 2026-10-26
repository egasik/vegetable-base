<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\VerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmailCodeService
{
    public function send(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }
        $code = (string) random_int(100000, 999999);
        DB::transaction(function () use ($user, $code) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $current = DB::table('verification_codes')->where('user_id', $user->id)->first();
            if ($current && now()->diffInSeconds($current->sent_at, true) < 60) {
                throw ValidationException::withMessages(['code' => 'Повторный код можно запросить через минуту.']);
            }
            DB::table('verification_codes')->updateOrInsert(['user_id' => $user->id], [
                'email' => $user->email, 'code_hash' => Hash::make($code), 'attempts' => 0,
                'sent_at' => now(), 'expires_at' => now()->addMinutes(10), 'used_at' => null,
            ]);
            // Synchronous delivery: a transport failure rolls back the code and resend cooldown.
            $user->notify(new VerificationCode($code));
        });
    }

    public function verify(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $row = DB::table('verification_codes')->where('user_id', $user->id)->lockForUpdate()->first();
            if (! $row || $row->email !== $locked->email || $row->used_at || $row->attempts >= 5 || now()->greaterThan($row->expires_at)) {
                return false;
            }
            DB::table('verification_codes')->where('id', $row->id)->increment('attempts');
            if (! Hash::check($code, $row->code_hash)) {
                return false;
            }
            DB::table('verification_codes')->where('id', $row->id)->update(['used_at' => now()]);
            $locked->markEmailAsVerified();
            event(new Verified($locked));

            return true;
        });
    }
}
