<?php

namespace App\Console\Commands;

use App\Models\Seller;
use Illuminate\Console\Command;

class RecalculateSellerRatings extends Command
{
    protected $signature = 'sellers:recalculate-ratings {--stats : Also recalculate sales and product counts}';

    protected $description = 'Recalculate seller ratings from approved product reviews';

    public function handle(): int
    {
        $sellers = Seller::all();
        $bar = $this->output->createProgressBar($sellers->count());

        $this->info("Recalculating ratings for {$sellers->count()} sellers...");
        $bar->start();

        foreach ($sellers as $seller) {
            if ($this->option('stats')) {
                $seller->recalculateStats();
            } else {
                $seller->recalculateRating();
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done! All seller ratings updated.');

        return Command::SUCCESS;
    }
}
