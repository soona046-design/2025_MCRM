import * as React from 'react';
import { Avatar, AvatarImage, AvatarFallback } from 'insight-ui';

export const WithFallback = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
    <Avatar>
      <AvatarFallback>김상</AvatarFallback>
    </Avatar>
    <Avatar>
      <AvatarFallback>이닥</AvatarFallback>
    </Avatar>
    <Avatar>
      <AvatarFallback>박매</AvatarFallback>
    </Avatar>
  </div>
);

export const WithImage = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
    <Avatar>
      <AvatarImage src="https://i.pravatar.cc/64?img=12" alt="김상담 매니저" />
      <AvatarFallback>김상</AvatarFallback>
    </Avatar>
    <Avatar>
      <AvatarImage src="https://i.pravatar.cc/64?img=32" alt="이닥터" />
      <AvatarFallback>이닥</AvatarFallback>
    </Avatar>
  </div>
);

export const Sizes = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
    <Avatar style={{ width: 24, height: 24 }}>
      <AvatarFallback style={{ fontSize: 10 }}>김</AvatarFallback>
    </Avatar>
    <Avatar>
      <AvatarFallback>김상</AvatarFallback>
    </Avatar>
    <Avatar style={{ width: 48, height: 48 }}>
      <AvatarFallback style={{ fontSize: 16 }}>김상</AvatarFallback>
    </Avatar>
  </div>
);

export const AssigneeGroup = () => (
  <div style={{ display: 'flex' }}>
    <Avatar style={{ border: '2px solid var(--background)', marginRight: -8 }}>
      <AvatarFallback>김</AvatarFallback>
    </Avatar>
    <Avatar style={{ border: '2px solid var(--background)', marginRight: -8 }}>
      <AvatarFallback>이</AvatarFallback>
    </Avatar>
    <Avatar style={{ border: '2px solid var(--background)' }}>
      <AvatarFallback>박</AvatarFallback>
    </Avatar>
  </div>
);
