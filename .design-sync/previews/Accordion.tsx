import * as React from 'react';
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from 'insight-ui';

export const TicketFaq = () => (
  <Accordion type="single" defaultValue="item-1" collapsible style={{ width: 380 }}>
    <AccordionItem value="item-1">
      <AccordionTrigger>SLA 위반 기준은 무엇인가요?</AccordionTrigger>
      <AccordionContent>
        상담 배정 후 30분 이내 첫 응답이 없으면 SLA 위반으로 처리되며, 담당 매니저에게 즉시 알림이 전송됩니다.
      </AccordionContent>
    </AccordionItem>
    <AccordionItem value="item-2">
      <AccordionTrigger>리드 중복은 어떻게 처리되나요?</AccordionTrigger>
      <AccordionContent>
        전화번호 또는 이메일 해시가 동일한 경우 자동으로 병합되며, 최초 유입 채널 정보는 유지됩니다.
      </AccordionContent>
    </AccordionItem>
    <AccordionItem value="item-3">
      <AccordionTrigger>예약 리마인더 발송 시점은?</AccordionTrigger>
      <AccordionContent>예약 24시간 전, 2시간 전 총 2회 자동 발송됩니다.</AccordionContent>
    </AccordionItem>
  </Accordion>
);

export const ChannelBreakdown = () => (
  <Accordion type="single" defaultValue="naver" collapsible style={{ width: 380 }}>
    <AccordionItem value="naver">
      <AccordionTrigger>네이버 검색광고 · 리드 86건</AccordionTrigger>
      <AccordionContent>
        임플란트 문의 52건, 교정 문의 34건. 평균 전환율 6.2%, 광고비 ₩4.8M.
      </AccordionContent>
    </AccordionItem>
    <AccordionItem value="instagram">
      <AccordionTrigger>인스타그램 · 리드 41건</AccordionTrigger>
      <AccordionContent>보존치료 문의 21건, 임플란트 문의 20건. 광고비 ₩2.1M.</AccordionContent>
    </AccordionItem>
  </Accordion>
);
