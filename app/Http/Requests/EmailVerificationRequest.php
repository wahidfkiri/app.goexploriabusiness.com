<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    private ?User $verifiedUser = null;

    public function authorize()
    {
        $user = $this->resolveUser();

        if (! $user) {
            return false;
        }

        if (! hash_equals((string) $user->getKey(), (string) $this->route('id'))) {
            return false;
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $this->route('hash'))) {
            return false;
        }

        $this->verifiedUser = $user;

        return true;
    }

    public function rules()
    {
        return [];
    }

    public function fulfill()
    {
        $user = $this->verifiedUser;

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
    }

    private function resolveUser(): ?User
    {
        $user = $this->user();

        if ($user) {
            return $user;
        }

        return User::find($this->route('id'));
    }
}
