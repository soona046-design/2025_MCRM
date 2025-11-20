# M-CRM 버그 로그

## 2025년 11월 20일 - 채널 피벗 대시보드 카테고리화 작업

### 발견된 버그 및 해결 내역

---

#### Bug #1: Laravel `groupBy()` 중첩 구조 문제
**발생 일시**: 2025-11-20 14:00
**심각도**: Critical
**상태**: ✅ 해결됨

**문제 설명**:
`ChannelPivotController.php`에서 `groupBy(['utm_source', 'utm_campaign'])`를 사용할 때, Laravel이 중첩된 Collection을 반환하는데, 이를 `map()` 함수에서 단일 배열로 처리하려 해서 에러 발생.

**에러 메시지**:
```
Exception: Property [status] does not exist on this collection instance.
```

**원인**:
```php
// 문제 코드
$pivotTableData = $leads->groupBy(['utm_source', 'utm_campaign'])
    ->map(function ($leadsByChannelCampaign, $keys) {
        list($channel, $campaign) = $keys; // $keys는 배열이 아님!
        // ...
    });
```

`groupBy(['utm_source', 'utm_campaign'])`는 다음과 같은 중첩 구조를 생성:
```php
[
  'utm_source_1' => [
    'campaign_1' => Collection,
    'campaign_2' => Collection
  ]
]
```

**해결 방법**:
```php
// 수정된 코드
$pivotTableData = collect();

$leads->groupBy('utm_source')->each(function ($leadsByChannel, $channel) use (&$pivotTableData, ...) {
    $leadsByChannel->groupBy('utm_campaign')->each(function ($leadsByChannelCampaign, $campaign) use (&$pivotTableData, $channel, ...) {
        // ... 데이터 처리
        $pivotTableData->push([...]);
    });
});
```

**교훈**: Laravel의 `groupBy()` 메서드에 배열을 전달하면 중첩된 Collection을 반환하므로, 명시적으로 각 레벨을 순회해야 함.

---

#### Bug #2: Lead Status Enum 값 불일치
**발생 일시**: 2025-11-20 14:30
**심각도**: High
**상태**: ✅ 해결됨

**문제 설명**:
컨트롤러 코드에서 한글 status 값(`'상담완료'`, `'미팅완료'`, `'계약완료'`)을 사용했으나, 데이터베이스의 `leads.status` 컬럼은 영문 enum 값(`'new'`, `'contacted'`, `'pending'`, `'converted'`, `'rejected'`)을 사용.

**에러 메시지**:
API가 빈 배열을 반환하며, 모든 leads의 tickets/appointments/contracts 카운트가 0으로 나옴.

**원인**:
```php
// 문제 코드
$totalTickets = $leadsByChannel->filter(function($lead) {
    return in_array($lead->status, ['상담완료', '미팅완료', '계약완료']); // 절대 매치 안 됨!
})->count();
```

**해결 방법**:
```php
// 수정된 코드
$totalTickets = $leadsByChannel->filter(function($lead) {
    return in_array($lead->status, ['contacted', 'pending', 'converted']);
})->count();
```

**교훈**: 데이터베이스 스키마와 애플리케이션 코드의 enum 값이 일치하는지 항상 확인 필요. 마이그레이션 파일을 먼저 확인하는 습관 중요.

---

#### Bug #3: Visits 테이블 컬럼명 오류
**발생 일시**: 2025-11-20 15:00
**심각도**: Critical
**상태**: ✅ 해결됨

**문제 설명**:
`visits` 테이블에 `channel_category_id` 컬럼이 없는데 join 시도.

**에러 메시지**:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'visits.channel_category_id' in 'ON'
```

**원인**:
```php
// 문제 코드
$leadsQuery = Lead::query()
    ->join('visits', 'leads.source_visit_id', '=', 'visits.visit_id')
    ->leftJoin('channel_categories as cc', 'visits.channel_category_id', '=', 'cc.id')
    // channel_category_id 컬럼이 존재하지 않음!
```

실제 테이블 구조:
- `visits.channel_category` (enum: 'online', 'offline', 'db')
- `channel_categories.code` ('online', 'offline', 'db')

**해결 방법**:
```php
// 수정된 코드
$leadsQuery = Lead::query()
    ->join('visits', 'leads.source_visit_id', '=', 'visits.visit_id')
    ->leftJoin('channel_categories as cc', 'visits.channel_category', '=', 'cc.code')
    ->select(
        'leads.lead_id',
        'leads.status',
        'visits.utm_source',
        'visits.utm_campaign',
        'visits.channel_category as category_code',  // enum 값 직접 사용
        'cc.name as category_name',
        'cc.color as category_color'
    );
