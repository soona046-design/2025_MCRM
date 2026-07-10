import * as React from 'react';
import { Kbd, KbdGroup } from 'insight-ui';

export const Basic = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13 }}>
    <span>빠른 검색</span>
    <Kbd>⌘</Kbd>
    <Kbd>K</Kbd>
  </div>
);

export const Group = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13 }}>
    <span>새 리드 등록</span>
    <KbdGroup>
      <Kbd>Ctrl</Kbd>
      <Kbd>Shift</Kbd>
      <Kbd>N</Kbd>
    </KbdGroup>
  </div>
);

export const ShortcutList = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8, fontSize: 13, width: 220 }}>
    <div style={{ display: 'flex', justifyContent: 'space-between' }}>
      <span>티켓 다음으로 이동</span>
      <Kbd>J</Kbd>
    </div>
    <div style={{ display: 'flex', justifyContent: 'space-between' }}>
      <span>상담 완료 처리</span>
      <KbdGroup>
        <Kbd>⌘</Kbd>
        <Kbd>Enter</Kbd>
      </KbdGroup>
    </div>
  </div>
);
