<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_switch_stores_selected_locale(): void
    {
        $this->get(route('lang.switch', 'en'))
            ->assertRedirect()
            ->assertSessionHas('locale', 'en');

        $this->get(route('lang.switch', 'id'))
            ->assertRedirect()
            ->assertSessionHas('locale', 'id');
    }

    public function test_web_pages_use_selected_locale_from_session(): void
    {
        $user = User::factory()->admin()->create();

        $this
            ->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Record Movement')
            ->assertSee('Items Need Restock')
            ->assertSee('Items')
            ->assertDontSee('Daftar Barang');

        $this
            ->actingAs($user)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Daftar Barang');
    }

    public function test_auth_pages_use_selected_locale_from_session(): void
    {
        $this
            ->withSession(['locale' => 'en'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to Dashboard')
            ->assertSee('Remember me on this device')
            ->assertSee('Create a new account')
            ->assertDontSee('Masuk ke Dashboard');
    }

    public function test_resource_pages_translate_static_content(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('barang.index'))
            ->assertOk()
            ->assertSee('Search and Group Item Data')
            ->assertSee('Displayed rows')
            ->assertSee('Item Table')
            ->assertDontSee('Cari dan Kelompokkan Data Barang');
    }

    public function test_purchase_report_pages_translate_static_content(): void
    {
        $user = User::factory()->admin()->create();

        $this
            ->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('laporan-pengeluaran.index'))
            ->assertOk()
            ->assertSee('Report Filter')
            ->assertSee('Monthly')
            ->assertSee('Total Transactions')
            ->assertSee('Expense Chart for Year')
            ->assertDontSee('Filter Laporan');

        $this
            ->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('laporan-pengeluaran.pdf'))
            ->assertOk()
            ->assertSee('Item Purchase Expense Report')
            ->assertSee('Print Report')
            ->assertSee('Report Period')
            ->assertDontSee('Cetak Laporan');
    }

    public function test_settings_pages_translate_static_content(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this
            ->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Website Settings')
            ->assertSee('Application Identity')
            ->assertSee('Website Name')
            ->assertSee('Save Changes')
            ->assertDontSee('Pengaturan Website');

        $this
            ->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('settings.accounts'))
            ->assertOk()
            ->assertSee('Account Management')
            ->assertSee('Add New Account')
            ->assertSee('Registered Account List')
            ->assertDontSee('Manajemen Akun');

        $this
            ->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('settings.access'))
            ->assertOk()
            ->assertSee('Access Management')
            ->assertSee('Access Rights Matrix')
            ->assertSee('Feature / Module')
            ->assertSee('Save Access Matrix')
            ->assertDontSee('Manajemen Akses');
    }
}