```

**교훈**:
1. 데이터베이스 스키마를 먼저 확인 (`php artisan tinker` + `Model::first()`)
2. 마이그레이션 파일에서 정확한 컬럼 이름과 타입 확인
3. Foreign key가 아닌 enum 값으로 join하는 경우도 있음

---

#### Bug #4: ROAS 필드 누락
**발생 일시**: 2025-11-20 15:30
**심각도**: Medium
**상태**: ✅ 해결됨

**문제 설명**:
프론트엔드에서 `row.roas.toFixed(2)`를 호출했으나, 백엔드 API의 `channelPerformance` 데이터에 `roas` 필드가 없음.

**에러 메시지**:
```
TypeError: Cannot read properties of undefined (reading 'toFixed')
Source: page.tsx (1192:68) @ toFixed
```

**원인**:
```php
// channelPerformanceData 반환 객체에 roas 누락
return [
    'channel' => $channel,
    'category_code' => $categoryCode,
    // ... 기타 필드
    'roi' => round($roi),
    // 'roas' => ... 없음!
];
```

**해결 방법**:
```php
// ROAS 계산 추가
$roas = $totalCost > 0 ? ($totalRevenue / $totalCost) * 100 : 0;

return [
    'channel' => $channel,
    'category_code' => $categoryCode,
    // ... 기타 필드
    'roi' => round($roi),
    'roas' => round($roas, 2),  // 추가
];
```

**교훈**:
1. 프론트엔드와 백엔드의 데이터 구조 일치 확인
2. TypeScript 인터페이스와 실제 API 응답 비교
3. API 응답을 먼저 curl/Postman으로 확인

---

#### Bug #5: Laravel 서버 캐싱 문제
**발생 일시**: 2025-11-20 (전체 작업 기간 동안)
**심각도**: Low (개발 환경)
**상태**: ✅ 해결됨

**문제 설명**:
컨트롤러 코드를 수정했음에도 불구하고 이전 코드가 계속 실행됨. Laravel의 `php artisan serve`로 실행 중인 서버가 코드 변경을 즉시 반영하지 않음.

**원인**:
- PHP OPcache가 활성화되어 있음
- 오래된 Laravel 서버 프로세스 (11월 6일부터 실행 중)

**해결 방법**:
```bash
# 서버 재시작
cd mcrm-backend
pkill -f "php artisan serve"
php artisan serve

# 또는 캐시 클리어
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

**교훈**:
1. 코드 변경 후에도 동일한 에러 발생 시 서버 재시작 고려
2. 프로덕션과 달리 개발 환경에서는 hot reload가 항상 작동하지 않음
3. `ps aux | grep "php artisan serve"`로 오래된 프로세스 확인

---

## 개선 사항 제안

### 1. 테스트 커버리지 확대
현재 발견된 버그들은 대부분 통합 테스트로 사전에 발견 가능:
```php
// 추천: ChannelPivotControllerTest.php
public function test_channel_performance_includes_all_required_fields()
{
    $response = $this->get('/api/dashboards/channel-pivot');

    $response->assertJsonStructure([
        'channelPerformance' => [
            '*' => [
                'channel',
                'category_code',
                'category_name',
                'category_color',
                'roi',
                'roas',  // 필수 필드 검증
            ]
        ]
    ]);
}
```

### 2. TypeScript 타입 검증 강화
```typescript
// 추천: API 응답 타입 자동 검증
import { z } from 'zod';

const ChannelPerformanceSchema = z.object({
  channel: z.string(),
  category_code: z.string().nullable(),
  category_name: z.string(),
  category_color: z.string(),
  // ... 모든 필드 정의
  roas: z.number(),
});

// API 호출 시 자동 검증
const data = ChannelPerformanceSchema.parse(response.data);
```

### 3. 데이터베이스 스키마 문서화
- 각 테이블의 컬럼 정의를 README 또는 Wiki에 문서화
- Enum 값 목록 명시
- Foreign key 관계도 작성

### 4. 개발 환경 개선
```bash
# composer.json에 개발 스크립트 추가
"scripts": {
    "dev": "php artisan serve & php artisan queue:work --tries=1 & php artisan pail",
    "test": "php artisan test",
    "fresh": "php artisan migrate:fresh --seed"
}
```

---

## 통계

**총 버그 수**: 5개
**해결된 버그**: 5개 (100%)
**평균 해결 시간**: ~30분
**주요 원인**: 데이터 구조 불일치 (60%), 문서 부족 (40%)

**교훈**:
1. 데이터베이스 스키마 먼저 확인
2. API 계약(Contract) 명확히 정의
3. 자동화된 테스트 작성
4. 서버 재시작으로 캐시 문제 해결
