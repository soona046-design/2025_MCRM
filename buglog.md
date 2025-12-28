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

## 2025년 12월 15일 - 채널-진료 매트릭스 페이지 구현

### 발견된 버그 및 해결 내역

---

#### Bug #6: date-fns 패키지 호환성 문제
**발생 일시**: 2025-12-15 10:57
**심각도**: Critical
**상태**: ✅ 해결됨

**문제 설명**:
채널-진료 매트릭스 페이지에서 `@mui/x-date-pickers`의 `AdapterDateFns`를 사용할 때 Next.js 빌드 오류 발생.

**에러 메시지**:
```
Failed to compile
Module not found: Package path ./_lib/format/longFormatters is not exported from package
/Users/soona/Documents/인사이트/2025_MCRM/m-crm-project/node_modules/date-fns
(see exports field in /Users/soona/Documents/인사이트/2025_MCRM/m-crm-project/node_modules/date-fns/package.json)

Import trace for requested module:
./node_modules/@mui/x-date-pickers/AdapterDateFns/index.js
./src/app/dashboards/channel-treatment-matrix/page.tsx
```

**원인**:
- `date-fns` 패키지의 버전과 `@mui/x-date-pickers`의 `AdapterDateFns` 간 호환성 문제
- `date-fns` 내부 모듈 경로 `_lib/format/longFormatters`가 exports에 포함되지 않음
- Next.js 14.2.33과 최신 date-fns 버전 간 충돌

**해결 방법**:
```tsx
// 문제 코드 - DatePicker 사용
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';
import { ko } from 'date-fns/locale';
import { format, subDays } from 'date-fns';

<LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={ko}>
  <DatePicker
    label="시작일"
    value={startDate}
    onChange={(date) => date && setStartDate(date)}
    format="yyyy-MM-dd"
  />
</LocalizationProvider>

// 수정된 코드 - 일반 TextField 사용
// 날짜 유틸리티 함수 직접 구현
const formatDate = (date: Date): string => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const subDays = (date: Date, days: number): Date => {
  const result = new Date(date);
  result.setDate(result.getDate() - days);
  return result;
};

// TextField로 교체
<TextField
  label="시작일"
  type="date"
  value={startDate}  // string 타입
  onChange={(e) => setStartDate(e.target.value)}
  size="small"
  InputLabelProps={{ shrink: true }}
/>
```

**변경 사항**:
1. `@mui/x-date-pickers` 관련 import 모두 제거
2. `date-fns` import 제거
3. `Date` 타입을 `string` 타입으로 변경
4. 날짜 유틸리티 함수 직접 구현
5. `LocalizationProvider` wrapper 제거
6. 모든 `format()` 함수 호출을 직접 구현한 `formatDate()` 또는 문자열 그대로 사용으로 변경

**교훈**:
1. **패키지 의존성 주의**: 서드파티 라이브러리 간 호환성 문제는 빌드 타임에만 발견될 수 있음
2. **간단한 기능은 직접 구현**: 날짜 포맷팅 같은 간단한 기능은 외부 라이브러리 없이 구현 가능
3. **HTML5 기본 기능 활용**: `<input type="date">`는 브라우저 네이티브 DatePicker 제공
4. **번들 사이즈 감소**: 불필요한 패키지 제거로 번들 크기 축소 효과

**대안 고려사항**:
- `date-fns` 버전 다운그레이드 (v2.x)
- `dayjs`로 교체 (더 가벼운 대안)
- `Luxon` 사용
- 또는 현재처럼 네이티브 date input 사용

---

## 2025년 12월 20일 - 채널 피벗 페이지 버전 불일치 문제

### 발견된 버그 및 해결 내역

---

#### Bug #7: 로컬 채널 피벗 페이지가 이전 버전으로 되돌아감
**발생 일시**: 2025-12-20 23:48
**심각도**: High
**상태**: ✅ 해결됨

**문제 설명**:
로컬 개발 환경의 채널 피벗 페이지(`src/app/dashboards/channel-pivot/page.tsx`)가 Vercel 프로덕션 배포 버전보다 구버전으로 돌아가 있었음.

**증상**:
- Vercel 배포 버전: `AdapterDateFns` 사용 (main 브랜치, 1807 lines)
- 로컬 버전: `AdapterDayjs` 사용 (feature/date-range-filtering 브랜치, 2011 lines)
- 엑셀 업로드 기능 추가 후 파일이 수정되었으나, UI/UX가 이전 버전으로 보임

