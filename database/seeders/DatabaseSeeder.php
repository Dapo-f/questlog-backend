<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Add more ids by copying the number from /game/{id} URLs on your site
        $gameIds = [3498, 616693, 3328, 4200, 28, 12020, 4062, 5286, 452650];

        $statuses = ['playing', 'completed', 'wishlist', 'dropped'];
        $ratings = [5, 4.5, 4, 3.5, 3, 2.5];

        $reviewTexts = [
            'Really enjoyed this one, the pacing kept me hooked.',
            'Great art style, but the story lost me halfway.',
            'Easily one of the best games I have played this year.',
            'Solid gameplay loop. Gets repetitive after a while.',
            'The soundtrack alone makes it worth it.',
            'Not for everyone, but I loved it.',
            'Bugs aside, this is a lot of fun.',
            'Beautiful world, tight controls.',
        ];

        $usernames = [
            'demo_player', 'PixelNomad', 'ShadowKnight', 'LunaPlays', 'ByteBandit',
            'CritHit', 'RogueWave', 'NeonDrifter', 'QuestMaker', 'LootGoblin',
            'FrostByte', 'AceOfSpades', 'GlitchHunter', 'MapleStrike', 'OrbitRunner',
            'SilentFox', 'TurboTuna', 'VoidWalker', 'CobaltRush', 'DuskRider',
            'EmberFall', 'HexMaster', 'IronPulse', 'JadeSpecter', 'KiloKnight',
            'NovaBlitz', 'PhantomLoop', 'RiftRaider', 'StormCaller', 'ZenithZed',
        ];

        $users = [];
        foreach ($usernames as $name) {
            // forceCreate skips $fillable, which is needed for is_verified
            $users[] = User::forceCreate([
                'username' => $name,
                'email' => strtolower($name) . '@example.com',
                'password' => 'password123', // hashed by the model cast
                'date_of_birth' => now()->subYears(rand(18, 35))->subDays(rand(0, 364))->toDateString(),
                'is_verified' => true,
            ]);
        }

        foreach ($users as $user) {
            // Library
            foreach (collect($gameIds)->shuffle()->take(rand(3, 6)) as $gameId) {
                $user->libraryEntries()->create([
                    'rawg_id' => $gameId,
                    'status' => $statuses[array_rand($statuses)],
                    'hours_played' => rand(0, 120),
                ]);
            }

            // Reviews (0 to 3 per user, never the same game twice)
            foreach (collect($gameIds)->shuffle()->take(rand(0, 3)) as $gameId) {
                $user->reviews()->create([
                    'rawg_id' => $gameId,
                    'rating' => $ratings[array_rand($ratings)],
                    'body' => $reviewTexts[array_rand($reviewTexts)],
                ]);
            }

            // Follows (never yourself, never the same person twice)
            $others = collect($users)->where('id', '!=', $user->id)->shuffle()->take(rand(2, 10));
            foreach ($others as $other) {
                $user->following()->create(['followed_id' => $other->id]);
            }
        }
    }
}