<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JenisDonasi>
 */
class JenisDonasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // unique(): jenis_donasis.nama unik, dan dua Donasi::factory() dalam satu
        // test tidak boleh menabrak constraint jenis_donasis.nama.
        return [
            'nama' => ucfirst($this->faker->unique()->word()),
            'deskripsi' => $this->faker->sentence,
            'apakah_barang' => $this->faker->boolean(20), // 20% chance untuk barang
            'membutuhkan_keterangan_tambahan' => $this->faker->boolean(30),
            'aktif' => true,
        ];
    }
}
