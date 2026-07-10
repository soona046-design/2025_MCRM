import * as React from 'react';
import { InputOTP, InputOTPGroup, InputOTPSlot, InputOTPSeparator } from 'insight-ui';

export const PhoneVerification = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
    <span style={{ fontSize: 12.5, color: 'hsl(var(--muted-foreground))' }}>휴대폰 인증번호 입력</span>
    <InputOTP maxLength={6} defaultValue="482913">
      <InputOTPGroup>
        <InputOTPSlot index={0} />
        <InputOTPSlot index={1} />
        <InputOTPSlot index={2} />
      </InputOTPGroup>
      <InputOTPSeparator />
      <InputOTPGroup>
        <InputOTPSlot index={3} />
        <InputOTPSlot index={4} />
        <InputOTPSlot index={5} />
      </InputOTPGroup>
    </InputOTP>
  </div>
);

export const StaffLoginCode = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
    <span style={{ fontSize: 12.5, color: 'hsl(var(--muted-foreground))' }}>지점 접속 코드 (4자리)</span>
    <InputOTP maxLength={4} defaultValue="7042">
      <InputOTPGroup>
        <InputOTPSlot index={0} />
        <InputOTPSlot index={1} />
        <InputOTPSlot index={2} />
        <InputOTPSlot index={3} />
      </InputOTPGroup>
    </InputOTP>
  </div>
);
