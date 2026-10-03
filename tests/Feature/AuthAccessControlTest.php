<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_access_account_manager(): void
    {
        $adminPosition = $this->createPosition('Admin', array_keys(User::moduleOptions()));
        $admin = $this->createUser('Admin User', 'admin@example.com', $adminPosition);
        $this->assertSame(array_keys(User::moduleOptions()), $adminPosition->module_access);
        $this->assertFalse(Schema::hasColumn('users', 'role'));
        $this->assertFalse(Schema::hasColumn('users', 'module_access'));

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->assertRedirect('/dashboard');

        $this->actingAs($admin)
            ->get('/admin/accounts')
            ->assertOk()
            ->assertSee('Main navigation')
            ->assertSee('User accounts')
            ->assertSee('Position Manager')
            ->assertDontSee('PM Summary Report')
            ->assertSee('<table', false)
            ->assertDontSee('System role')
            ->assertSee('id="create-account-drawer"', false)
            ->assertSee('id="edit-account-' . $admin->id . '"', false)
            ->assertSee('Select a position')
            ->assertSee('Create account');
    }

    public function test_position_without_admin_permissions_cannot_access_management_modules(): void
    {
        $position = $this->createPosition('Technician', ['dashboard', 'office-selection', 'pm-records-list']);
        $user = $this->createUser('User', 'user@example.com', $position);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect('/dashboard');

        $this->actingAs($user)->get('/admin/accounts')->assertForbidden();
        $this->get('/admin/positions')->assertForbidden();

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Profile')
            ->assertDontSee('Account Manager')
            ->assertDontSee('Position Manager');
    }

    public function test_admin_can_update_user_and_position_controls_access(): void
    {
        $admin = $this->createUser('Admin User', 'admin2@example.com', $this->createPosition('Admin', array_keys(User::moduleOptions())));
        $this->createPosition('Technician', ['dashboard']);
        $seniorPosition = $this->createPosition('Senior Technician', ['dashboard', 'office-selection', 'pm-records-list']);
        $user = $this->createUser('User', 'conductor2@example.com', Position::where('name', 'Technician')->firstOrFail());

        $this->actingAs($admin)
            ->put('/admin/accounts/' . $user->id, [
                'name' => 'Updated Conductor',
                'email' => 'updated-conductor@example.com',
                'position' => 'Senior Technician',
                'department' => 'MIS',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('updated-conductor@example.com', $user->email);
        $this->assertTrue(Hash::check('newpass123', $user->password));
        $this->assertSame($seniorPosition->name, $user->position);
        $this->assertTrue($user->hasModuleAccess('office-selection'));
    }

    public function test_admin_dashboard_shows_account_manager_profile_and_logout(): void
    {
        $admin = $this->createUser('Admin User', 'admin3@example.com', $this->createPosition('Admin', array_keys(User::moduleOptions())));

        $dashboardResponse = $this->actingAs($admin)
            ->get('/dashboard')
            ->assertSee('Account Manager')
            ->assertSee('href="' . route('admin.accounts') . '"', false)
            ->assertSee('Position Manager')
            ->assertSee('Profile')
            ->assertSee('href="' . route('profile') . '"', false)
            ->assertSee('Main navigation')
            ->assertDontSee('Logout');

        $this->assertSame(1, substr_count($dashboardResponse->getContent(), 'aria-label="Main navigation"'));

        $officeSelectionResponse = $this->get('/office-selection')->assertOk()->assertSee('Main navigation');
        $this->assertSame(1, substr_count($officeSelectionResponse->getContent(), 'aria-label="Main navigation"'));

        $this->get('/profile')
            ->assertOk()
            ->assertSee('Save profile')
            ->assertSee('Main navigation')
            ->assertSee('Logout');
    }

    public function test_user_can_update_own_profile_without_changing_rbac(): void
    {
        $position = $this->createPosition('Technician', ['dashboard', 'office-selection']);
        $user = $this->createUser('User', 'profile@example.com', $position);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Updated User',
                'email' => 'updated-profile@example.com',
                'department' => 'Operations',
                'password' => 'updatedpass123',
                'password_confirmation' => 'updatedpass123',
            ])
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Updated User', $user->name);
        $this->assertSame('updated-profile@example.com', $user->email);
        $this->assertTrue(Hash::check('updatedpass123', $user->password));
        $this->assertTrue($user->hasModuleAccess('office-selection'));
        $this->assertFalse($user->hasModuleAccess('office-manager'));
        $this->assertSame('Technician', $user->position);
    }

    public function test_admin_can_delete_another_account_but_not_their_own(): void
    {
        $admin = $this->createUser('Admin User', 'delete-admin@example.com', $this->createPosition('Admin', array_keys(User::moduleOptions())));
        $technicianPosition = $this->createPosition('Technician', ['dashboard']);
        $conductor = $this->createUser('User', 'delete-user@example.com', $technicianPosition);
        $officeId = DB::table('offices')->insertGetId([
            'name' => 'Deletion Test Office',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $recordId = DB::table('pm_records')->insertGetId([
            'office_id' => $officeId,
            'requested_by_name' => 'Office Representative',
            'position' => 'Manager',
            'date_started' => now()->toDateString(),
            'conducted_by' => $conductor->id,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete('/admin/accounts/' . $conductor->id)
            ->assertRedirect()
            ->assertSessionHas('message', 'Account deleted. Existing PM records were retained.');
        $this->assertDatabaseMissing('users', ['id' => $conductor->id]);
        $this->assertDatabaseHas('pm_records', ['id' => $recordId, 'conducted_by' => null]);

        $this->delete('/admin/accounts/' . $admin->id)
            ->assertRedirect()
            ->assertSessionHasErrors('account');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_manage_positions_and_renames_update_assigned_users(): void
    {
        $admin = $this->createUser('Admin User', 'position-admin@example.com', $this->createPosition('Admin', array_keys(User::moduleOptions())));
        $technician = $this->createPosition('Technician', ['dashboard']);
        $user = $this->createUser('Technician User', 'position-user@example.com', $technician);

        $this->actingAs($admin)
            ->get('/admin/positions')
            ->assertOk()
            ->assertSee('Module permissions')
            ->assertSee('Create position')
            ->assertDontSee('PM Summary Report')
            ->assertSee('id="create-position-drawer"', false)
            ->assertSee('id="edit-position-' . $technician->id . '"', false);

        $this->post('/admin/positions', ['name' => 'Senior Technician', 'module_access' => ['dashboard']])
            ->assertRedirect()
            ->assertSessionHas('message', 'Position created successfully.');

        $this->put('/admin/positions/' . $technician->id, ['name' => 'IT Support Technician', 'module_access' => ['dashboard', 'pm-records-list']])
            ->assertRedirect()
            ->assertSessionHas('message', 'Position updated successfully.');
        $this->assertSame('IT Support Technician', $user->fresh()->position);

        $this->delete('/admin/positions/' . $technician->id)
            ->assertRedirect()
            ->assertSessionHasErrors('position');
        $this->assertDatabaseHas('positions', ['id' => $technician->id]);
    }

    private function createPosition(string $name, array $modules): Position
    {
        return Position::updateOrCreate(['name' => $name], ['module_access' => $modules]);
    }

    private function createUser(string $name, string $email, Position $position): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('secret123'),
            'position' => $position->name,
            'department' => 'MIS',
        ]);
    }
}