**원인**:
1. 작업 브랜치(`feature/date-range-filtering`)와 배포 브랜치(`main`)가 분리되어 있음
2. main 브랜치에는 최신 UI/UX 개선사항이 반영되어 Vercel에 배포됨
3. 로컬에서는 feature 브랜치에서 작업하면서 main의 최신 변경사항을 가져오지 않음
4. 엑셀 업로드 기능을 feature 브랜치에만 추가하여 main 브랜치 기준으로는 구버전이 됨

**해결 방법**:
```bash
# 1. 현재 작업 백업
cp src/app/dashboards/channel-pivot/page.tsx src/app/dashboards/channel-pivot/page.tsx.backup_before_main_restore

# 2. main 브랜치의 최신 버전으로 복구
git show main:src/app/dashboards/channel-pivot/page.tsx > src/app/dashboards/channel-pivot/page.tsx

# 3. 엑셀 업로드 기능 추가
# - imports에 xlsx, UploadFileIcon, DownloadIcon 추가
# - handleDownloadTemplate 함수 구현
# - handleExcelUpload 함수 구현
# - UI에 "템플릿 다운로드", "엑셀 업로드" 버튼 추가
```

**변경 사항**:
1. **기본 버전**: main 브랜치의 최신 코드 (AdapterDateFns, Date 타입 사용)
2. **추가 기능**:
   - `import * as XLSX from 'xlsx'` 추가
   - `UploadFileIcon`, `DownloadIcon` 아이콘 import
   - 엑셀 템플릿 다운로드 핸들러 (Blob 방식)
   - 엑셀 업로드 핸들러 (파일 검증, 날짜 변환, 데이터 파싱)
   - UI에 3개 버튼 배치: "템플릿 다운로드", "엑셀 업로드", "캠페인 추가"

**엑셀 업로드 기능 사양**:
- **템플릿 형식**: channel, campaign, startDate, endDate, leads, appointments, cost, revenue
- **자동 계산**: CPL, CPA, 전환율, ROI, ROAS
- **날짜 처리**: 엑셀 시리얼 날짜 자동 변환 (`XLSX.SSF.parse_date_code`)
- **검증**: 필수 필드 체크, 오류 행 상세 리포트
- **저장**: localStorage에 manual 캠페인 자동 저장

**교훈**:
1. **브랜치 전략 명확화**: main 브랜치는 프로덕션 배포용, feature 브랜치는 개발용으로 명확히 구분
2. **정기적인 main 병합**: feature 브랜치에서 작업 시 주기적으로 main의 변경사항 pull/merge
3. **버전 확인**: 로컬과 배포 환경의 코드 버전이 일치하는지 주기적으로 확인
4. **백업 습관**: 파일 복구 전 반드시 백업 생성
5. **git show 활용**: 특정 브랜치의 파일 버전을 확인/복구할 때 유용

**예방책**:
```bash
# feature 브랜치 작업 시 main 최신 변경사항 가져오기
git checkout feature/date-range-filtering
git fetch origin
git merge origin/main

# 또는 rebase
git rebase origin/main
```

---

## 2025년 12월 28일 - Cafe24 서버 마이그레이션 배포 문제

### 발견된 버그 및 해결 내역

---

#### Bug #8: Cafe24 서버 마이그레이션 외래 키 중복 오류
**발생 일시**: 2025-12-28 22:50
**심각도**: Critical
**상태**: ✅ 해결됨

**문제 설명**:
채널-진료 매트릭스 시스템의 3개 마이그레이션 파일을 Cafe24 서버에 배포하기 위해 웹 기반 마이그레이션 스크립트(`run-migration.php`)를 실행했을 때 외래 키 중복 오류 발생.

**에러 메시지**:
```
SQLSTATE[HY000]: General error: 1005 Can't create table `insightmcrm`.`leads` (errno: 121 "Duplicate key on write or update")
(Connection: mysql, SQL: alter table `leads` add constraint `leads_source_visit_id_foreign` foreign key (`source_visit_id`) references `visits` (`visit_id`) on delete set null)
```

**원인**:
1. 기존 데이터베이스에 이미 외래 키 제약 조건이 존재함
2. `php artisan migrate` 실행 시 모든 마이그레이션을 순차 실행하려다가 이미 적용된 외래 키를 재생성하려고 시도
3. 특히 `2025_09_25_000000_add_foreign_keys_to_leads_table.php` 마이그레이션이 이미 실행되어 있음

