<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Follow;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- Demo User (login: demo@socly.app / password) ---
        $demoUser = User::create([
            'name' => 'Jan Novák',
            'username' => 'jan_novak',
            'email' => 'demo@socly.app',
            'password' => Hash::make('password'),
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face',
            'bio' => "Milovník umění a dobrého jídla\nPraha",
            'is_verified' => false,
            'is_vip' => false,
            'is_creator' => false,
            'subscription_price' => 0,
        ]);

        // --- Creators ---
        $karolina = User::create([
            'name' => 'Karolína M.',
            'username' => 'karolina.m',
            'email' => 'karolina@socly.app',
            'password' => Hash::make('password'),
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face',
            'cover_image' => 'https://images.unsplash.com/photo-1557682250-33bd709cbe85?w=800&h=400&fit=crop',
            'bio' => "Tvůrkyně exkluzivního obsahu\nPraha | Modelka & Influencerka\nNový obsah každý den",
            'is_verified' => true,
            'is_vip' => true,
            'is_creator' => true,
            'subscription_price' => 400,
        ]);

        $tereza = User::create([
            'name' => 'Tereza B.',
            'username' => 'tereza.b',
            'email' => 'tereza@socly.app',
            'password' => Hash::make('password'),
            'avatar' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&h=200&fit=crop&crop=face',
            'cover_image' => 'https://images.unsplash.com/photo-1579546929518-9e396f3cc809?w=800&h=400&fit=crop',
            'bio' => "Fitness & Lifestyle\nBrno\nSpolupráce v DM",
            'is_verified' => true,
            'is_vip' => false,
            'is_creator' => true,
            'subscription_price' => 250,
        ]);

        $nikola = User::create([
            'name' => 'Nikola S.',
            'username' => 'nikola.s',
            'email' => 'nikola@socly.app',
            'password' => Hash::make('password'),
            'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&h=200&fit=crop&crop=face',
            'cover_image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=800&h=400&fit=crop',
            'bio' => "Fotografka & Cestovatelka\nOstrava → svět",
            'is_verified' => false,
            'is_vip' => false,
            'is_creator' => true,
            'subscription_price' => 150,
        ]);

        $eliska = User::create([
            'name' => 'Eliška V.',
            'username' => 'eliska.v',
            'email' => 'eliska@socly.app',
            'password' => Hash::make('password'),
            'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face',
            'cover_image' => 'https://images.unsplash.com/photo-1614850523296-d8c1af93d400?w=800&h=400&fit=crop',
            'bio' => "Behind the scenes\nFotografka & Modelka\nExkluzivní obsah",
            'is_verified' => true,
            'is_vip' => true,
            'is_creator' => true,
            'subscription_price' => 200,
        ]);

        $adela = User::create([
            'name' => 'Adéla K.',
            'username' => 'adela.k',
            'email' => 'adela@socly.app',
            'password' => Hash::make('password'),
            'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&h=200&fit=crop&crop=face',
            'cover_image' => 'https://images.unsplash.com/photo-1557683316-973673baf926?w=800&h=400&fit=crop',
            'bio' => "Umělkyně & Tanečnice\nPlzeň",
            'is_verified' => true,
            'is_vip' => false,
            'is_creator' => true,
            'subscription_price' => 300,
        ]);

        $creators = [$karolina, $tereza, $nikola, $eliska, $adela];

        // --- Follows ---
        foreach ($creators as $creator) {
            $demoUser->following()->attach($creator->id);
        }

        // Cross-follow between creators
        $karolina->following()->attach([$tereza->id, $nikola->id, $eliska->id]);
        $tereza->following()->attach([$karolina->id, $eliska->id]);
        $nikola->following()->attach([$karolina->id, $adela->id]);
        $eliska->following()->attach([$karolina->id, $tereza->id, $nikola->id]);
        $adela->following()->attach([$karolina->id, $nikola->id]);

        // Add extra dummy followers for realism
        for ($i = 0; $i < 20; $i++) {
            $fakeUser = User::create([
                'name' => fake('cs_CZ')->name(),
                'username' => fake()->unique()->userName(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'avatar' => 'https://i.pravatar.cc/200?img=' . ($i + 10),
                'is_creator' => false,
            ]);
            // Each fake user follows random creators
            $followIds = collect($creators)->random(rand(2, 5))->pluck('id')->toArray();
            $fakeUser->following()->attach($followIds);
        }

        // --- Posts ---
        $postImages = [
            'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=600&h=750&fit=crop',
            'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=600&h=750&fit=crop',
            'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=600&h=750&fit=crop',
            'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=600&h=750&fit=crop',
            'https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?w=600&h=750&fit=crop',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=600&h=750&fit=crop',
            'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=600&h=750&fit=crop',
            'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=600&h=750&fit=crop',
        ];

        $captions = [
            'Nový exkluzivní obsah je tu! Děkuji za podporu...',
            'Exkluzivní fotky z nového shootu 📸',
            'Víkendová nálada ✨',
            'Behind the scenes z natáčení 🎬',
            'Nový obsah právě vychází!',
            'Dnes byl skvělý den na focení',
            'Speciální obsah pro mé odběratele 💕',
            'Praha v noci je magická',
        ];

        $postIndex = 0;
        foreach ($creators as $creator) {
            for ($i = 0; $i < 4; $i++) {
                $isLocked = $i % 2 === 1;
                $post = Post::create([
                    'user_id' => $creator->id,
                    'caption' => $captions[$postIndex % count($captions)],
                    'image' => $postImages[$postIndex % count($postImages)],
                    'is_locked' => $isLocked,
                    'price' => $isLocked ? rand(1, 4) * 50 : null,
                    'likes_count' => rand(200, 9000),
                    'comments_count' => rand(10, 500),
                    'is_video' => $i === 2,
                    'created_at' => now()->subHours(rand(1, 72)),
                ]);

                // Add some likes from demo user
                if (rand(0, 1)) {
                    Like::create(['user_id' => $demoUser->id, 'post_id' => $post->id]);
                }

                // Add comments
                $commentBodies = [
                    'Úžasné! 🔥', 'Nádherné fotky!', 'TOP!', 'Moc krásná',
                    'Super obsah', 'Skvělý shoot!', 'Wow! 😍', 'Pecka!',
                ];
                for ($c = 0; $c < rand(1, 3); $c++) {
                    Comment::create([
                        'user_id' => $creators[array_rand($creators)]->id,
                        'post_id' => $post->id,
                        'body' => $commentBodies[array_rand($commentBodies)],
                    ]);
                }

                $postIndex++;
            }
        }

        // --- Messages ---
        Message::create([
            'sender_id' => $karolina->id,
            'receiver_id' => $demoUser->id,
            'body' => 'Děkuji za podporu! 💕',
            'is_read' => false,
        ]);
        Message::create([
            'sender_id' => $karolina->id,
            'receiver_id' => $demoUser->id,
            'body' => 'Nový obsah bude zítra!',
            'is_read' => false,
        ]);
        Message::create([
            'sender_id' => $tereza->id,
            'receiver_id' => $demoUser->id,
            'body' => 'Nový obsah bude zítra!',
            'is_read' => true,
        ]);
        Message::create([
            'sender_id' => $nikola->id,
            'receiver_id' => $demoUser->id,
            'body' => 'Ráda, že se ti líbí 😊',
            'is_read' => true,
        ]);
        Message::create([
            'sender_id' => $eliska->id,
            'receiver_id' => $demoUser->id,
            'body' => 'Už brzy bude živé vysílání!',
            'is_read' => false,
        ]);
        Message::create([
            'sender_id' => $adela->id,
            'receiver_id' => $demoUser->id,
            'body' => 'Díky za tip! ❤️',
            'is_read' => true,
        ]);
    }
}
