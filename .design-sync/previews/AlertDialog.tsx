import * as React from 'react';
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from 'insight-ui';

export const CancelAppointmentAlert = () => (
  <AlertDialog open>
    <AlertDialogContent style={{ position: 'static', transform: 'none', margin: '0 auto' }}>
      <AlertDialogHeader>
        <AlertDialogTitle>예약을 취소하시겠습니까?</AlertDialogTitle>
        <AlertDialogDescription>
          분당점 박지훈 환자의 7/12(일) 14:00 교정 상담 예약이 취소되며, 환자에게 취소 안내
          알림톡이 자동 발송됩니다.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>돌아가기</AlertDialogCancel>
        <AlertDialogAction>예약 취소</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
);

export const BulkDeleteAlert = () => (
  <AlertDialog open>
    <AlertDialogContent style={{ position: 'static', transform: 'none', margin: '0 auto' }}>
      <AlertDialogHeader>
        <AlertDialogTitle>선택한 리드 12건을 삭제할까요?</AlertDialogTitle>
        <AlertDialogDescription>
          이 작업은 되돌릴 수 없습니다. 삭제된 리드는 채널 성과 리포트 집계에서도 제외됩니다.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>취소</AlertDialogCancel>
        <AlertDialogAction className="bg-destructive hover:bg-destructive/90">
          영구 삭제
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
);
