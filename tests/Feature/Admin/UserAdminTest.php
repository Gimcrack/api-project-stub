<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_user_with_valid_attributes_can_be_created_by_an_admin()
    {
        $this->withoutExceptionHandling();

        $atts = [
            'name' => 'Name Lastname',
            'email' => 'email@example.com',
            'password' => 'Valid Password',
            'admin_flag' => false
        ];

        $this
            ->api()
            ->actingAsAdmin()
            ->post("admin/users", $atts)
            ->response()
            ->assertStatus(201);

        // don't check the password field since it will be hashed
        unset($atts['password']);

        $this->assertDatabaseHas('users', $atts);
    }

    /** @test */
    function a_user_wont_be_created_without_a_name()
    {
        $atts = make_array(User::class, [
            'name' => null
        ]);

        $this
            ->api()
            ->actingAsAdmin()
            ->post("admin/users", $atts)
            ->response()
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->assertDatabaseMissing('users', $atts);
    }

    /** @test */
    function a_user_wont_be_created_without_a_valid_email()
    {
        $atts = make_array(User::class, [
            'email' => 'bad-email'
        ]);

        $this
            ->api()
            ->actingAsAdmin()
            ->post("admin/users", $atts)
            ->response()
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('users', $atts);
    }

    /** @test */
    function a_user_wont_be_created_without_a_valid_password()
    {
        $atts = make_array(User::class, [
            'password' => '12345678'
        ]);

        $this
            ->api()
            ->actingAsAdmin()
            ->post("admin/users", $atts)
            ->response()
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');

        $this->assertDatabaseMissing('users', $atts);
    }

    /** @test */
    function a_user_can_be_promoted_to_admin_by_another_admin()
    {
        $user = create(User::class);

        $this
            ->api()
            ->actingAsAdmin()
            ->post("admin/users/{$user->id}/promote")
            ->response()
            ->assertStatus(202);

        $this->assertTrue( $user->fresh()->isAdmin() );
    }

    /** @test */
    function an_admin_can_be_demoted_to_user_by_another_admin()
    {
        $user = create_state(User::class,'admin');

        $this
            ->api()
            ->actingAsAdmin()
            ->post("admin/users/{$user->id}/demote")
            ->response()
            ->assertStatus(202);

        $this->assertFalse( $user->fresh()->isAdmin() );
    }

    /** @test */
    function a_user_cannot_be_promoted_to_admin_by_a_nonadmin()
    {
        $user = create(User::class);

        $this
            ->api()
            ->actingAsUser()
            ->post("admin/users/{$user->id}/promote")
            ->response()
            ->assertStatus(422);

        $this->assertFalse( $user->fresh()->isAdmin() );
    }

    /** @test */
    function a_listing_of_users_can_be_retrieved_by_an_admin()
    {
        create(User::class,3);

        $this
            ->api()
            ->actingAsAdmin()
            ->get("admin/users")
            ->response()
            ->assertStatus(200);

        $this->assertJsonCount(4);
    }

    /** @test */
    function a_nonadmin_can_be_deleted_by_an_admin()
    {
        $user = create(User::class);

        $this
            ->api()
            ->actingAsAdmin()
            ->delete("admin/users/{$user->id}")
            ->response()
            ->assertStatus(202);

        $this->assertDatabaseMissing('users', $user->toArray());
    }

    /** @test */
    function an_admin_cannot_be_deleted()
    {
        $user = create_state(User::class,'admin');

        $this
            ->api()
            ->actingAsAdmin()
            ->delete("admin/users/{$user->id}")
            ->response()
            ->assertStatus(403);

        $this->assertDatabaseHas('users', $user->toArray());
    }

    /** @test */
    public function it_can_be_updated() {
        $this->withoutExceptionHandling();

        $user = create(User::class);

        $this->api()
            ->actingAsAdmin()
            ->patch("admin/users/{$user->id}", [
                'name' => 'valid name',
                'email' => 'newEmail@example.com'
            ])
            ->response()
            ->assertStatus(202);

        $this->assertDatabaseHas('users', [
            'email' => 'newEmail@example.com'
        ]);
    }
}