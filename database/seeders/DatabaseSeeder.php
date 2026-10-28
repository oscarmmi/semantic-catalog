<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the Northwind Gadgets demo catalog.
     */
    public function run(): void
    {
        $items = [
            ['NW-CB-001', 'SoftLoop USB-C Cable', 'Cables', 'A flexible braided USB-C cable that coils neatly and resists tangles in a work bag.', ['usb-c', 'braided', 'tangle-free', 'travel', 'gift', 'under 40'], 18.00],
            ['NW-CB-002', 'StudioLink Audio Cable', 'Cables', 'A shielded low-noise cable for connecting microphones and monitors in a recording studio.', ['audio', 'studio', 'shielded', 'quiet signal', 'gift', 'under 40'], 29.00],
            ['NW-CB-003', 'SnapCharge Cable Kit', 'Cables', 'Three short charging leads with magnetic clips that keep a desk organized and cables untangled.', ['charging', 'organizer', 'magnetic', 'gift', 'under 40'], 24.00],
            ['NW-CB-004', 'LongReach HDMI Cable', 'Cables', 'A durable long-reach HDMI cable for a clean living room or meeting-room setup.', ['hdmi', 'display', 'long', 'durable', 'gift', 'under 40'], 16.00],
            ['NW-CB-005', 'FlexWeave Lightning Cable', 'Cables', 'A soft, reinforced charging cable with a flexible woven jacket that bends without knotting.', ['woven', 'flexible', 'charging', 'tangle-free', 'gift', 'under 40'], 21.00],
            ['NW-CB-006', 'DeskDock Cable Set', 'Cables', 'A set of color-coded desk cables and reusable ties for a tidy home office setup.', ['desk', 'organizer', 'color coded', 'reusable ties', 'gift', 'under 40'], 34.00],

            ['NW-FN-001', 'HushDesk Personal Fan', 'Fans', 'A whisper-quiet desktop fan with a low vibration motor for focused work and studio recording.', ['quiet', 'whisper quiet', 'desk', 'studio', 'low noise', 'gift', 'under 40'], 36.00],
            ['NW-FN-002', 'BreezeArc Tower Fan', 'Fans', 'A slim oscillating tower fan with gentle airflow for a small bedroom or shared office.', ['tower', 'oscillating', 'bedroom', 'quiet', 'gift'], 89.00],
            ['NW-FN-003', 'PocketAir Travel Fan', 'Fans', 'A lightweight rechargeable fan that slips into a backpack for cool air on the go.', ['portable', 'travel', 'rechargeable', 'lightweight', 'gift', 'under 40'], 27.00],
            ['NW-FN-004', 'AirDial Clip Fan', 'Fans', 'A compact adjustable clip-on fan for a shelf, bunk, or crowded desk with steady airflow.', ['clip', 'compact', 'adjustable', 'desk', 'gift', 'under 40'], 22.00],
            ['NW-FN-005', 'CalmFlow Floor Fan', 'Fans', 'A quiet floor fan with multiple speeds and a broad base for cooling a home workspace.', ['floor', 'quiet', 'low noise', 'workspace', 'multiple speeds', 'gift'], 74.00],
            ['NW-FN-006', 'MiniMist USB Fan', 'Fans', 'A small USB desk fan with a removable water tank for a refreshing personal cooling zone.', ['usb', 'desk', 'portable', 'mist', 'gift', 'under 40'], 32.00],

            ['NW-LM-001', 'HaloTask Desk Lamp', 'Lamps', 'An adjustable LED task lamp with warm and cool settings for late-night reading or focused desk work.', ['led', 'adjustable', 'reading', 'desk', 'gift'], 48.00],
            ['NW-LM-002', 'GlowPebble Night Light', 'Lamps', 'A soft-touch bedside light that gives a gentle warm glow without disturbing a sleeping room.', ['bedside', 'warm light', 'soft', 'quiet', 'gift', 'under 40'], 26.00],
            ['NW-LM-003', 'FoldLite Book Lamp', 'Lamps', 'A rechargeable clip-on reading light with flexible positioning for books and travel.', ['reading', 'clip', 'rechargeable', 'travel', 'gift', 'under 40'], 19.00],
            ['NW-LM-004', 'SpectrumBar Monitor Light', 'Lamps', 'A glare-reducing monitor light bar that illuminates a workspace without reflections on screen.', ['monitor', 'workspace', 'glare free', 'adjustable', 'gift'], 59.00],
            ['NW-LM-005', 'DawnSense Wake Lamp', 'Lamps', 'A gradual sunrise alarm lamp with a calm dawn glow to make early mornings feel gentler.', ['sunrise', 'alarm', 'bedroom', 'warm light', 'gift'], 68.00],
            ['NW-LM-006', 'PocketBeam Lantern', 'Lamps', 'A compact rechargeable lantern for a campsite, power outage, or evening walk.', ['portable', 'rechargeable', 'camping', 'emergency', 'gift', 'under 40'], 31.00],

            ['NW-BG-001', 'TidyTech Cable Pouch', 'Bags', 'A padded organizer pouch with elastic loops that keeps charging cables and adapters untangled.', ['cable organizer', 'tangle-free', 'travel', 'pouch', 'gift', 'under 40'], 25.00],
            ['NW-BG-002', 'DayPath Laptop Backpack', 'Bags', 'A weather-resistant commuter backpack with a padded laptop sleeve and organized pockets.', ['commuter', 'laptop', 'weather resistant', 'organized', 'gift'], 79.00],
            ['NW-BG-003', 'PocketGrid Tech Organizer', 'Bags', 'A compact zip case with mesh pockets for small devices, adapters, and everyday tech essentials.', ['organizer', 'compact', 'travel', 'tech', 'gift', 'under 40'], 29.00],
            ['NW-BG-004', 'SoftCarry Tablet Sleeve', 'Bags', 'A lightweight padded sleeve that protects a tablet while sliding easily into a tote or backpack.', ['tablet', 'lightweight', 'padded', 'protective', 'gift', 'under 40'], 33.00],
            ['NW-BG-005', 'WeekendFlex Duffel', 'Bags', 'A roomy carry-on duffel with a separate shoe pocket for a quick weekend trip.', ['weekend', 'carry-on', 'travel', 'roomy', 'gift'], 72.00],
            ['NW-BG-006', 'LoopSide Everyday Tote', 'Bags', 'A durable everyday tote with a padded device pocket and an easy-access organizer section.', ['everyday', 'tote', 'device pocket', 'organized', 'gift'], 42.00],
        ];

        foreach ($items as [$sku, $name, $category, $description, $tags, $price]) {
            CatalogItem::updateOrCreate(
                ['sku' => $sku],
                compact('sku', 'name', 'category', 'description', 'tags', 'price'),
            );
        }
    }
}
