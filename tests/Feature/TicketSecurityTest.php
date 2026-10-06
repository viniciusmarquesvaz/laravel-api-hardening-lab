<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/tickets')->assertUnauthorized();
    }

    public function test_a_user_can_view_their_own_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id);
    }

    public function test_a_user_cannot_view_another_users_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/tickets/{$ticket->id}")->assertForbidden();
    }

    public function test_ticket_listing_is_scoped_to_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $ownTicket = Ticket::factory()->for($user)->create();
        Ticket::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownTicket->id);
    }

    public function test_protected_fields_cannot_be_mass_assigned_on_create(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets', [
            'title' => 'Synthetic security review',
            'description' => 'Validate ownership and status boundaries.',
            'user_id' => $otherUser->id,
            'status' => 'closed',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', Ticket::STATUS_OPEN);

        $ticket = Ticket::query()->sole();
        $this->assertSame($user->id, $ticket->user_id);
        $this->assertSame(Ticket::STATUS_OPEN, $ticket->status);
    }

    public function test_protected_fields_cannot_be_changed_on_update(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/tickets/{$ticket->id}", [
            'title' => 'Updated title',
            'user_id' => $otherUser->id,
            'status' => 'closed',
        ])->assertOk();

        $ticket->refresh();
        $this->assertSame('Updated title', $ticket->title);
        $this->assertSame($user->id, $ticket->user_id);
        $this->assertSame(Ticket::STATUS_OPEN, $ticket->status);
    }
}
