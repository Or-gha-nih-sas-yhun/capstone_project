<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Resident;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_page_renders_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign In');
        $response->assertSee('Register');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        // Seed a temporary user
        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@user.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@user.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_resident_registration_creates_profile_and_inactive_account()
    {
        $response = $this->post('/register', [
            'first_name' => 'Maria Clara',
            'last_name' => 'Santos',
            'middle_name' => 'Dela Cruz',
            'gender' => 'Female',
            'birthdate' => '2000-01-01',
            'civil_status' => 'Single',
            'contact_number' => '09123456789',
            'purok' => 'Purok 1',
            'years_of_residency' => 5,
            'email' => 'clara@santos.com',
            'username' => 'mariaclara',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('success');

        // Check resident profile exists
        $resident = Resident::where('email', 'clara@santos.com')->first();
        $this->assertNotNull($resident);
        $this->assertEquals('Maria Clara', $resident->first_name);

        // Check user account exists, has a verification code, is unverified, and inactive (pending approval)
        $user = User::where('username', 'mariaclara')->first();
        $this->assertNotNull($user);
        $this->assertEquals('resident', $user->role);
        $this->assertEquals('inactive', $user->status);
        $this->assertNotNull($user->verification_code);
        $this->assertNull($user->email_verified_at);
        $this->assertEquals($resident->id, $user->resident_id);
    }

    public function test_user_cannot_login_without_email_verification()
    {
        $user = User::create([
            'username' => 'unverifieduser',
            'email' => 'unverified@user.com',
            'password' => Hash::make('secret123'),
            'role' => 'resident',
            'status' => 'inactive',
            'verification_code' => '123456',
            'email_verified_at' => null,
        ]);

        $response = $this->post('/login', [
            'username' => 'unverifieduser',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('error', 'Please verify your email address first.');
        $this->assertGuest();
    }

    public function test_user_can_verify_email_with_correct_code()
    {
        $user = User::create([
            'username' => 'verifyme',
            'email' => 'verify@me.com',
            'password' => Hash::make('secret123'),
            'role' => 'resident',
            'status' => 'inactive',
            'verification_code' => '654321',
            'email_verified_at' => null,
        ]);

        $response = $this->post('/verify-email', [
            'email' => 'verify@me.com',
            'code' => '654321',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Email verification successful! Your account is now pending approval by the administrator.');

        $user->refresh();
        $this->assertNull($user->verification_code);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_cannot_verify_email_with_incorrect_code()
    {
        $user = User::create([
            'username' => 'verifyme2',
            'email' => 'verify2@me.com',
            'password' => Hash::make('secret123'),
            'role' => 'resident',
            'status' => 'inactive',
            'verification_code' => '654321',
            'email_verified_at' => null,
        ]);

        $response = $this->post('/verify-email', [
            'email' => 'verify2@me.com',
            'code' => '111111',
        ]);

        $response->assertSessionHas('error', 'Invalid verification code. Please check and try again.');

        $user->refresh();
        $this->assertEquals('654321', $user->verification_code);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_resend_verification_code()
    {
        $user = User::create([
            'username' => 'resendme',
            'email' => 'resend@me.com',
            'password' => Hash::make('secret123'),
            'role' => 'resident',
            'status' => 'inactive',
            'verification_code' => '000000',
            'email_verified_at' => null,
        ]);

        $response = $this->post('/verify-email/resend', [
            'email' => 'resend@me.com',
        ]);

        $response->assertSessionHas('success', 'A new verification code has been sent to your email.');

        $user->refresh();
        $this->assertNotEquals('000000', $user->verification_code);
        $this->assertNotNull($user->verification_code);
    }

    public function test_resident_can_request_password_change_with_otp()
    {
        // Seed resident and linked user
        $resident = Resident::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'birthdate' => '1990-01-01',
            'civil_status' => 'Single',
            'email' => 'juan@delacruz.com',
            'address' => 'Barangay Pili, Madridejos, Cebu',
            'status' => 'active',
        ]);

        $user = User::create([
            'username' => 'juandelacruz',
            'email' => 'juan@delacruz.com',
            'password' => Hash::make('oldpassword123'),
            'role' => 'resident',
            'status' => 'active',
            'resident_id' => $resident->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/resident/profile', [
            'action' => 'change_password',
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('password_change_pending', true);
        $this->assertTrue(session()->has('password_change_otp'));
        $this->assertTrue(session()->has('password_change_new'));
    }

    public function test_resident_can_verify_password_change_otp()
    {
        $resident = Resident::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'birthdate' => '1990-01-01',
            'civil_status' => 'Single',
            'email' => 'juan@delacruz.com',
            'address' => 'Barangay Pili, Madridejos, Cebu',
            'status' => 'active',
        ]);

        $user = User::create([
            'username' => 'juandelacruz',
            'email' => 'juan@delacruz.com',
            'password' => Hash::make('oldpassword123'),
            'role' => 'resident',
            'status' => 'active',
            'resident_id' => $resident->id,
            'email_verified_at' => now(),
        ]);

        // Put pending change info in the session
        session([
            'password_change_otp' => '999999',
            'password_change_new' => Hash::make('newpassword123'),
        ]);

        $response = $this->actingAs($user)->post('/resident/profile', [
            'action' => 'verify_password_otp',
            'otp_code' => '999999',
        ]);

        $response->assertSessionHas('success', 'Password changed successfully!');
        $this->assertFalse(session()->has('password_change_otp'));
        $this->assertFalse(session()->has('password_change_new'));

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_resident_cannot_verify_password_change_otp_with_incorrect_code()
    {
        $resident = Resident::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'birthdate' => '1990-01-01',
            'civil_status' => 'Single',
            'email' => 'juan@delacruz.com',
            'address' => 'Barangay Pili, Madridejos, Cebu',
            'status' => 'active',
        ]);

        $user = User::create([
            'username' => 'juandelacruz',
            'email' => 'juan@delacruz.com',
            'password' => Hash::make('oldpassword123'),
            'role' => 'resident',
            'status' => 'active',
            'resident_id' => $resident->id,
            'email_verified_at' => now(),
        ]);

        session([
            'password_change_otp' => '999999',
            'password_change_new' => Hash::make('newpassword123'),
        ]);

        $response = $this->actingAs($user)->post('/resident/profile', [
            'action' => 'verify_password_otp',
            'otp_code' => '111111',
        ]);

        $response->assertSessionHas('error', 'Invalid verification code. Please check and try again.');
        $response->assertSessionHas('password_change_pending', true);

        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }
}
