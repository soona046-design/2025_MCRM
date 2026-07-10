import * as React from 'react';
import {
  Menubar,
  MenubarMenu,
  MenubarTrigger,
  MenubarContent,
  MenubarItem,
  MenubarSeparator,
  MenubarCheckboxItem,
  MenubarShortcut,
} from 'insight-ui';

export const DashboardMenubar = () => (
  <div style={{ display: 'flex', justifyContent: 'center', paddingBottom: 160 }}>
    <Menubar>
      <MenubarMenu value="view" open>
        <MenubarTrigger value="view">보기</MenubarTrigger>
        <MenubarContent
          value="view"
          forceMount
          style={{ position: 'absolute', transform: 'none', top: 40, left: 0 }}
        >
          <MenubarItem>
            퍼널 대시보드 <MenubarShortcut>⌘1</MenubarShortcut>
          </MenubarItem>
          <MenubarItem>
            채널 피벗 <MenubarShortcut>⌘2</MenubarShortcut>
          </MenubarItem>
          <MenubarItem>
            상담원 성과 <MenubarShortcut>⌘3</MenubarShortcut>
          </MenubarItem>
          <MenubarSeparator />
          <MenubarCheckboxItem checked>실시간 업데이트</MenubarCheckboxItem>
        </MenubarContent>
      </MenubarMenu>
      <MenubarMenu>
        <MenubarTrigger>리드</MenubarTrigger>
      </MenubarMenu>
      <MenubarMenu>
        <MenubarTrigger>상담 티켓</MenubarTrigger>
      </MenubarMenu>
      <MenubarMenu>
        <MenubarTrigger>예약</MenubarTrigger>
      </MenubarMenu>
    </Menubar>
  </div>
);
