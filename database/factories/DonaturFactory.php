<?php

namespace Database\Factories;

use App\Models\Pekerjaan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donatur>
 */
class DonaturFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->name,
            'alamat_detail' => $this->faker->address,
            'nomor_hp' => $this->faker->unique()->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'pekerjaan_id' => Pekerjaan::factory(),
        ];
    }
}