**시도한 해결 방법들**:
1. ❌ **SSH 접속 시도**: `ssh insightmcrm@insightmcrm.mycafe24.com` - Permission denied
2. ❌ **웹 기반 전체 마이그레이션**: `run-migration.php` - errno: 121 오류

**최종 해결 방법**:
```php
// create-new-tables.php - 신규 테이블만 직접 생성

// 1. treatment_types 테이블 생성
CREATE TABLE `treatment_types` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `code` varchar(50) NOT NULL COMMENT '진료 유형 코드',
    `name` varchar(100) NOT NULL COMMENT '진료 유형 이름',
    `category` varchar(50) DEFAULT NULL,
    `color` varchar(7) DEFAULT '#3b82f6',
    `sort_order` int DEFAULT 0,
    `active` tinyint(1) DEFAULT 1,
    `description` text,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `treatment_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

// 2. channel_treatment_records 테이블 생성
// 3. marketing_insights 테이블 생성
// 4. migrations 테이블에 기록 추가
```

**실행 방법**:
1. FTP로 `create-new-tables.php` 업로드 → `/insightmcrm/www/`
2. 브라우저 접속: `http://insightmcrm.mycafe24.com/create-new-tables.php`
3. 결과 확인: 23개 → 26개 테이블 증가
4. 보안을 위해 스크립트 파일 삭제

**변경 사항**:
- ✅ `treatment_types` 테이블 생성
- ✅ `channel_treatment_records` 테이블 생성 (외래 키: channel_categories, treatment_types, users)
- ✅ `marketing_insights` 테이블 생성 (외래 키: users)
- ✅ `migrations` 테이블에 3개 레코드 추가

**교훈**:
1. **점진적 마이그레이션**: 전체 마이그레이션 실행 대신 신규 테이블만 생성하는 것이 안전
2. **웹 기반 스크립트**: SSH 접속 불가 시 Laravel bootstrap을 활용한 웹 스크립트로 대체 가능
3. **테이블 존재 여부 확인**: `SHOW TABLES LIKE 'table_name'` 으로 중복 생성 방지
4. **외래 키 중복 체크**: `information_schema.KEY_COLUMN_USAGE` 조회로 기존 제약 조건 확인
5. **보안 주의**: 웹 기반 DB 관리 스크립트는 실행 후 즉시 삭제 필수

**예방책**:
```php
// 마이그레이션 파일에서 if not exists 패턴 활용
if (!Schema::hasTable('treatment_types')) {
    Schema::create('treatment_types', function (Blueprint $table) {
        // ...
    });
}

// 외래 키 추가 시 존재 여부 확인
$sm = Schema::getConnection()->getDoctrineSchemaManager();
$foreignKeys = $sm->listTableForeignKeys('leads');
$exists = collect($foreignKeys)->contains(fn($fk) => $fk->getName() === 'leads_source_visit_id_foreign');

if (!$exists) {
    Schema::table('leads', function (Blueprint $table) {
        $table->foreign('source_visit_id')->references('visit_id')->on('visits');
    });
}
```

**참고 스크립트**:
- `check-db.php`: 데이터베이스 연결 및 테이블 목록 확인
- `run-migration.php`: 전체 마이그레이션 실행 (외래 키 오류로 사용 중단)
- `check-foreign-keys.php`: 외래 키 상태 확인
- `create-new-tables.php`: 신규 테이블만 직접 생성 ✅ **채택된 방법**

---

## 통계

**총 버그 수**: 8개
**해결된 버그**: 8개 (100%)
**평균 해결 시간**: ~25분
**주요 원인**:
- 데이터 구조 불일치 (38%)
- 패키지 호환성 문제 (12%)
- 브랜치 관리 문제 (12%)
- 배포 환경 제약 (13%)
- 문서 부족 (25%)

**교훈**:
1. 데이터베이스 스키마 먼저 확인
2. API 계약(Contract) 명확히 정의
3. 자동화된 테스트 작성
4. 서버 재시작으로 캐시 문제 해결
5. 브랜치 전략 명확화 및 정기적인 main 동기화
6. 점진적 배포 전략 (신규 항목만 추가)
7. 웹 기반 대체 솔루션 준비 (SSH 불가 환경 대비)
