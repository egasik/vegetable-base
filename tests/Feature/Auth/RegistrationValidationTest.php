<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $changes = []): array
    {
        return array_replace(['name' => 'Егор', 'last_name' => 'Иванов', 'phone' => '+7 (999) 123-45-67',
            'email' => 'test@example.com', 'password' => 'Password1!', 'password_confirmation' => 'Password1!'], $changes);
    }

    public function test_phone_formats_are_normalized_and_role_cannot_be_injected(): void
    {
        Notification::fake();
        foreach (['89991234567', '9991234567', '+7 (999) 123-45-67', '+7-(999)-123-45-67'] as $i => $phone) {
            $this->post('/register', $this->data(['phone' => $phone, 'email' => " TEST{$i}@EXAMPLE.COM ",
                'last_name' => ' Иванова-Петрова ', 'role' => 'admin']))->assertSessionHasNoErrors()->assertRedirect('/verify-email');
            $this->assertDatabaseHas('users', ['email' => "test{$i}@example.com", 'phone' => '+7-(999)-123-45-67',
                'last_name' => 'Иванова-Петрова', 'role' => 'guest']);
            $this->post('/logout');
        }
    }

    public function test_invalid_data_is_rejected_without_creating_users(): void
    {
        foreach (['name' => 'иван', 'last_name' => 'Иванов123', 'middle_name' => '<script>',
            'phone' => '+1 (999) 123-45-67', 'email' => 'invalid', 'password' => 'пароль123!'] as $field => $value) {
            $this->from('/register')->post('/register', $this->data([$field => $value]))->assertSessionHasErrors($field);
        }
        foreach (['short1!', 'Password!', 'Password1', 'Password 1!', str_repeat('A', 73).'1!'] as $password) {
            $this->post('/register', $this->data(['password' => $password, 'password_confirmation' => $password]))->assertSessionHasErrors('password');
        }
        $this->post('/register', $this->data(['password_confirmation' => 'Different1!']))->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_duplicate_email_is_checked_without_case_sensitivity(): void
    {
        User::factory()->create(['email' => 'Test@Example.com']);
        $this->post('/register', $this->data())->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }
}
