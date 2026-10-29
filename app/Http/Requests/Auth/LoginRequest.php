<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Coba masuk. Salah memasukkan email atau password sebanyak batas (admin.login.max_attempts)
     * mengunci percobaan berikutnya selama admin.login.lockout_seconds detik.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Tanpa "ingat saya": sesi berakhir saat browser ditutup atau setelah tidak aktif.
        if (! Auth::attempt($this->only('email', 'password'))) {
            $max = (int) config('admin.login.max_attempts');
            $attempts = RateLimiter::hit($this->attemptsKey(), 300);

            if ($attempts >= $max) {
                RateLimiter::clear($this->attemptsKey());
                RateLimiter::hit($this->lockKey(), (int) config('admin.login.lockout_seconds'));

                $this->throwLockout((int) config('admin.login.lockout_seconds'));
            }

            // Sisa percobaan sengaja tidak ditampilkan.
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($this->attemptsKey());
    }

    /**
     * Tolak percobaan selama masa tunggu.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->lockKey(), 1)) {
            return;
        }

        event(new Lockout($this));

        $this->throwLockout(RateLimiter::availableIn($this->lockKey()));
    }

    private function throwLockout(int $seconds): never
    {
        // Dibaca halaman login untuk hitung mundur pada tombol Masuk.
        session()->flash('login_wait', $seconds);

        throw ValidationException::withMessages([
            'email' => 'Terlalu banyak percobaan salah. Coba lagi dalam beberapa menit.',
        ]);
    }

    /** Dihitung per alamat IP, supaya mengganti-ganti email tidak melewati batas. */
    private function attemptsKey(): string
    {
        return 'login-attempts|'.$this->ip();
    }

    private function lockKey(): string
    {
        return 'login-lock|'.$this->ip();
    }
}
