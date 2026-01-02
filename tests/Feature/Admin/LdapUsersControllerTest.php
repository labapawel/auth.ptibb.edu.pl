<?php

namespace Tests\Feature\Admin;

use App\Jobs\CreateLdapUserJob;
use App\Ldap\Group;
use App\Ldap\User as LdapUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use LdapRecord\Laravel\Testing\DirectoryFake;
use Tests\TestCase;

class LdapUsersControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'username' => 'admin',
            'permission' => [User::PERMISSION_ADMIN],
        ]);

        $this->actingAs($admin);

        return $admin;
    }

    public function test_index_displays_directory_users(): void
    {
        DirectoryFake::setup('default');
        cache()->forget('ldap_users_list');

        LdapUser::create([
            'objectClass' => LdapUser::$objectClasses,
            'cn' => 'John Doe',
            'givenname' => 'John',
            'sn' => 'Doe',
            'mail' => 'john@example.test',
            'uid' => 'jdoe',
        ]);

        $this->actingAsAdmin();

        $response = $this->get(route('ldap.users.index'));

        $response->assertOk();
        $response->assertViewHas('users', function ($users) {
            $match = $users->first(fn ($user) => $user->uid === 'jdoe');

            return $match && $match->cn === 'John Doe';
        });
    }

    public function test_create_displays_available_groups(): void
    {
        DirectoryFake::setup('default');
        cache()->forget('ldap_groups_for_create');

        Group::create([
            'objectClass' => Group::$objectClasses,
            'cn' => 'Teachers',
            'description' => 'Teaching staff',
            'gidnumber' => 1001,
        ]);

        $this->actingAsAdmin();

        $response = $this->get(route('ldap.users.create'));

        $response->assertOk();
        $response->assertViewHas('groups', function ($groups) {
            return collect($groups)->contains(function ($group) {
                return $group['cn'] === 'Teachers' && $group['description'] === 'Teaching staff';
            });
        });
    }

    public function test_store_dispatches_job(): void
    {
        DirectoryFake::setup('default');
        $this->actingAsAdmin();

        Queue::fake();

        $payload = [
            'cn' => 'Jane Doe',
            'sn' => 'Doe',
            'givenname' => 'Jane',
            'uid' => 'jane',
            'mail' => 'jane@example.test',
            'userpassword' => 'SuperSecret1!',
            'groups' => ['Teachers'],
        ];

        $response = $this->post(route('ldap.users.store'), $payload);

        $response->assertRedirect(route('ldap.users.index'));
        $response->assertSessionHas('success');

        Queue::assertPushed(CreateLdapUserJob::class, function ($job) use ($payload) {
            $reflection = new \ReflectionClass($job);
            $property = $reflection->getProperty('userData');
            $property->setAccessible(true);
            $data = $property->getValue($job);

            return $data['uid'] === $payload['uid'] && $data['cn'] === $payload['cn'];
        });
    }
}
