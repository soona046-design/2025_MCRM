<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Helpers\ChannelCategoryHelper;

class MigrateExistingVisitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 마이그레이션을 위한 전체 방문 데이터 처리
        $totalVisits = DB::table('visits')->count();
        echo "Total visits to migrate: $totalVisits\n";

        $batchSize = 1000;
        $processed = 0;
        $updated = 0;

        DB::table('visits')
            ->whereNull('channel_category')
            ->orderBy('visit_id')
            ->chunk($batchSize, function ($visits) use (&$processed, &$updated) {
                foreach ($visits as $visit) {
                    $channelCategory = ChannelCategoryHelper::getCategoryFromUtmSource($visit->utm_source);

                    DB::table('visits')
                        ->where('visit_id', $visit->visit_id)
                        ->update(['channel_category' => $channelCategory]);

                    $updated++;
                    $processed++;

                    if ($processed % 100 === 0) {
                        echo "Processed: $processed / $updated updated\n";
                    }
                }
            });

        echo "\nMigration completed!\n";
        echo "Total processed: $processed\n";
        echo "Total updated: $updated\n";

        // 요약 통계
        $stats = DB::table('visits')
            ->selectRaw('channel_category, COUNT(*) as count')
            ->whereNotNull('channel_category')
            ->groupBy('channel_category')
            ->get();

        echo "\n--- Category Summary ---\n";
        foreach ($stats as $stat) {
            echo "{$stat->channel_category}: {$stat->count}\n";
        }

        $this->command->info('Migrated channel categories for all visits.');
    }
}

