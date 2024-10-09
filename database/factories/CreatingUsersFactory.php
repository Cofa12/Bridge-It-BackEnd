<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CreatingUsersFactory extends Factory
{

    protected $Database = 'sqlite';
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=>fake()->text(),
            'email'=>fake()->email(),
            'password'=>fake()->Hash::make('mahmoud20##20##'),
            'type'=>'regular',
        ];
    }
}
