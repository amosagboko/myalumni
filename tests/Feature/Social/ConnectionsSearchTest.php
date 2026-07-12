<?php

namespace Tests\Feature\Social;

use App\Livewire\FriendRequestManager;
use App\Models\Alumni;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConnectionsSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    public function test_connections_search_finds_alumni_by_name_and_year(): void
    {
        $viewer = User::factory()->create(['name' => 'Viewer Alumni']);
        $viewer->assignRole('alumni');

        $match = User::factory()->create(['name' => 'Unique Search Target']);
        $match->assignRole('alumni');
        Alumni::create([
            'user_id' => $match->id,
            'matric_number' => 'MAT'.uniqid(),
            'programme' => 'B.Sc Economics',
            'department' => 'Economics',
            'faculty' => 'Social Sciences',
            'year_of_graduation' => 1987,
            'date_of_birth' => '1990-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2011,
            'gender' => 'female',
        ]);

        Livewire::actingAs($viewer)
            ->test(FriendRequestManager::class)
            ->set('search', 'Unique Search')
            ->assertSet('searchResults', fn ($results) => $results->contains(
                fn ($row) => $row['user']->id === $match->id
            ));

        Livewire::actingAs($viewer)
            ->test(FriendRequestManager::class)
            ->set('search', '1987')
            ->assertSet('searchResults', fn ($results) => $results->contains(
                fn ($row) => $row['user']->id === $match->id
            ));
    }
}
