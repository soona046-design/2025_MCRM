import * as React from 'react';
import { Alert, AlertTitle, AlertDescription } from 'insight-ui';

export const Default = () => (
  <Alert style={{ maxWidth: 460 }}>
    <AlertTitle>예약 리마인더가 전송되었습니다</AlertTitle>
    <AlertDescription>
      내일 오전 10시 이서연님 임플란트 상담 예약이 확정되었습니다.
    </AlertDescription>
  </Alert>
);

export const Destructive = () => (
  <Alert variant="destructive" style={{ maxWidth: 460 }}>
    <AlertTitle>SLA 응답 시간 초과</AlertTitle>
    <AlertDescription>
      3건의 신규 상담이 15분 이상 미배정 상태입니다. 담당자를 지정해 주세요.
    </AlertDescription>
  </Alert>
);
