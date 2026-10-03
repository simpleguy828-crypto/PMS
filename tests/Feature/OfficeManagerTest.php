<?php

namespace Tests\Feature;

use App\Livewire\OfficeManager;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfficeManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_table_search_and_name_sort_are_available(): void
    {
        Office::create(['name' => 'Alpha Office', 'status' => 'active', 'computer_count' => 2]);
        Office::create(['name' => 'Zulu Office', 'status' => 'active', 'computer_count' => 4]);

        $component = Livewire::test(OfficeManager::class)
            ->assertSee('office-search')
            ->assertSee('Name: A to Z')
            ->assertSeeInOrder(['Alpha Office', 'Zulu Office']);

        $component->set('sortOrder', 'name_desc')
            ->assertSeeInOrder(['Zulu Office', 'Alpha Office'])
            ->set('search', 'Alpha')
            ->assertSee('Alpha Office')
            ->assertDontSee('Zulu Office');
    }
        }
