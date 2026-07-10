import * as React from 'react';
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
  SheetFooter,
  Button,
  Label,
  Input,
  Separator,
} from 'insight-ui';

export const LeadDetailSheet = () => (
  <Sheet open>
    <SheetContent side="right" style={{ position: 'static', transform: 'none', height: 520 }}>
      <SheetHeader>
        <SheetTitle>이서연 · 리드 상세</SheetTitle>
        <SheetDescription>네이버 검색광고 · 2026-07-05 09:12 유입</SheetDescription>
      </SheetHeader>
      <div style={{ display: 'grid', gap: 14, padding: '0 16px' }}>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="sheet-phone">연락처</Label>
          <Input id="sheet-phone" defaultValue="010-****-5521" readOnly />
        </div>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="sheet-branch">담당 지점</Label>
          <Input id="sheet-branch" defaultValue="강남점" />
        </div>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="sheet-treatment">관심 진료</Label>
          <Input id="sheet-treatment" defaultValue="임플란트 상담" />
        </div>
        <Separator />
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="sheet-memo">상담 메모</Label>
          <Input id="sheet-memo" defaultValue="당일 예약 문의, 통화 선호" />
        </div>
      </div>
      <SheetFooter>
        <Button>상담 티켓 생성</Button>
        <Button variant="outline">닫기</Button>
      </SheetFooter>
    </SheetContent>
  </Sheet>
);
