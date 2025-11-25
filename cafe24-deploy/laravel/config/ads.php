<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ads API Mock Mode
    |--------------------------------------------------------------------------
    |
    | Mock 모드가 활성화되면 실제 API 호출 대신 더미 데이터를 반환합니다.
    | 개발 환경에서 API 크레딧을 소비하지 않고 테스트할 수 있습니다.
    |
    */
    'mock' => env('ADS_MOCK', true),

    /*
    |--------------------------------------------------------------------------
    | Naver Ads Configuration
    |--------------------------------------------------------------------------
    |
    | 네이버 검색광고 API 설정
    | https://searchad.naver.com/
    |
    */
    'naver' => [
        'base_url' => env('NAVER_ADS_BASE_URL', 'https://api.naver.com/naver-searchad-api/v2'),
        'access_license' => env('NAVER_ADS_ACCESS_LICENSE'),
        'secret_key' => env('NAVER_ADS_SECRET_KEY'),
        'customer_id' => env('NAVER_ADS_CUSTOMER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Ads Configuration
    |--------------------------------------------------------------------------
    |
    | Google Ads API 설정
    | https://developers.google.com/google-ads/api/
    |
    */
    'google' => [
        'client_id' => env('GOOGLE_ADS_CLIENT_ID'),
        'client_secret' => env('GOOGLE_ADS_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_ADS_REFRESH_TOKEN'),
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'customer_id' => env('GOOGLE_ADS_CUSTOMER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Ads Configuration
    |--------------------------------------------------------------------------
    |
    | Facebook/Instagram Ads API 설정
    | https://developers.facebook.com/docs/marketing-apis/
    |
    */
    'meta' => [
        'access_token' => env('META_ACCESS_TOKEN'),
        'ad_account_id' => env('META_AD_ACCOUNT_ID'),
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
    ],
];
