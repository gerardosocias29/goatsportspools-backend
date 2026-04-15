<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        // NBA Playoff "How this Works" content
        $howThisWorks = <<<'HTML'
<p>Each bracket entry is $20.</p>
<p>Maximum number of brackets per individual is 8.</p>
<p>If there are 25 brackets entered, $500 will be paid to winners.</p>
<ul>
  <li>For 10 brackets entered, 1 winner.</li>
  <li>For 20 brackets entered, 2 winners.</li>
  <li>For 30 brackets entered, 3 winners.</li>
</ul>
<p>For every 10 entries, 1 winner will be added.</p>
<p>For 100 brackets entered, 10 winners.</p>
<h3>How to play:</h3>
<ol>
  <li>Open a bracket.</li>
  <li>Pick all winners for every NBA playoff round. Pick all winning teams up to the Conference Championship and NBA Finals.</li>
</ol>
<h3>Bonus point system</h3>
<p>You also have to select the number of games per playoff series. Example: if you pick Denver to beat the Wolves, you have to select the number of games in the series. 6 games, predicting the Nuggets will win 4-2.</p>
<p>Select the winning team and the number of games.</p>
<p>Points will be awarded for every correct team selected and number of games.</p>
<p>Seed Bonus points will also be awarded for every correct lower seeded team selection. Example: Number 5 Rockets over the Number 4 seed Lakers. Five minus four — 1 bonus point.</p>
<p>If you pick say the Blazers over OKC and OKC loses, you are awarded 7 bonus points.</p>
HTML;

        // Shared FAQ content
        $faqs = <<<'HTML'
<h3>Give me the short story.</h3>
<p>Sure, pick your winners, pick the number of games. You will be awarded points for every correct pick.</p>
<h3>Will you pay every dollar put in the pool?</h3>
<p>Yes, all monies will be paid out.</p>
<h3>Can I put in more than one entry?</h3>
<p>Yes. The maximum is 8. $20 per entry.</p>
<h3>How to pay for my entry?</h3>
<p>Zelle. Venmo. PayPal.</p>
<h3>When does the bracket selection close?</h3>
<p>Selection of winners and number of games close end of day Sunday, 19th. No more edits allowed after Sunday.</p>
<h3>Why is my bracket entry not showing in the standings page?</h3>
<p>You can enter up to 8 brackets. Only paid finalized brackets show in the page. Make sure each of your brackets are paid. Make sure your bracket is finalized. All entries will be shown in the standings page. All entry selections will be displayed after 20th of April.</p>
<h3>What is the payout structure?</h3>
<p>Display payout structure.</p>
<h3>When do I get paid?</h3>
<p>Payouts will be in late June after the 2026 NBA champion is announced.</p>
<h3>How to contact the pool admin?</h3>
<p>sports@okrng.com<br>7242-SPORTS<br>(724) 277-6787</p>
HTML;

        SiteContent::updateOrCreate(
            ['key' => 'playoff_how_this_works'],
            ['title' => 'Playoff Pool — How this Works', 'body' => $howThisWorks]
        );

        SiteContent::updateOrCreate(
            ['key' => 'playoff_faqs'],
            ['title' => 'Playoff Pool — FAQs', 'body' => $faqs]
        );
    }
}
