<?php

namespace Tests\Feature;

use App\Livewire\Project\Actions;
use App\Livewire\Project\Filters;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectFilterResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_chip_clears_search_without_removing_other_filters(): void
    {
        Livewire::actingAs(User::factory()->create())->test(Filters::class)
            ->set('search', 'Project query')
            ->set('yearSearch', ['2026'])
            ->call('clearSearch')
            ->assertSet('search', '')
            ->assertSet('yearSearch', ['2026'])
            ->assertDispatched('project-clear-search');

        Livewire::test(Actions::class)
            ->set('search', 'Project query')
            ->dispatch('project-clear-search')
            ->assertSet('search', '')
            ->assertDispatched('project-search-updated', search: '');
    }

    public function test_clear_all_also_synchronizes_the_separate_search_control(): void
    {
        Livewire::actingAs(User::factory()->create())->test(Filters::class)
            ->set('search', 'Project query')
            ->set('yearSearch', ['2026'])
            ->set('orderByProject', true)
            ->call('resetAll')
            ->assertSet('search', '')
            ->assertSet('yearSearch', [])
            ->assertSet('orderByProject', false)
            ->assertDispatched('project-clear-search')
            ->assertDispatched('project-filters-updated');
    }
}
