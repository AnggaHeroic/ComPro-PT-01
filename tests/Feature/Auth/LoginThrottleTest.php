<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    private function attempt(string $email, string $password = 'salah-salah')
    {
        return $this->from('/login')->post('/login', ['email' => $email, 'password' => $password]);
    }

    public function test_wrong_attempts_do_not_reveal_how_many_tries_are_left(): void
    {
        $user = User::factory()->create();

        $this->attempt($user->email)->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->attempt($user->email)->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->assertGuest();

        $html = $this->followingRedirects()->from('/login')->post('/login', ['email' => $user->email, 'password' => 'salah-lagi'])->getContent();
        $this->assertStringNotContainsString('Sisa percobaan', $html);
        $this->assertStringNotContainsString('percobaan lagi', $html);
    }

    public function test_the_third_wrong_attempt_locks_logging_in_for_ten_seconds(): void
    {
        $user = User::factory()->create(['password' => bcrypt('sandi-benar-123')]);

        $this->attempt($user->email);
        $this->attempt($user->email);
        $this->attempt($user->email)->assertSessionHasErrors(['email' => 'Terlalu banyak percobaan salah. Coba lagi dalam beberapa menit.'])
            ->assertSessionHas('login_wait', 10);

        // Selama masa tunggu, bahkan password yang benar ditolak.
        $this->attempt($user->email, 'sandi-benar-123')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logging_in_works_again_after_the_ten_second_wait(): void
    {
        $user = User::factory()->create(['password' => bcrypt('sandi-benar-123')]);

        foreach (range(1, 3) as $ignored) {
            $this->attempt($user->email);
        }

        $this->travel(9)->seconds();
        $this->attempt($user->email, 'sandi-benar-123')->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->travel(2)->seconds();
        $this->attempt($user->email, 'sandi-benar-123')->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_the_counter_applies_to_any_email_and_a_correct_login_resets_it(): void
    {
        $user = User::factory()->create(['password' => bcrypt('sandi-benar-123')]);

        // Mengganti-ganti email tidak melewati batas.
        $this->attempt('a@example.test');
        $this->attempt('b@example.test');
        $this->attempt('c@example.test')->assertSessionHas('login_wait');

        $this->travel(11)->seconds();

        // Login berhasil mengosongkan hitungan salah sebelumnya.
        $this->attempt($user->email);
        $this->attempt($user->email, 'sandi-benar-123')->assertRedirect();
        auth()->logout();
        $this->attempt($user->email)->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    }

    public function test_the_login_page_counts_down_on_the_button_after_a_lockout(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 3) as $ignored) {
            $this->attempt($user->email);
        }

        $this->followingRedirects()->from('/login')->get('/login')->assertOk()->assertSee('loginLock(10)', false);
        $this->get('/login')->assertSee('loginLock(0)', false)->assertSee('Coba lagi dalam');
    }
}
