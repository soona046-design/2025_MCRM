<?php

namespace App\Console\Commands;

use App\Services\Ads\NaverAdsApiService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CollectAdCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:collect-costs
                            {--from= : 시작 날짜 (YYYY-MM-DD), 기본값: 오늘로부터 --days일 전}
                            {--to= : 종료 날짜 (YYYY-MM-DD), 기본값: 오늘}
                            {--days=7 : --from 미지정 시 조회할 기간(일). 사후 보정 반영을 위한 롤링 윈도우}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '네이버 광고비를 가져와 cost_imports 테이블에 영구 저장 (채널피벗/퍼널 대시보드가 읽는 실제 소스).
                                대시보드를 직접 열지 않아도 매일 자동으로 누적되도록 스케줄 등록됨 (Kernel.php).';

    public function __construct(protected NaverAdsApiService $naverAdsApiService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $to = $this->option('to') ?? now()->format('Y-m-d');
        $from = $this->option('from') ?? Carbon::parse($to)->subDays((int) $this->option('days'))->format('Y-m-d');

        $this->info("📊 네이버 광고비 수집 시작: {$from} ~ {$to}");

        try {
            $costs = $this->naverAdsApiService->syncCostImports($from, $to);

            $totalCost = array_sum(array_column($costs, 'cost'));
            $this->info("✅ {$from} ~ {$to} 기간 cost_imports 갱신 완료 — " . count($costs) . "건, 총 비용 ₩" . number_format($totalCost));

            Log::info('CollectAdCosts 완료', ['from' => $from, 'to' => $to, 'count' => count($costs), 'total_cost' => $totalCost]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ 오류 발생: ' . $e->getMessage());
            Log::error('CollectAdCosts 실행 오류', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }
    }
}
