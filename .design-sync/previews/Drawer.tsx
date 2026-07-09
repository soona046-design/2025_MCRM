import * as React from 'react';
import {
  Drawer,
  DrawerContent,
  DrawerHeader,
  DrawerTitle,
  DrawerDescription,
  DrawerFooter,
  Button,
  Label,
  Input,
} from 'insight-ui';

export const QuickAppointmentDrawer = () => (
  <Drawer open>
    <DrawerContent
      style={{ position: 'static', transform: 'none', width: '100%', maxWidth: 480, margin: '0 auto' }}
    >
      <DrawerHeader>
        <DrawerTitle>빠른 예약 등록</DrawerTitle>
        <DrawerDescription>분당점 · 최유나 상담매니저</DrawerDescription>
      </DrawerHeader>
      <div style={{ display: 'grid', gap: 12, padding: '0 16px 16px' }}>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="drawer-patient">환자명</Label>
          <Input id="drawer-patient" defaultValue="박지훈" />
        </div>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="drawer-date">예약 일시</Label>
          <Input id="drawer-date" defaultValue="2026-07-12 14:00" />
        </div>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="drawer-doctor">담당의</Label>
          <Input id="drawer-doctor" defaultValue="정승우 원장" />
        </div>
      </div>
      <DrawerFooter>
        <Button>예약 확정</Button>
        <Button variant="outline">취소</Button>
      </DrawerFooter>
    </DrawerContent>
  </Drawer>
);
