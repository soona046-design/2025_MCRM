import * as React from 'react';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  Button,
  Label,
  Input,
} from 'insight-ui';

export const NewLeadDialog = () => (
  <Dialog open>
    <DialogContent style={{ position: 'static', transform: 'none', margin: '0 auto' }}>
      <DialogHeader>
        <DialogTitle>신규 리드 등록</DialogTitle>
        <DialogDescription>
          신규 리드 정보를 입력하면 담당 상담매니저에게 자동 배정됩니다.
        </DialogDescription>
      </DialogHeader>
      <div style={{ display: 'grid', gap: 12, padding: '4px 0' }}>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="lead-name">환자명</Label>
          <Input id="lead-name" defaultValue="이서연" />
        </div>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="lead-channel">유입 채널</Label>
          <Input id="lead-channel" defaultValue="네이버 검색광고" />
        </div>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="lead-treatment">관심 진료</Label>
          <Input id="lead-treatment" defaultValue="임플란트" />
        </div>
      </div>
      <DialogFooter>
        <Button variant="outline">취소</Button>
        <Button>리드 등록</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
);

export const DeleteConfirmDialog = () => (
  <Dialog open>
    <DialogContent
      showCloseButton={false}
      style={{ position: 'static', transform: 'none', margin: '0 auto', maxWidth: 420 }}
    >
      <DialogHeader>
        <DialogTitle>상담 티켓을 삭제할까요?</DialogTitle>
        <DialogDescription>
          강남점 · 김민준 상담매니저에게 배정된 티켓입니다. 삭제 후에는 복구할 수 없습니다.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline">취소</Button>
        <Button variant="destructive">삭제</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
);
