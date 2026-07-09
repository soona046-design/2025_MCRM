import * as React from 'react';
import {
  NavigationMenu,
  NavigationMenuList,
  NavigationMenuItem,
  NavigationMenuTrigger,
  NavigationMenuContent,
  NavigationMenuLink,
} from 'insight-ui';

export const AnalyticsNavigationMenu = () => (
  <div style={{ display: 'flex', justifyContent: 'center', paddingBottom: 220 }}>
    <NavigationMenu viewport={false}>
      <NavigationMenuList>
        <NavigationMenuItem>
          <NavigationMenuTrigger>분석 대시보드</NavigationMenuTrigger>
          <NavigationMenuContent
            forceMount
            style={{ position: 'absolute', top: 44, left: 0 }}
          >
            <ul style={{ display: 'grid', gap: 4, width: 320, padding: 4 }}>
              <li>
                <NavigationMenuLink href="#">
                  <div style={{ fontWeight: 600, fontSize: 13.5 }}>전환 퍼널</div>
                  <div style={{ fontSize: 12, color: 'var(--muted-foreground)' }}>
                    방문 → 리드 → 상담 → 예약 → 결제 단계별 전환율
                  </div>
                </NavigationMenuLink>
              </li>
              <li>
                <NavigationMenuLink href="#">
                  <div style={{ fontWeight: 600, fontSize: 13.5 }}>채널 피벗</div>
                  <div style={{ fontSize: 12, color: 'var(--muted-foreground)' }}>
                    네이버 · 인스타그램 채널별 광고비 대비 성과
                  </div>
                </NavigationMenuLink>
              </li>
              <li>
                <NavigationMenuLink href="#">
                  <div style={{ fontWeight: 600, fontSize: 13.5 }}>상담원 성과</div>
                  <div style={{ fontSize: 12, color: 'var(--muted-foreground)' }}>
                    지점별 상담매니저 응답 SLA 및 계약 전환율
                  </div>
                </NavigationMenuLink>
              </li>
            </ul>
          </NavigationMenuContent>
        </NavigationMenuItem>
        <NavigationMenuItem>
          <NavigationMenuLink href="#" style={{ display: 'inline-flex', height: 36, alignItems: 'center', padding: '0 16px', borderRadius: 6, fontSize: 14, fontWeight: 500 }}>
            리드 관리
          </NavigationMenuLink>
        </NavigationMenuItem>
        <NavigationMenuItem>
          <NavigationMenuLink href="#" style={{ display: 'inline-flex', height: 36, alignItems: 'center', padding: '0 16px', borderRadius: 6, fontSize: 14, fontWeight: 500 }}>
            예약 관리
          </NavigationMenuLink>
        </NavigationMenuItem>
      </NavigationMenuList>
    </NavigationMenu>
  </div>
);
