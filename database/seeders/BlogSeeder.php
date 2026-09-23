<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['name' => 'Spotting fakes',   'description' => 'How to check a bottle is what it says it is', 'sort_order' => 1],
            ['name' => 'Skincare advice',  'description' => 'Routines, ingredients and what actually works', 'sort_order' => 2],
            ['name' => 'Product guides',   'description' => 'Which one to buy, and why',                     'sort_order' => 3],
            ['name' => 'Shop news',        'description' => 'New stock, offers and what we are up to',       'sort_order' => 4],
        ];

        foreach ($topics as $topic) {
            BlogCategory::firstOrCreate(['slug' => \Illuminate\Support\Str::slug($topic['name'])], $topic);
        }

        if (Post::exists()) {
            $this->addMenuLinks();

            return;
        }

        $this->seedFirstPost();
        $this->addMenuLinks();
    }

    /**
     * One finished post, written to show what a good one looks like —
     * a focus keyword, subheadings, links, a list and a proper summary.
     */
    private function seedFirstPost(): void
    {
        $category = BlogCategory::where('slug', 'spotting-fakes')->first();
        $author   = User::where('is_admin', true)->first();

        $content = <<<'TXT'
Counterfeit Korean skincare is not a small problem in Bangladesh. It is most of the market. The bottles look right, the boxes look right, and the price is usually only a little lower than the real thing — just enough to feel like a deal rather than a warning.

Here is how to check, in about two minutes, before you pay anyone.

## Start with the batch code

Every genuine Korean cosmetic carries a batch code, printed or stamped on the bottom of the box and again on the bottle. It is usually a short mix of letters and numbers.

- Find it on both the box and the bottle
- Check the two match
- Type it into the brand's own site, or a batch code checker

A real code returns a manufacture date. A fake one usually returns nothing, or a date that makes no sense — a batch made after the day you are reading it, for example.

This is the single best check, and it is the one forgers get wrong most often, because the code has to exist in the brand's own records.

## Look at the seal, not the wrapper

Plastic wrap is trivial to reapply, and sellers of fake stock do it well. The seal underneath is harder.

- The inner foil should be flush and unbroken
- The cap should click, not spin freely
- A dropper should sit straight and the rubber should be firm

A wrapper that is slightly loose, with a seam that does not line up, is a sign the box has been opened and rewrapped.

## Read the back of the box

Genuine imports carry the manufacturer name, the country of origin and an ingredient list in Korean and English. Fakes often get the ingredient list right — they copy it — but slip on the small print.

> If the text is blurry, crooked or misspelt anywhere on the box, stop. Real packaging is printed once, properly.

## Be suspicious of the price, not comforted by it

A serum that sells for 1,700 taka everywhere will not be 900 taka from someone with no shop, no returns and no phone number. If the discount is large enough to be exciting, that is the product telling you something.

Equally, a high price proves nothing. Plenty of counterfeits are sold at full price by people who know exactly what they are doing.

## Ask who imported it

This is the question most sellers cannot answer. Ask where the stock came from. A real importer will tell you, and will not mind you checking the batch code in front of them.

At [our shop](/shop) every product is imported in our own name, arrives sealed, and keeps its batch code intact — check it against the brand before you pay a taka. If a code ever fails, we take the item back and refund it in full.

## If you have already bought a fake

Stop using it. Counterfeit skincare is not simply weaker — it is made without the safety testing the real formula went through, and reactions to it are common.

- Take a photo of the batch code and packaging
- Ask the seller for a refund, in writing
- Tell people where you bought it

The market only cleans up when refusing to buy becomes normal. Checking takes two minutes, and it is the cheapest thing you will do all week.
TXT;

        $post = Post::create([
            'blog_category_id' => $category?->id,
            'user_id'          => $author?->id,
            'title'            => 'How to spot fake Korean skincare before you pay',
            'slug'             => 'how-to-spot-fake-korean-skincare',
            'excerpt'          => 'Counterfeit K-beauty looks convincing and sells at almost the real price. Five checks that take two minutes and catch nearly all of it.',
            'content'          => $content,
            'status'           => 'published',
            'published_at'     => now(),
            'is_featured'      => true,
            'focus_keyword'    => 'fake Korean skincare',
            'meta_title'       => 'How to spot fake Korean skincare before you pay',
            'meta_description' => 'Fake Korean skincare is most of the market in Bangladesh. Five checks — batch code, seal, packaging, price and importer — that take two minutes.',
            'cover_alt'        => 'A sealed Korean skincare box with its batch code visible',
        ]);

        $post->tags()->sync(Tag::fromList('counterfeits, korean skincare, batch code, buying guide'));
    }

    private function addMenuLinks(): void
    {
        if (MenuItem::where('url', '/blog')->doesntExist()) {
            MenuItem::create([
                'location'   => 'header',
                'label'      => 'Blog',
                'url'        => '/blog',
                'sort_order' => (int) MenuItem::where('location', 'header')->max('sort_order') + 1,
            ]);

            MenuItem::create([
                'location'   => 'footer',
                'column'     => 2,
                'label'      => 'Blog',
                'url'        => '/blog',
                'sort_order' => (int) MenuItem::where('location', 'footer')->where('column', 2)->max('sort_order') + 1,
            ]);
        }
    }
}
