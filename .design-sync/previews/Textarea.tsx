import * as React from 'react';
import { Textarea, Label } from 'insight-ui';

export const Basic = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 320 }}>
    <Label htmlFor="consult-note">상담 메모</Label>
    <Textarea
      id="consult-note"
      placeholder="상담 내용을 입력하세요"
      defaultValue="임플란트 상담 진행, 다음주 재방문 예정. 예상 견적 320만원 안내함."
    />
  </div>
);

export const Empty = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 320 }}>
    <Label htmlFor="ticket-memo">티켓 메모</Label>
    <Textarea id="ticket-memo" placeholder="상담 내용, 특이사항을 기록하세요" />
  </div>
);

export const Disabled = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 320 }}>
    <Label htmlFor="ticket-memo-locked">상담 메모 (잠김)</Label>
    <Textarea
      id="ticket-memo-locked"
      disabled
      defaultValue="상담 완료 후 수정 불가 처리되었습니다."
    />
  </div>
);
