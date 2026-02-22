<?php

use App\Filament\Pages\SendEmail;
use App\Filament\Pages\SendSMS;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Hostel;
use App\Models\Payment;
use App\Models\ReferralAgent;
use App\Models\ReferralPayoutRequest;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestableSendSMSPage extends SendSMS
{
    public function exposedResolveRecipients(array $data)
    {
        return $this->resolveRecipients($data);
    }
}

class TestableSendEmailPage extends SendEmail
{
    public function exposedResolveRecipients(array $data): array
    {
        return $this->resolveRecipients($data);
    }
}

it('submits staff registration when general staff is selected and hostel selector is required', function () {
    Addon::create([
        'name' => 'Staff Payroll',
        'slug' => 'staff-payroll',
        'version' => '1.0.0',
        'description' => 'Test',
        'is_active' => true,
    ]);

    if (!Schema::hasTable('staff_members')) {
        Schema::create('staff_members', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone', 32)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number', 64)->nullable();
            $table->string('department')->nullable();
            $table->string('category')->nullable();
            $table->string('job_title')->nullable();
            $table->text('address')->nullable();
            $table->json('meta')->nullable();
            $table->string('source_role')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('registered_via_link')->default(false);
            $table->boolean('is_general_staff')->default(true);
            $table->unsignedBigInteger('assigned_hostel_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    $owner = User::factory()->create(['role' => 'admin']);
    Hostel::create([
        'name' => 'Atlas Hostel',
        'address' => '12 Main Street',
        'city' => 'Lagos',
        'owner_id' => $owner->id,
        'price_per_month' => 1000,
        'total_capacity' => 100,
        'is_active' => true,
    ]);

    SystemSetting::setSetting('staff_payroll_registration_enabled', '1');
    SystemSetting::setSetting('staff_payroll_registration_token', 'qa-token');
    SystemSetting::setSetting('staff_payroll_registration_show_hostel_selector', '1');
    SystemSetting::setSetting('staff_payroll_registration_require_hostel_selector', '1');
    SystemSetting::setSetting('staff_payroll_registration_show_profile_image', '0');

    $response = $this->post(route('staff.register.store', ['token' => 'qa-token']), [
        'full_name' => 'Jane Staff',
        'email' => 'jane.staff@example.com',
        'phone' => '08030000000',
        'bank_name' => 'Demo Bank',
        'bank_account_name' => 'Jane Staff',
        'bank_account_number' => '0011223344',
        'is_general_staff' => '1',
    ]);

    $response->assertRedirect(route('staff.register.create', ['token' => 'qa-token']));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('staff_members', [
        'email' => 'jane.staff@example.com',
        'is_general_staff' => 1,
    ]);
});

it('resolves manager targeting correctly for send sms and send email pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'admin']);
    $hostelA = Hostel::create([
        'name' => 'Hostel A',
        'address' => 'Street A',
        'city' => 'City A',
        'owner_id' => $owner->id,
        'price_per_month' => 1200,
        'total_capacity' => 80,
        'is_active' => true,
    ]);
    $hostelB = Hostel::create([
        'name' => 'Hostel B',
        'address' => 'Street B',
        'city' => 'City B',
        'owner_id' => $owner->id,
        'price_per_month' => 1200,
        'total_capacity' => 80,
        'is_active' => true,
    ]);

    $managerA = User::factory()->create(['role' => 'manager', 'hostel_id' => $hostelA->id, 'phone' => '08011111111', 'email' => 'a@example.com']);
    $managerB = User::factory()->create(['role' => 'manager', 'hostel_id' => null, 'phone' => '08022222222', 'email' => 'b@example.com']);
    $managerC = User::factory()->create(['role' => 'manager', 'hostel_id' => $hostelB->id, 'phone' => '08033333333', 'email' => 'c@example.com']);
    $hostelA->managers()->attach($managerB->id);

    $this->actingAs($admin);

    $smsPage = new TestableSendSMSPage();
    $smsRecipients = $smsPage->exposedResolveRecipients([
        'recipient_type' => 'managers_hostel',
        'hostel_id' => $hostelA->id,
    ])->pluck('id')->all();
    expect($smsRecipients)->toContain($managerA->id, $managerB->id)->not->toContain($managerC->id);

    $emailPage = new TestableSendEmailPage();
    $emailRecipients = $emailPage->exposedResolveRecipients([
        'recipient_type' => 'managers_hostel',
        'hostel_id' => $hostelA->id,
    ]);
    expect($emailRecipients)->toContain('a@example.com', 'b@example.com')->not->toContain('c@example.com');
});

it('loads student referral dashboard and accepts payout request when addon is active', function () {
    Addon::create([
        'name' => 'Referral System',
        'slug' => 'referral-system',
        'version' => '1.0.0',
        'description' => 'Test',
        'is_active' => true,
    ]);

    SystemSetting::setSetting('referral_enabled', '1');
    SystemSetting::setSetting('referral_students_can_be_agents', '1');
    SystemSetting::setSetting('referral_min_payout', '10');

    $student = User::factory()->create(['role' => 'student']);
    ReferralAgent::create([
        'user_id' => $student->id,
        'name' => $student->name,
        'email' => $student->email,
        'phone' => '08040000000',
        'password' => bcrypt('secret'),
        'is_active' => true,
        'commission_type' => 'percentage',
        'commission_value' => 5,
        'balance' => 100,
        'total_earned' => 100,
        'total_paid' => 0,
    ]);

    $this->actingAs($student);
    $this->get(route('student.referrals.index'))
        ->assertOk()
        ->assertSee('Referral Dashboard');

    $this->post(route('student.referrals.payouts.store'), [
        'amount' => 50,
        'bank_name' => 'Demo Bank',
        'account_name' => 'Student Agent',
        'account_number' => '1234567890',
    ])->assertSessionHas('success');

    expect(ReferralPayoutRequest::count())->toBe(1);
});

it('shows and downloads student id card only with an active paid booking', function () {
    $student = User::factory()->create(['role' => 'student', 'phone' => '08050000000']);
    $owner = User::factory()->create(['role' => 'admin']);
    $hostel = Hostel::create([
        'name' => 'ID Hostel',
        'address' => 'Street C',
        'city' => 'City C',
        'owner_id' => $owner->id,
        'price_per_month' => 1000,
        'total_capacity' => 100,
        'is_active' => true,
    ]);
    $room = Room::create([
        'hostel_id' => $hostel->id,
        'room_number' => 'R-10',
        'type' => 'double',
        'capacity' => 2,
        'price_per_month' => 1000,
        'is_available' => true,
    ]);

    $this->actingAs($student);
    $this->get(route('student.id-card.show'))
        ->assertOk()
        ->assertSee('ID not available yet');

    $booking = Booking::create([
        'user_id' => $student->id,
        'room_id' => $room->id,
        'check_in_date' => now()->subDay()->toDateString(),
        'check_out_date' => now()->addDays(10)->toDateString(),
        'status' => 'approved',
        'total_amount' => 1000,
    ]);
    Payment::create([
        'booking_id' => $booking->id,
        'user_id' => $student->id,
        'amount' => 1000,
        'status' => 'paid',
        'payment_method' => 'manual',
        'transaction_id' => 'tx-1000',
        'payment_date' => now()->toDateString(),
    ]);

    $this->get(route('student.id-card.show'))
        ->assertOk()
        ->assertSee('STUDENT ID CARD');
    $this->get(route('student.id-card.download.svg'))
        ->assertOk()
        ->assertSee('<svg', false);
});
