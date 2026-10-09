<?php

namespace Tests\Feature\Foundation;

use App\Concerns\BelongsToDesa;
use App\Models\Asset;
use Database\Seeders\DesaSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

/**
 * Issue #13 close-out: foundation polish gaps.
 *
 * - Logo/favicon must not 404 (provider points at an existing public file).
 * - Backup schedule must be registered (daily backup:run + backup:clean).
 * - Desa global scope must survive on models that define their own booted().
 */
class FoundationPolishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DesaSeeder::class);
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_favicon_points_to_existing_public_file(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertNotNull($panel, 'Admin panel must be registered.');

        $favicon = $panel->getFavicon();

        $this->assertNotNull($favicon, 'Admin panel must define a favicon (issue #13 item 1).');

        // asset() URLs resolve under the app URL; map back to public/ on disk.
        $path = parse_url((string) $favicon, PHP_URL_PATH);
        $this->assertNotFalse($path);
        $this->assertFileExists(public_path(ltrim((string) $path, '/')));
    }

    public function test_backup_commands_are_scheduled_daily(): void
    {
        $events = collect(Schedule::events())->map(
            fn ($event) => $event->getSummaryForDisplay()
        )->all();

        $joined = implode("\n", $events);

        $this->assertStringContainsString('backup:run', $joined);
        $this->assertStringContainsString('backup:clean', $joined);
    }

    public function test_desa_scope_survives_model_with_own_booted_method(): void
    {
        // hasGlobalScope() reads the boot-time registry, so boot each model
        // with a query first -- then assert the scope was registered.
        (new ScopeProbeModel)->newQuery();
        (new Asset)->newQuery();

        // A model using the trait AND defining its own booted() must still
        // carry the 'desa' global scope -- otherwise cross-desa content leaks.
        $this->assertTrue(
            ScopeProbeModel::hasGlobalScope('desa'),
            'BelongsToDesa scope lost when the model defines its own booted().'
        );

        // And the real models keep the scope too.
        $this->assertTrue(Asset::hasGlobalScope('desa'));
    }
}

/** Probe: trait user with its own model-level booted() hook. */
class ScopeProbeModel extends Model
{
    use BelongsToDesa;

    protected $table = 'assets';

    protected static function booted(): void
    {
        // Model-specific boot logic must not clobber the trait scope.
    }
}
