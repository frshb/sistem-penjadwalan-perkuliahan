<?php

namespace Tests\Feature\View\PilihTahun\Blade;

use Tests\TestCase;

class PhpTest extends TestCase
{
    /**
     * A basic view test example.
     */
    public function test_it_can_render(): void
    {
        $contents = $this->view('pilih-tahun.blade.php', [
            //
        ]);

        $contents->assertSee('');
    }
}
