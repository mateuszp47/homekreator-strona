<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Support\Catalog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogImagesTest extends TestCase
{
    public function test_product_and_project_cover_and_gallery_are_readable_after_upload(): void
    {
        Storage::fake('public');
        config(['filesystems.disks.public.url' => 'https://old-host.invalid/storage']);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        foreach (['produkty' => Product::class, 'realizacje' => Project::class] as $kind => $model) {
            $this->post('/admin/'.$kind, [
                'name' => 'Test '.$kind,
                'category' => 'domki',
                'description' => 'Opis testowy.',
                'sort_order' => 0,
                'is_published' => 1,
                'image' => UploadedFile::fake()->image('cover.png', 100, 100),
                'gallery' => [UploadedFile::fake()->image('gallery.jpg', 120, 100)],
            ])->assertSessionHasNoErrors()->assertRedirect();

            $item = $model::where('name', 'Test '.$kind)->firstOrFail();
            $this->assertCount(2, $item->gallery);
            foreach ($item->gallery as $path) {
                Storage::disk('public')->assertExists($path);
                $url = Catalog::imageUrl($path);
                $this->assertStringStartsWith('/media/catalog/', $url);
                $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/webp');
            }

            $this->get('/admin/'.$kind.'/'.$item->id.'/edytuj')->assertOk()
                ->assertSee(Catalog::imageUrl($item->image), false);
            $this->get(($kind === 'produkty' ? '/produkty/' : '/realizacje/').$item->slug)
                ->assertOk()->assertSee(Catalog::imageUrl($item->image), false);
        }
    }

    public function test_missing_and_non_image_files_are_not_served(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('catalog/private.txt', 'private');
        $this->get('/media/catalog/missing.webp')->assertNotFound();
        $this->get('/media/catalog/private.txt')->assertNotFound();
        $this->get('/media/catalog/..%2Fprivate.txt')->assertNotFound();
    }
}
