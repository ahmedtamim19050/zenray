<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Starter blog content: three topics and three general, plain-language
 * articles. Review and edit them in Admin → Blog before relying on them.
 * Run with: php artisan db:seed --class=BlogSeeder
 */
class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Buying guides', 'slug' => 'buying-guides', 'description' => 'Help with comparing and choosing imaging equipment.'],
            ['name' => 'Installation & planning', 'slug' => 'installation-planning', 'description' => 'Preparing rooms and facilities for new equipment.'],
            ['name' => 'Care & maintenance', 'slug' => 'care-maintenance', 'description' => 'Keeping equipment and accessories in good working order.'],
        ])->mapWithKeys(fn ($category, $i) => [
            $category['slug'] => BlogCategory::query()->firstOrCreate(['slug' => $category['slug']], $category + ['sort_order' => $i + 1]),
        ]);

        foreach ($this->posts() as $i => $post) {
            Post::query()->firstOrCreate(['slug' => $post['slug']], [
                'blog_category_id' => $categories[$post['category']]->id,
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'body' => trim($post['body']),
                'photo' => $post['photo'],
                'alt' => $post['alt'],
                'status' => 'published',
                'published_at' => now()->subDays(($i + 1) * 9),
                'is_featured' => $i === 0,
            ]);
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function posts(): array
    {
        return [
            [
                'slug' => 'film-cr-and-dr-explained',
                'category' => 'buying-guides',
                'title' => 'Film, CR and DR: how X-ray images are captured',
                'excerpt' => 'A plain-language look at the three main ways of capturing X-ray images, and the questions to ask when you compare them.',
                'photo' => 'detector-panel',
                'alt' => 'Flat-panel digital X-ray detector',
                'body' => <<<'MD'
Every X-ray exam has the same basic idea: an X-ray tube sends a beam through the body, and something on the other side records what comes through. What that "something" is has changed a lot over the years. Here is a simple overview of the three main options.

## Film

Traditional radiography uses film in a cassette. After the exposure, the film is developed with chemicals, either by hand or in a processor, and then viewed on a light box.

Film is familiar and well understood, but it needs a darkroom or processor, chemicals and physical storage, and an image can't easily be shared or adjusted after it has been developed.

## Computed radiography (CR)

CR also uses cassettes, but instead of film they contain a reusable imaging plate. After the exposure, the cassette goes into a reader that scans the plate and turns the image into a digital file. The plate is then erased and used again.

CR is often chosen as a step from film to digital, because it can work with an existing X-ray room and cassette-based workflow.

## Digital radiography (DR)

DR uses a flat-panel detector that converts the X-rays into a digital image directly, usually within seconds. Detectors can be built into a table or wall stand, or be portable and wireless.

Because there is no cassette to carry to a reader, DR can make the workflow quicker, and images are ready to review almost straight away.

## Questions to ask when comparing options

- **How many exams do you do each day?** Busier departments often benefit most from faster workflows.
- **What equipment do you already have?** Some X-ray rooms can be upgraded rather than replaced.
- **Where will images be viewed and stored?** Think about your viewing stations, PACS or other storage, and how images are shared.
- **What space do you have?** Detectors, readers and workstations all need a place in the room.
- **What support will you need?** Ask about installation, training and ongoing maintenance.

## Not sure where to start?

You don't need to know the technical details before getting in touch. Tell us about your facility and the exams you do, and our team can talk you through the options that fit.
MD,
            ],
            [
                'slug' => 'planning-an-x-ray-room',
                'category' => 'installation-planning',
                'title' => 'Planning an X-ray room: what to think about before installation',
                'excerpt' => 'Space, shielding, power and workflow: the main things to plan before a new X-ray system arrives.',
                'photo' => 'room-overview',
                'alt' => 'Digital X-ray room with a patient table, tube stand and wall stand',
                'body' => <<<'MD'
A new X-ray system is a big step for any facility. Good planning before installation day helps avoid delays and makes sure the room works well for staff and patients. These are the main areas to think about.

## Room size and layout

X-ray systems need space for the equipment itself, for moving the tube and table, and for staff and patients to move around safely, including patients in wheelchairs or on trolleys.

Think about:

- where the patient table, wall stand and tube stand will go
- the route patients take into and out of the room
- where the control area will be, and the view from it into the room
- room for accessories, such as positioning aids and protective aprons

## Radiation protection

Rooms used for X-ray imaging usually need shielding in the walls, doors and sometimes floors and ceilings, and the control area must be protected.

Requirements depend on local regulations and on how the room will be used. A qualified radiation protection expert or physicist normally assesses the room and specifies what is needed, and some regions require approval or registration before the room is used. Check the rules that apply to your facility early in the project.

## Power, data and environment

Ask for the system's installation requirements as early as possible. They typically cover:

- electrical supply and any dedicated circuits
- network connections for sending images to viewing and storage systems
- temperature and humidity in the room
- floor strength and fixing points for ceiling or wall-mounted parts

## Workflow and IT

Think about how images will move once they are taken: to a viewing station, to your image storage or PACS, and to the people who report on them. Involving your IT team early avoids surprises on go-live day.

## Training and handover

Plan time for staff training on the new system, and agree how and when the system will be checked and handed over.

## How we can help

We can help you prepare for installation, from talking through the room and requirements to supporting the installation itself. Get in touch to discuss your project.
MD,
            ],
            [
                'slug' => 'caring-for-lead-aprons',
                'category' => 'care-maintenance',
                'title' => 'Looking after protective lead aprons',
                'excerpt' => 'Simple everyday habits that help protective aprons stay in good condition, and why regular checks matter.',
                'photo' => 'apron',
                'alt' => 'Protective lead apron with a thyroid collar hanging on a wall hook',
                'body' => <<<'MD'
Protective aprons and thyroid collars are worn every day in many imaging departments. They are designed to help reduce staff exposure to scattered radiation, but they only work well when the protective layer inside is intact. A few simple habits help them last.

## Hang them, don't fold them

Folding or creasing an apron can crack the protective material inside, even if the outside looks fine. Always hang aprons on a suitable hanger or rack when they are not being worn, and avoid throwing them over chairs or onto the floor.

## Keep them clean

Clean aprons following the manufacturer's instructions. Harsh chemicals or soaking can damage the cover and the protective layer, so use only the cleaning products and methods that are recommended.

## Check them regularly

Damage inside an apron can't always be seen. Many facilities check aprons:

- **visually and by touch**, looking for tears, worn areas and lumps or gaps in the protective layer
- **with imaging**, such as fluoroscopy or radiography, to look for cracks and holes

How often checks are needed, and what counts as a failure, depends on local rules and guidance. Keep a simple record for each apron, so you know when it was last checked.

## Choose the right fit

An apron that fits well is more comfortable and more likely to be worn properly. Consider the type of work, how long staff wear aprons, and whether a two-piece design or thyroid collar is needed.

## Need replacements or advice?

If you need new protective equipment, or advice on choosing suitable aprons for your team, our team can help.
MD,
            ],
        ];
    }
}
