import * as React from 'react';
import {
  Field,
  FieldLabel,
  FieldDescription,
  FieldError,
  FieldGroup,
  FieldSet,
  FieldLegend,
  FieldSeparator,
  Input,
  RadioGroup,
  RadioGroupItem,
} from 'insight-ui';

export const LabeledInput = () => (
  <FieldGroup style={{ maxWidth: 360 }}>
    <Field>
      <FieldLabel htmlFor="lead-name">환자명</FieldLabel>
      <Input id="lead-name" placeholder="김민지" />
      <FieldDescription>상담 접수 시 등록된 이름입니다.</FieldDescription>
    </Field>
  </FieldGroup>
);

export const WithError = () => (
  <FieldGroup style={{ maxWidth: 360 }}>
    <Field data-invalid="true">
      <FieldLabel htmlFor="lead-phone">연락처</FieldLabel>
      <Input id="lead-phone" placeholder="010-0000-0000" aria-invalid="true" />
      <FieldError>연락처 형식이 올바르지 않습니다.</FieldError>
    </Field>
  </FieldGroup>
);

export const FieldSetWithLegend = () => (
  <FieldSet style={{ maxWidth: 360 }}>
    <FieldLegend>상담 방식</FieldLegend>
    <FieldDescription>환자가 선호하는 상담 방식을 선택하세요.</FieldDescription>
    <RadioGroup defaultValue="visit">
      <Field orientation="horizontal">
        <RadioGroupItem value="visit" id="fs-visit" />
        <FieldLabel htmlFor="fs-visit">방문 상담</FieldLabel>
      </Field>
      <FieldSeparator />
      <Field orientation="horizontal">
        <RadioGroupItem value="phone" id="fs-phone" />
        <FieldLabel htmlFor="fs-phone">전화 상담</FieldLabel>
      </Field>
    </RadioGroup>
  </FieldSet>
);

export const HorizontalOrientation = () => (
  <FieldGroup style={{ maxWidth: 420 }}>
    <Field orientation="horizontal">
      <FieldLabel htmlFor="branch-select" style={{ whiteSpace: 'nowrap', flex: '0 0 auto' }}>담당 지점</FieldLabel>
      <Input id="branch-select" defaultValue="강남점" />
    </Field>
  </FieldGroup>
);
